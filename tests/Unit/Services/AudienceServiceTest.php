<?php

namespace Tests\Unit\Services;

use App\Models\RadioStation;
use App\Services\External\AudienceService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AudienceServiceTest extends TestCase
{
    public function test_it_returns_online_audience_data_from_configured_listener_path(): void
    {
        Http::fake([
            'https://stream.example.test/status' => Http::response([
                'server' => [
                    'listeners' => 27,
                ],
            ]),
        ]);

        $result = (new AudienceService)->get($this->radioStation([
            'listeners_path' => 'server.listeners',
        ]));

        $this->assertSame(27, $result['listeners']);
        $this->assertSame('online', $result['status']);
        $this->assertIsInt($result['response_time_ms']);
    }

    public function test_it_marks_response_as_invalid_when_listener_path_is_not_numeric(): void
    {
        Http::fake([
            'https://stream.example.test/status' => Http::response([
                'listeners' => 'many',
            ]),
        ]);

        $result = (new AudienceService)->get($this->radioStation());

        $this->assertNull($result['listeners']);
        $this->assertSame('invalid_response', $result['status']);
        $this->assertIsInt($result['response_time_ms']);
    }

    public function test_it_marks_station_as_offline_when_http_request_fails(): void
    {
        Http::fake([
            'https://stream.example.test/status' => Http::response([], 500),
        ]);

        $result = (new AudienceService)->get($this->radioStation());

        $this->assertNull($result['listeners']);
        $this->assertSame('offline', $result['status']);
        $this->assertIsInt($result['response_time_ms']);
    }

    public function test_it_never_returns_negative_listener_count(): void
    {
        Http::fake([
            'https://stream.example.test/status' => Http::response([
                'listeners' => -5,
            ]),
        ]);

        $result = (new AudienceService)->get($this->radioStation());

        $this->assertSame(0, $result['listeners']);
        $this->assertSame('online', $result['status']);
    }

    private function radioStation(array $attributes = []): RadioStation
    {
        return new RadioStation([
            ...[
                'name' => 'Rádio Teste',
                'endpoint' => 'https://stream.example.test/status',
                'listeners_path' => 'listeners',
            ],
            ...$attributes,
        ]);
    }
}
