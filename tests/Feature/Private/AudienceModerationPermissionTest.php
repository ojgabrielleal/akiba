<?php

namespace Tests\Feature\Private;

use App\Models\SongRequestBlock;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AudienceModerationPermissionTest extends TestCase
{
    public function test_moderation_permissions_authorize_only_their_respective_actions(): void
    {
        $cases = [
            [[], [false, false, false]],
            [['song.request.block', 'song.request.unblock'], [false, false, false]],
            [['audience.moderation.list'], [true, false, false]],
            [['audience.moderation.block'], [false, true, false]],
            [['audience.moderation.unblock'], [false, false, true]],
        ];

        foreach ($cases as [$permissions, $expected]) {
            $user = $this->getMockBuilder(User::class)
                ->onlyMethods(['hasPermission'])
                ->getMock();
            $user->method('hasPermission')
                ->willReturnCallback(fn (string $permission): bool => in_array($permission, $permissions, true));

            $gate = Gate::forUser($user);

            $this->assertSame($expected, [
                $gate->allows('viewAny', SongRequestBlock::class),
                $gate->allows('create', SongRequestBlock::class),
                $gate->allows('delete', new SongRequestBlock()),
            ]);
        }
    }
}
