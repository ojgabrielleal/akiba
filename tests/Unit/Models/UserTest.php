<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

use App\Models\User;
use App\Models\Preference;
use App\Models\Social;
use App\Models\Role;
use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Program;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Review;
use App\Models\Opinion;
use App\Models\Task;
use App\Models\Favority;
use App\Models\Permission;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from User model relationships.
     */
    public function testPreferencesRelationship(): void
    {
        $preference = Preference::factory();

        $user = User::factory()
            ->has($preference, 'preferences')
            ->create();

        $this->assertContainsOnlyInstancesOf(Preference::class, $user->preferences);
    }

    public function testSocialsRelationship(): void
    {
        $social = Social::factory();

        $user = User::factory()
            ->has($social, 'socials')
            ->create();

        $this->assertContainsOnlyInstancesOf(Social::class, $user->socials);
    }

    public function testFavoritesRelationship(): void
    {
        $favority = Favority::factory();

        $user = User::factory()
            ->has($favority, 'favorites')
            ->create();

        $this->assertContainsOnlyInstancesOf(Favority::class, $user->favorites);
    }

    public function testRolesRelationship(): void
    {
        $role = Role::factory();

        $user = User::factory()
            ->hasAttached($role, [], 'roles')
            ->create();

        $this->assertContainsOnlyInstancesOf(Role::class, $user->roles);
    }

    public function testActivitiesRelationship(): void
    {
        $activity = Activity::factory();

        $user = User::factory()
            ->has($activity, 'activities')
            ->create();

        $this->assertContainsOnlyInstancesOf(Activity::class, $user->activities);
    }

    public function testCalendarRelationship(): void
    {
        $user = User::factory()->create();

        $calendar = Calendar::factory()
            ->for($user, 'responsible')
            ->create();

        $this->assertContainsOnlyInstancesOf(Calendar::class, $user->calendar);
    }

    public function testEventsRelationship(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        Event::factory()
            ->for($post, 'post')
            ->create();

        $this->assertContainsOnlyInstancesOf(Event::class, $user->events);
    }

    public function testProgramsRelationship(): void
    {
        $program = Program::factory();

        $user = User::factory()
            ->has($program, 'programs')
            ->create();

        $this->assertContainsOnlyInstancesOf(Program::class, $user->programs);
    }

    public function testPodcastsRelationship(): void
    {
        $podcast = Podcast::factory();

        $user = User::factory()
            ->has($podcast, 'podcasts')
            ->create();

        $this->assertContainsOnlyInstancesOf(Podcast::class, $user->podcasts);
    }

    public function testPostsRelationship(): void
    {
        $post = Post::factory();

        $user = User::factory()
            ->has($post, 'posts')
            ->create();

        $this->assertContainsOnlyInstancesOf(Post::class, $user->posts);
    }

    public function testOpinionsRelationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $review = Review::factory()
            ->for($post, 'post')
            ->create();

        $opinion = Opinion::factory()
            ->for($review, 'review')
            ->for($user, 'author')
            ->create();

        $this->assertContainsOnlyInstancesOf(Opinion::class, $user->opinions);
    }

    public function testTasksRelationship(): void
    {
        $task = Task::factory();

        $user = User::factory()
            ->has($task, 'tasks')
            ->create();

        $this->assertContainsOnlyInstancesOf(Task::class, $user->tasks);
    }

    /**
     * Tests from User model scopes.
     */
    public function testActiveScope(): void
    {
        $activeUser = User::factory()
            ->create([
                'is_active' => true
            ]);

        $inactiveUser = User::factory()
            ->create([
                'is_active' => false
            ]);

        $users = User::active()->get();

        $this->assertTrue($users->contains($activeUser));
        $this->assertFalse($users->contains($inactiveUser));
    }

    public function testVirtualAttributeCastsToBoolean(): void
    {
        $user = User::factory()->create([
            'is_virtual' => 1,
        ]);

        $this->assertTrue($user->is_virtual);
    }

    public function testPermissionHelpers(): void
    {
        $permission = Permission::factory()->create(['name' => 'posts.create']);
        $otherPermission = Permission::factory()->create(['name' => 'posts.delete']);
        $role = Role::factory()
            ->hasAttached($permission, [], 'permissions')
            ->create(['name' => 'editor']);

        $user = User::factory()
            ->hasAttached($role, [], 'roles')
            ->create();

        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertFalse($user->hasPermission('posts.update'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasRole('administrator'));
        $this->assertTrue($user->hasAnyPermission(['posts.update', 'posts.create']));
        $this->assertFalse($user->hasAnyPermission([$otherPermission->name]));
    }

    /**
     * Tests from User model attributes.
     */
    public function testSlugAttribute(): void
    {
        $user = User::factory()->create(
            ['nickname' => 'sample-review-title']
        );

        $this->assertEquals('sample-review-title', $user->slug);
    }

    public function testPasswordAttributeAcceptsNullableValues(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'password' => null,
        ]);

        $this->assertNull($user->username);
        $this->assertNull($user->password);
    }

    public function testPasswordAttributeIgnoresEmptyValues(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'password' => '',
        ]);

        $this->assertNull($user->password);
    }

    public function testPasswordAttributeHashesFilledValues(): void
    {
        $user = User::factory()->create([
            'password' => 'secret',
        ]);

        $this->assertTrue(Hash::check('secret', $user->password));
    }
}
