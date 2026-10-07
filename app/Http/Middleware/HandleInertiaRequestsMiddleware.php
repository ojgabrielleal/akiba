<?php

namespace App\Http\Middleware;

use App\Models\BadgeAssignment;
use App\Services\OnairService;
use App\Http\Resources\Onair\OnairResource;
use App\Integrations\StreamService;
use App\Support\AuthenticatedMember;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

use Inertia\Middleware;

class HandleInertiaRequestsMiddleware extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'onair' => fn () => OnairResource::collection(
                app(OnairService::class)->filter([
                    'live' => true,
                    'with' => ['host', 'program.host'],
                ])
            ),
            'stream' => fn () => (new StreamService)->data(),
            'flash' => fn () => session('flash'),
            'push' => [
                'vapid_public_key' => config('services.webpush.public_key'),
            ],
            'newBadges' => fn () => $this->newBadges($request),
            'publicSiteVersion' => fn () => File::exists(storage_path('app/public-site-version.txt'))
                ? trim((string) File::get(storage_path('app/public-site-version.txt')))
                : 'provisory',
        ]);
    }

    private function newBadges(Request $request): array
    {
        $member = AuthenticatedMember::fromRequest($request);

        if (! $member) {
            return [];
        }

        return BadgeAssignment::query()
            ->with('badge')
            ->where('owner_type', $member->getMorphClass())
            ->where('owner_id', $member->getKey())
            ->whereNull('seen_at')
            ->active()
            ->latest('acquired_at')
            ->limit(5)
            ->get()
            ->map(fn (BadgeAssignment $assignment) => [
                'uuid' => $assignment->uuid,
                'badge' => [
                    'name' => $assignment->badge?->name,
                    'image' => $assignment->badge?->image,
                ],
            ])
            ->values()
            ->all();
    }
}
