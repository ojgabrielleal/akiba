<?php

namespace Tests\Feature\Private;

use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PostSocialDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Storage::fake('public');
        config(['services.meta.instagram_enabled' => true]);
    }

    public function test_draft_can_be_saved_without_social_data(): void
    {
        $user = $this->userWithPermissions(['post.create']);

        $this->actingAs($user)->post('/panel/post', [
            'module' => 'post',
            'status' => 'draft',
            'title' => 'Rascunho editorial',
        ])->assertSessionHasNoErrors();

        $post = Post::query()->where('title', 'Rascunho editorial')->firstOrFail();

        $this->assertSame('draft', $post->status);
        $this->assertNull($post->social);
        $this->assertDatabaseCount('social_publication_schedules', 0);
        Queue::assertNothingPushed();
    }

    public function test_review_submission_requires_social_text(): void
    {
        $user = $this->userWithPermissions(['post.create']);

        $response = $this->actingAs($user)
            ->from('/panel/post')
            ->post('/panel/post', [
                ...$this->validPostPayload('revision'),
                'social' => [
                    'text' => '',
                    'hashtags' => [],
                    'channels' => ['instagram' => true],
                ],
            ]);

        $response->assertRedirect('/panel/post');
        $response->assertSessionHasErrors([
            'social.text' => 'Informe o texto para as redes sociais.',
        ]);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_review_submission_normalizes_and_persists_social_data_without_publishing(): void
    {
        $user = $this->userWithPermissions(['post.create']);

        $response = $this->actingAs($user)->post('/panel/post', [
            ...$this->validPostPayload('revision'),
            'social' => [
                'text' => '  Confira a novidade da Akiba!  ',
                'hashtags' => ['#Anime', '#REDEAKIBA', '#anime', '#日本'],
                'channels' => ['instagram' => true],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $post = Post::query()->where('title', 'Matéria social')->firstOrFail();

        $this->assertEquals([
            'text' => 'Confira a novidade da Akiba!',
            'hashtags' => ['anime', 'redeakiba', '日本'],
            'channels' => ['instagram' => true],
        ], $post->social);
        $this->assertSame('revision', $post->status);
        $this->assertDatabaseCount('social_publication_schedules', 0);
        Queue::assertNothingPushed();
    }

    public function test_user_without_publish_permission_cannot_create_a_published_post(): void
    {
        $user = $this->userWithPermissions(['post.create']);

        $this->actingAs($user)->post('/panel/post', [
            ...$this->validPostPayload('published'),
            'social' => $this->social(),
        ])->assertForbidden();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_published_post_requires_the_instagram_channel(): void
    {
        $user = $this->userWithPermissions(['post.create', 'post.publish']);

        $this->actingAs($user)->post('/panel/post', [
            ...$this->validPostPayload('published'),
            'social' => $this->social(false),
        ])->assertSessionHasErrors([
            'social.channels.instagram' => 'A publicação no Instagram é obrigatória.',
        ]);

        $this->assertDatabaseCount('posts', 0);
        Queue::assertNotPushed(PublishSocialPublicationSchedule::class);
    }

    public function test_social_validation_rejects_too_many_or_invalid_hashtags(): void
    {
        $user = $this->userWithPermissions(['post.create']);

        $response = $this->actingAs($user)
            ->from('/panel/post')
            ->post('/panel/post', [
                ...$this->validPostPayload('revision'),
                'social' => [
                    'text' => 'Texto social válido.',
                    'hashtags' => [
                        ...array_map(fn (int $index): string => "tag{$index}", range(1, 31)),
                        'hashtag inválida',
                        ['indevida'],
                    ],
                    'channels' => ['instagram' => true],
                ],
            ]);

        $response->assertRedirect('/panel/post');
        $response->assertSessionHasErrors([
            'social.hashtags',
            'social.hashtags.31',
            'social.hashtags.32',
        ]);
    }

    public function test_publishing_a_review_requires_publish_or_approve_permission(): void
    {
        $author = $this->userWithPermissions(['post.update']);
        $post = Post::factory()->for($author, 'author')->create([
            'status' => 'revision',
            'social' => $this->social(),
        ]);
        $payload = $this->validUpdatePayload($post, 'published');

        $this->actingAs($author)
            ->patch("/panel/post/{$post->uuid}", $payload)
            ->assertForbidden();

        $this->assertSame('revision', $post->fresh()->status);

        $approver = $this->userWithPermissions(['post.update', 'post.approve']);

        $this->actingAs($approver)
            ->patch("/panel/post/{$post->uuid}", $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_edit_screen_returns_saved_social_data_and_only_required_capabilities(): void
    {
        $user = $this->userWithPermissions([
            'post.list',
            'post.view',
            'post.update',
            'post.publish',
            'post.approve',
        ]);
        $post = Post::factory()->for($user, 'author')->create([
            'status' => 'revision',
            'social' => [
                ...$this->social(),
                'hashtags' => ['anime', 'redeakiba'],
            ],
        ]);

        $this->actingAs($user)
            ->get("/panel/post/{$post->uuid}")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('post.data.social.text', 'Texto criado para as redes sociais.')
                ->where('post.data.social.hashtags', ['anime', 'redeakiba'])
                ->where('postCapabilities', [
                    'submitForReview' => true,
                    'publish' => true,
                    'approve' => true,
                ]));
    }

    private function validPostPayload(string $status): array
    {
        return [
            'module' => 'post',
            'status' => $status,
            'title' => 'Matéria social',
            'content' => '<p>Conteúdo editorial.</p>',
            'image' => UploadedFile::fake()->image('featured.jpg', 708, 827),
            'cover' => UploadedFile::fake()->image('cover.jpg', 1200, 400),
            'tags' => [['name' => 'anime']],
        ];
    }

    private function social(bool $instagram = true): array
    {
        return [
            'text' => 'Texto criado para as redes sociais.',
            'hashtags' => ['#anime', '#redeakiba'],
            'channels' => ['instagram' => $instagram],
        ];
    }

    private function validUpdatePayload(Post $post, string $status): array
    {
        return [
            'module' => 'post',
            'status' => $status,
            'title' => $post->title,
            'content' => $post->content,
            'image' => $post->image,
            'cover' => $post->cover,
            'tags' => [['name' => 'anime']],
            'social' => $this->social(),
        ];
    }

    private function userWithPermissions(array $permissionNames): User
    {
        $role = Role::factory()->create();
        $permissions = collect($permissionNames)
            ->map(fn (string $name) => Permission::factory()->create(['name' => $name]));

        $role->permissions()->sync($permissions->pluck('id'));

        return User::factory()
            ->hasAttached($role, [], 'roles')
            ->create();
    }
}
