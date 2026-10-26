<?php

namespace Tests\Unit\Integrations;

use App\Integrations\IpLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IpLocationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function test_it_resolves_and_normalizes_a_public_ip_location(): void
    {
        Http::fake([
            'api.country.is/*' => Http::response([
                'ip' => '8.8.8.8',
                'country' => 'us',
                'subdivision' => 'ca',
                'city' => 'Mountain View',
                'location' => [
                    'latitude' => 37.386,
                    'longitude' => -122.0838,
                    'accuracy_radius' => 1000,
                    'time_zone' => 'America/Los_Angeles',
                ],
            ]),
        ]);

        $location = app(IpLocation::class)->find('8.8.8.8');

        $this->assertSame('US', $location['country_code']);
        $this->assertSame('CA', $location['region_code']);
        $this->assertSame('Mountain View', $location['city']);
        $this->assertSame(37.386, $location['latitude']);
        $this->assertSame(-122.0838, $location['longitude']);
        $this->assertSame(1000, $location['location_accuracy_radius']);
        $this->assertSame('America/Los_Angeles', $location['timezone']);
    }

    public function test_it_does_not_request_locations_for_private_ips(): void
    {
        Http::fake();

        $this->assertSame([], app(IpLocation::class)->find('127.0.0.1'));

        Http::assertNothingSent();
    }

    public function test_it_caches_the_location_by_ip(): void
    {
        Http::fake([
            'api.country.is/*' => Http::response(['country' => 'BR', 'city' => 'São Paulo']),
        ]);

        $service = app(IpLocation::class);
        $service->find('8.8.4.4');
        $service->find('8.8.4.4');

        Http::assertSentCount(1);
    }

    public function test_it_can_resolve_the_server_egress_for_a_private_ip_in_local_development(): void
    {
        config(['services.geoip.resolve_private_ips' => true]);
        Http::fake([
            'api.country.is*' => Http::response([
                'country' => 'BR',
                'subdivision' => 'SP',
                'city' => 'São Paulo',
            ]),
        ]);

        $location = app(IpLocation::class)->find('172.18.0.1');

        $this->assertSame('BR', $location['country_code']);
        $this->assertSame('São Paulo', $location['city']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.country.is?fields=city%2Csubdivision%2Clocation');
    }
}
