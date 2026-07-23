<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\InactiveItem\ReactivateInactiveItemAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Program;
use App\Models\Repository;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ReactivateInactiveItemController extends Controller
{
    use HasFlashMessages;

    private const MODELS = [
        'user' => User::class,
        'program' => Program::class,
        'post' => Post::class,
        'podcast' => Podcast::class,
        'poll' => Poll::class,
        'task' => Task::class,
        'repository' => Repository::class,
    ];

    public function __construct(
        private ReactivateInactiveItemAction $reactivateInactiveItemAction,
    ) {}

    public function __invoke(string $type, string $uuid)
    {
        Gate::authorize('inactive.restore');

        $item = $this->resolveItem($type, $uuid);
        $this->reactivateInactiveItemAction->execute($item);

        return $this->flashMessage('update', 'Item reativado com sucesso.', '♻️');
    }

    private function resolveItem(string $type, string $uuid): Model
    {
        abort_unless(array_key_exists($type, self::MODELS), 404);

        return self::MODELS[$type]::query()
            ->where('is_active', false)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
