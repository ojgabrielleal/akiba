<?php

namespace Tests\Unit\Integrations;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfiguration;
use App\Integrations\MetaSocial\PublicationConfigurationException;
use Tests\TestCase;

class MetaPublicationConfigurationTest extends TestCase
{
    private PublicationConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = new PublicationConfiguration;
        config([
            'services.meta.graph_version' => 'v24.0',
            'services.meta.instagram_user_id' => 'instagram-user-id',
            'services.meta.access_token' => 'secret-access-token',
            'services.meta.instagram_enabled' => false,
        ]);
    }

    public function test_instagram_is_disabled_by_default(): void
    {
        config([
            'services.meta.instagram_enabled' => false,
        ]);

        $this->assertFalse($this->configuration->isEnabled(PublicationChannel::Instagram));
        $this->assertFalse($this->configuration->isReady(PublicationChannel::Instagram));
    }

    public function test_enabled_instagram_is_ready_with_its_required_settings(): void
    {
        config([
            'services.meta.instagram_enabled' => true,
        ]);

        $this->assertTrue($this->configuration->isReady(PublicationChannel::Instagram));
    }

    public function test_instagram_reports_only_its_missing_settings(): void
    {
        config([
            'services.meta.instagram_enabled' => true,
            'services.meta.graph_version' => null,
            'services.meta.instagram_user_id' => null,
            'services.meta.access_token' => null,
        ]);

        $this->assertFalse($this->configuration->isReady(PublicationChannel::Instagram));
        $this->assertSame(
            ['graph_version', 'access_token', 'instagram_user_id'],
            $this->configuration->missingSettings(PublicationChannel::Instagram),
        );
    }

    public function test_disabled_channel_fails_with_a_clear_error(): void
    {
        $this->expectException(PublicationConfigurationException::class);
        $this->expectExceptionMessage('Meta publication channel [instagram] is disabled.');

        $this->configuration->ensureReady(PublicationChannel::Instagram);
    }

    public function test_missing_configuration_error_does_not_expose_the_access_token(): void
    {
        config([
            'services.meta.instagram_enabled' => true,
            'services.meta.instagram_user_id' => null,
        ]);

        try {
            $this->configuration->ensureReady(PublicationChannel::Instagram);
            $this->fail('Expected an exception for missing Meta configuration.');
        } catch (PublicationConfigurationException $exception) {
            $this->assertStringContainsString('instagram_user_id', $exception->getMessage());
            $this->assertStringNotContainsString('secret-access-token', $exception->getMessage());
        }
    }
}
