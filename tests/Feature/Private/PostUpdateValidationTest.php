<?php

namespace Tests\Feature\Private;

use App\Models\Permission;
use App\Models\Post;
use App\Models\PostReview;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

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
