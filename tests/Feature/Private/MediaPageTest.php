<?php

namespace Tests\Feature\Private;

use App\Http\Resources\EnigmaGameResource;
use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\Permission;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPageTest extends TestCase
{
    use RefreshDatabase;

    public function testMediaPageRendersWhenThereIsNoLatestValidPoll(): void
    {
        $user = $this->userWithPermissions([
            'media.module.view',
            'poll.list',
        ]);

        Poll::factory()
            ->has(PollOption::factory(4), 'options')
            ->create([
                'is_active' => true,
                'expires_at' => now()->subDay(),
            ]);

        $this
            ->actingAs($user)
            ->get('/panel/media')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Media', false)
                ->where('latestPoll', null)
            );
    }

    public function test_guest_is_redirected_from_media_page(): void
    {
        $this
            ->get('/panel/media')
            ->assertRedirect('/panel');
    }

    public function test_media_page_requires_permission(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get('/panel/media')
            ->assertForbidden();
    }

    public function test_media_page_renders_expected_component_for_authorized_user(): void
    {
        $user = $this->userWithPermissions([
            'media.module.view',
            'poll.list',
            'listener.gallery.list',
        ]);

        $this
            ->actingAs($user)
            ->get('/panel/media')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Media', false)
                ->has('polls')
                ->has('listenerGalleries')
            );
    }

    public function test_enigmagame_can_be_created_with_image(): void
    {
        Storage::fake('public');

        $user = $this->userWithPermissions([
            'media.module.view',
            'enigmagame.create',
        ]);

        $this
            ->actingAs($user)
            ->post('/panel/media/enigmagame', [
                'title' => 'Enigma de teste',
                'image' => UploadedFile::fake()->image('enigma.png', 900, 500),
                'status' => 'draft',
                'solution' => 'Resposta secreta',
            ])
            ->assertRedirect();

        $enigmagame = EnigmaGame::query()->firstOrFail();

        $this->assertSame('Enigma de teste', $enigmagame->title);
        $this->assertSame(EnigmaGame::STATUS_DRAFT, $enigmagame->status);
        $this->assertStringStartsWith('/storage/images/enigmagames/', $enigmagame->content);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $enigmagame->content));
    }

    public function test_enigmagame_image_can_be_updated(): void
    {
        Storage::fake('public');

        $user = $this->userWithPermissions([
            'media.module.view',
            'enigmagame.update',
        ]);
        $enigmagame = EnigmaGame::factory()->draft()->create([
            'title' => 'Enigma antigo',
            'content' => '/storage/images/enigmagames/old.webp',
        ]);

        $this
            ->actingAs($user)
            ->post("/panel/media/enigmagame/{$enigmagame->uuid}", [
                '_method' => 'PATCH',
                'title' => 'Enigma atualizado',
                'image' => UploadedFile::fake()->image('novo-enigma.png', 900, 500),
                'status' => 'draft',
                'solution' => 'Resposta atualizada',
            ])
            ->assertRedirect();

        $enigmagame->refresh();

        $this->assertSame('Enigma atualizado', $enigmagame->title);
        $this->assertStringStartsWith('/storage/images/enigmagames/', $enigmagame->content);
        $this->assertNotSame('/storage/images/enigmagames/old.webp', $enigmagame->content);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $enigmagame->content));
    }

    public function test_enigmagame_can_be_updated_without_reprocessing_legacy_content(): void
    {
        Storage::fake('public');

        $user = $this->userWithPermissions([
            'media.module.view',
            'enigmagame.update',
        ]);
        $legacyContent = "Passou a vida se preparando para lutar\ncontra monstros e descobriu que...";
        $enigmagame = EnigmaGame::factory()->draft()->create([
            'title' => 'Enigma antigo',
            'content' => $legacyContent,
        ]);

        $this
            ->actingAs($user)
            ->post("/panel/media/enigmagame/{$enigmagame->uuid}", [
                '_method' => 'PATCH',
                'title' => 'Enigma atualizado',
                'status' => 'draft',
                'solution' => 'Resposta atualizada',
            ])
            ->assertRedirect();

        $enigmagame->refresh();

        $this->assertSame('Enigma atualizado', $enigmagame->title);
        $this->assertSame($legacyContent, $enigmagame->content);
    }

    public function test_enigmagame_question_can_be_classified_as_yes(): void
    {
        $user = $this->userWithPermissions([
            'media.module.view',
            'enigmagame.respond',
        ]);
        $interaction = EnigmaGameInteraction::factory()
            ->question()
            ->create();

        $this
            ->actingAs($user)
            ->patch("/panel/media/enigmagame/interaction/{$interaction->uuid}/respond", [
                'result' => 'yes',
            ])
            ->assertRedirect();

        $interaction->refresh();

        $this->assertSame('yes', $interaction->result);
        $this->assertNull($interaction->admin_response);
        $this->assertSame($user->id, $interaction->responded_by);
        $this->assertNotNull($interaction->responded_at);
    }

    public function test_enigmagame_question_yes_does_not_solve_enigma(): void
    {
        $game = EnigmaGame::factory()->active()->create([
            'solution' => 'Naruto',
        ]);

        EnigmaGameInteraction::factory()
            ->yes()
            ->for($game, 'enigmagame')
            ->create();

        $payload = EnigmaGameResource::make($game->load('interactions.participant'))
            ->resolve(Request::create('/midias'));

        $this->assertFalse($payload['solved']);
        $this->assertNull($payload['solution']);
        $this->assertNull($payload['solved_by']);
        $this->assertNull($payload['solved_at']);
    }

    public function test_enigmagame_question_rejects_final_answer_result(): void
    {
        $user = $this->userWithPermissions([
            'media.module.view',
            'enigmagame.respond',
        ]);
        $interaction = EnigmaGameInteraction::factory()
            ->question()
            ->create();

        $this
            ->actingAs($user)
            ->patch("/panel/media/enigmagame/interaction/{$interaction->uuid}/respond", [
                'result' => 'correct',
            ])
            ->assertSessionHasErrors('result');
    }

    private function userWithPermissions(array $permissionNames): User
    {
        $role = Role::factory()->create();
        $permissions = collect($permissionNames)
            ->map(fn (string $name) => Permission::factory()->create(['name' => $name]));
        $role->permissions()->attach($permissions);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
