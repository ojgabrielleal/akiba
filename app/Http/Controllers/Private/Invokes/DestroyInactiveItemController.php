<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\InactiveItem\DestroyInactiveItemAction;
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

class DestroyInactiveItemController extends Controller
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

    public function __invoke(DestroyInactiveItemAction $action, string $type, string $uuid)
    {
        Gate::authorize('inactive.delete');

        $item = $this->resolveItem($type, $uuid);
        $action->execute($item);

        return $this->flashMessage('delete', 'Item excluído definitivamente.');
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
