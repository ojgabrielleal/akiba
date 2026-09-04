<?php

namespace Tests\Feature\Private;

use App\Models\Permission;
use App\Models\Post;
use App\Models\PostReview;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_store_accepts_rich_text_fields_sent_as_arrays(): void
    {
        Storage::fake('public');

        $user = $this->userWithPermissions(['post.create']);

        $response = $this
            ->actingAs($user)
            ->from('/panel/post')
            ->post('/panel/post', [
                'module' => 'review',
                'title' => 'Anime Test',
                'image' => UploadedFile::fake()->image('featured.jpg', 708, 827),
                'cover' => UploadedFile::fake()->image('cover.jpg', 640, 360),
                'studio' => 'Studio Test',
                'metadata' => [
                    'date_of_release' => '2026-09-04',
                    'sinopse' => ['', '<p>Sinopse final</p>'],
                ],
                'review' => [
                    'status' => 'published',
                    'content' => ['', '<p>Review final</p>'],
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('posts', [
            'module' => 'review',
            'title' => 'Anime Test',
        ]);
        $this->assertDatabaseHas('post_reviews', [
            'content' => '<p>Review final</p>',
        ]);

        $post = Post::query()->where('title', 'Anime Test')->firstOrFail();

        $this->assertSame('<p>Sinopse final</p>', $post->metadata['sinopse']);
    }

    public function test_event_store_accepts_text_fields_sent_as_arrays(): void
    {
        Storage::fake('public');

        $user = $this->userWithPermissions(['post.create']);

        $response = $this
            ->actingAs($user)
            ->from('/panel/post')
            ->post('/panel/post', [
                'module' => 'event',
                'status' => 'published',
                'title' => 'Evento Test',
                'content' => ['', '<p>Conteúdo do evento</p>'],
                'image' => UploadedFile::fake()->image('featured.jpg', 708, 827),
                'cover' => UploadedFile::fake()->image('cover.jpg', 640, 360),
                'metadata' => [
                    'event_date' => '2026-09-04',
                    'dates' => ['', '04 de setembro de 2026'],
                    'address' => ['', 'Rua Teste, 123'],
                ],
                'tags' => [
                    ['name' => 'event'],
                    ['name' => 'anime'],
                ],
            ]);

        $response->assertSessionHasNoErrors();

        $post = Post::query()->where('title', 'Evento Test')->firstOrFail();

        $this->assertSame('event', $post->module);
        $this->assertSame('<p>Conteúdo do evento</p>', $post->content);
        $this->assertSame('04 de setembro de 2026', $post->metadata['dates']);
        $this->assertSame('Rua Teste, 123', $post->metadata['address']);
    }

    public function test_review_update_returns_field_errors_for_required_fields(): void
    {
        $user = $this->userWithPermissions(['post.update', 'post.list']);
        $post = Post::factory()->review()->for($user, 'author')->create();

        PostReview::factory()
            ->for($post)
            ->for($user, 'author')
            ->create([
                'status' => 'revision',
                'content' => 'Review original',
            ]);

        $response = $this
            ->actingAs($user)
            ->from("/panel/post/{$post->uuid}")
            ->patch("/panel/post/{$post->uuid}", [
                'module' => 'review',
                'title' => '',
                'image' => $post->image,
                'cover' => $post->cover,
                'studio' => '',
                'metadata' => [
                    'date_of_release' => '',
                    'sinopse' => '<p><br></p>',
                ],
                'review' => [
                    'status' => 'published',
                    'content' => '<p><br></p>',
                ],
            ]);

        $response->assertRedirect("/panel/post/{$post->uuid}");
        $response->assertSessionHasErrors([
            'title',
            'studio',
            'metadata.date_of_release',
            'metadata.sinopse',
            'review.content',
        ]);
        $response->assertSessionMissing('_old_input.metadata');
        $response->assertSessionMissing('_old_input.review');
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
