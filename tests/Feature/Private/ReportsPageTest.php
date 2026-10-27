<?php

namespace Tests\Feature\Private;

use App\Models\Music;
use App\Models\Onair;
use App\Models\PageView;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Program;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SongRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => str_repeat('a', 32)]);
    }

    public function test_reports_page_exposes_the_internal_ranking(): void
    {
        $authenticatedUser = $this->userWithPermissions(['report.module.view']);

        $writer = User::factory()->create();
        Post::factory(3)->for($writer, 'author')->create();
        Post::factory()->review()->for($writer, 'author')->create();

        $host = User::factory()->create();
        $firstProgram = Program::factory()->for($host, 'host')->create();
        $secondProgram = Program::factory()->for($host, 'host')->create();
        Onair::factory(2)->for($firstProgram, 'program')->create();
        Onair::factory(2)->for($secondProgram, 'program')->create();

        $requestsProgram = Program::factory()
            ->for(User::factory(), 'host')
            ->create();
        $onair = Onair::factory()->for($requestsProgram, 'program')->create();
        $onair->update([
            'peak_listeners' => 85,
            'peak_listeners_at' => '2026-07-20 18:30:00',
        ]);
        $music = Music::factory()->create();
        SongRequest::factory(3)->for($onair)->for($music)->create([
            'created_at' => '2026-07-20 15:00:00',
        ]);
        SongRequest::factory(2)->for($onair)->for($music)->create([
            'created_at' => '2026-07-21 15:00:00',
        ]);

        $mostInteractedPost = Post::factory()
            ->has(PostReaction::factory(4), 'reactions')
            ->create();
        Post::factory()
            ->has(PostReaction::factory(2), 'reactions')
            ->create();

        $mostVotedPoll = Poll::factory()->create();
        $mostVotedOption = PollOption::factory()->for($mostVotedPoll, 'poll')->create();
        PollVote::factory(3)->create([
            'poll_id' => $mostVotedPoll->id,
            'poll_option_id' => $mostVotedOption->id,
        ]);

        $otherPoll = Poll::factory()->create();
        $otherOption = PollOption::factory()->for($otherPoll, 'poll')->create();
        PollVote::factory()->create([
            'poll_id' => $otherPoll->id,
            'poll_option_id' => $otherOption->id,
        ]);

        $this
            ->actingAs($authenticatedUser)
            ->get('/panel/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('ranking_interno.redator_mais_ativo.usuario.uuid', $writer->uuid)
                ->where('ranking_interno.redator_mais_ativo.total', 3)
                ->where('ranking_interno.locutor_mais_ativo.usuario.uuid', $host->uuid)
                ->where('ranking_interno.locutor_mais_ativo.total', 4)
                ->where('ranking_interno.pedidos_atendidos.data', '20/07/2026')
                ->where('ranking_interno.pedidos_atendidos.total', 3)
                ->where('ranking_interno.pico_audiencia.programa.uuid', $requestsProgram->uuid)
                ->where('ranking_interno.pico_audiencia.total', 85)
                ->where('ranking_interno.pico_audiencia.data', '20/07/2026 18:30')
                ->where('ranking_interno.maior_interacao.uuid', $mostInteractedPost->uuid)
                ->where('ranking_interno.maior_interacao.total', 4)
                ->where('ranking_interno.enquete_mais_votada.uuid', $mostVotedPoll->uuid)
                ->where('ranking_interno.enquete_mais_votada.total', 3)
            );
    }

    public function test_internal_ranking_is_null_when_there_is_no_data(): void
    {
        $this
            ->actingAs($this->userWithPermissions(['report.module.view']))
            ->get('/panel/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('ranking_interno.redator_mais_ativo', null)
                ->where('ranking_interno.locutor_mais_ativo', null)
                ->where('ranking_interno.pedidos_atendidos', null)
                ->where('ranking_interno.pico_audiencia', null)
                ->where('ranking_interno.maior_interacao', null)
                ->where('ranking_interno.enquete_mais_votada', null)
            );
    }

    public function test_guest_is_redirected_from_reports_page(): void
    {
        $this
            ->get('/panel/reports')
            ->assertRedirect('/panel');
    }

    public function test_reports_page_requires_permission(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get('/panel/reports')
            ->assertForbidden();
    }

    public function test_reports_page_renders_expected_component_for_authorized_user(): void
    {
        $this
            ->actingAs($this->userWithPermissions(['report.module.view']))
            ->get('/panel/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Reports', false)
                ->has('audience')
                ->has('onair')
                ->has('ranking_interno')
            );
    }

    public function test_reports_page_exposes_access_report_permissions(): void
    {
        $this
            ->actingAs($this->userWithPermissions([
                'report.module.view',
                'report.podcast.view',
            ]))
            ->get('/panel/reports')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('reportAccessPermissions.publications', false)
                ->where('reportAccessPermissions.podcasts', true)
                ->where('reportAccessPermissions.pages', false)
                ->where('reportAccessPermissions.geography', false)
            );
    }

    public function test_post_viewers_report_requires_publication_report_permission(): void
    {
        $post = Post::factory()->create();

        $this
            ->actingAs($this->userWithPermissions(['report.module.view']))
            ->get("/panel/reports/post/{$post->uuid}/viewers")
            ->assertForbidden();

        $this
            ->actingAs($this->userWithPermissions([
                'report.module.view',
                'report.publication.view',
            ]))
            ->get("/panel/reports/post/{$post->uuid}/viewers")
            ->assertOk();
    }

    public function test_reports_page_exposes_geographic_page_view_totals(): void
    {
        config(['inertia.testing.ensure_pages_exist' => false]);

        PageView::factory(3)->create([
            'country_code' => 'BR',
            'region_code' => 'SP',
            'city' => 'São Paulo',
        ]);
        PageView::factory(2)->create([
            'country_code' => 'US',
            'region_code' => 'CA',
            'city' => 'Mountain View',
        ]);
        PageView::factory()->create([
            'country_code' => null,
            'region_code' => null,
            'city' => null,
        ]);
        PageView::factory()->create([
            'actor_type' => User::class,
            'actor_id' => User::factory(),
            'country_code' => 'JP',
            'region_code' => '13',
            'city' => 'Tóquio',
        ]);

        $this
            ->actingAs($this->userWithPermissions([
                'report.module.view',
                'report.geography.view',
            ]))
            ->get('/panel/reports?geography_period=30&geography_visitor=anonymous')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->loadDeferredProps('reports-geography', fn ($reload) => $reload
                    ->component('private/Reports', false)
                    ->where('geography.total_views', 7)
                    ->where('geography.located_views', 6)
                    ->where('geography.unknown_views', 1)
                    ->where('geography.visitor_type', 'all')
                    ->where('geography.countries.0.code', 'BR')
                    ->where('geography.countries.0.views', 3)
                    ->where('geography.cities.0.city', 'São Paulo')
                    ->where('geography.cities.0.views', 3)
                    ->where('geography.viewers.0.city', 'Tóquio')
                    ->where('geography.viewers.0.region_code', '13')
                    ->where('geography.viewers.0.country_code', 'JP')
                )
            );
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
