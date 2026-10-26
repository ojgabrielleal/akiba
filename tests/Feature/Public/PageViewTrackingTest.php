<?php

namespace Tests\Feature\Public;

use App\Models\PageView;
use App\Models\Post;
use App\Services\PageViewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PageViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_tracks_a_public_page_without_a_viewable_model(): void
    {
        $this->get('/entrar')->assertOk();

        $view = PageView::query()->sole();

        $this->assertNull($view->viewable_type);
        $this->assertNull($view->viewable_id);
        $this->assertSame('Entrar', $view->page_name);
        $this->assertSame(url('/entrar'), $view->page_url);
    }

    public function test_it_does_not_track_unsuccessful_page_responses(): void
    {
        $this->get('/podcast/podcast-inexistente')->assertNotFound();

        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_it_stores_the_ip_location_snapshot_on_the_view(): void
    {
        Http::fake([
            'api.country.is/*' => Http::response([
                'country' => 'BR',
                'subdivision' => 'SP',
                'city' => 'São Paulo',
                'location' => [
                    'latitude' => -23.5505,
                    'longitude' => -46.6333,
                    'accuracy_radius' => 20,
                    'time_zone' => 'America/Sao_Paulo',
                ],
            ]),
        ]);

        $request = Request::create('/materia/exemplo', server: ['REMOTE_ADDR' => '1.1.1.1']);
        $view = app(PageViewService::class)->store(Post::factory()->create(), $request);

        $this->assertSame('BR', $view->country_code);
        $this->assertSame('SP', $view->region_code);
        $this->assertSame('São Paulo', $view->city);
        $this->assertSame(-23.5505, $view->latitude);
        $this->assertSame(-46.6333, $view->longitude);
        $this->assertSame(20, $view->location_accuracy_radius);
        $this->assertSame('America/Sao_Paulo', $view->timezone);
    }
}
