<?php

namespace Tests\Unit\Integrations;

use App\Integrations\MetaSocial\GraphErrorClassifier;
use App\Integrations\MetaSocial\PublicationFailure;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MetaGraphApiContractTest extends TestCase
{
    public function test_instagram_image_container_creation_contract(): void
    {
        $contract = $this->publicationContracts()['instagram_container_create'];

        $this->assertSame('POST', $contract['method']);
        $this->assertSame('/{ig_user_id}/media', $contract['endpoint']);
        $this->assertSame('bearer', $contract['authentication']);
        $this->assertSame(['image_url', 'caption'], array_keys($contract['request']));
        $this->assertNotEmpty($contract['response']['id']);
    }

    public function test_instagram_container_status_contract(): void
    {
        $contract = $this->publicationContracts()['instagram_container_status'];

        $this->assertSame('GET', $contract['method']);
        $this->assertSame('/{container_id}', $contract['endpoint']);
        $this->assertSame('status_code,status', $contract['request']['fields']);
        $this->assertSame('FINISHED', $contract['response']['status_code']);
        $this->assertNotEmpty($contract['response']['id']);
    }

    public function test_instagram_media_publication_contract(): void
    {
        $contract = $this->publicationContracts()['instagram_media_publish'];

        $this->assertSame('POST', $contract['method']);
        $this->assertSame('/{ig_user_id}/media_publish', $contract['endpoint']);
        $this->assertSame(['creation_id'], array_keys($contract['request']));
        $this->assertNotEmpty($contract['response']['id']);
    }

    public function test_facebook_page_photo_publication_contract(): void
    {
        $contract = $this->publicationContracts()['facebook_photo_publish'];

        $this->assertSame('POST', $contract['method']);
        $this->assertSame('/{page_id}/photos', $contract['endpoint']);
        $this->assertSame('bearer', $contract['authentication']);
        $this->assertSame(['url', 'message'], array_keys($contract['request']));
        $this->assertNotEmpty($contract['response']['post_id']);
    }

    #[DataProvider('errorContractProvider')]
    public function test_it_classifies_graph_api_errors(
        array $error,
        PublicationFailure $expectedFailure,
        bool $retryable,
    ): void {
        $failure = GraphErrorClassifier::classify($error);

        $this->assertSame($expectedFailure, $failure);
        $this->assertSame($retryable, $failure->isRetryable());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, PublicationFailure, bool}>
     */
    public static function errorContractProvider(): iterable
    {
        foreach (self::fixture('error_contracts.json') as $name => $contract) {
            yield $name => [
                $contract['response']['error'],
                PublicationFailure::from($contract['expected_failure']),
                $contract['retryable'],
            ];
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function publicationContracts(): array
    {
        return self::fixture('publication_contracts.json');
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @throws JsonException
     */
    private static function fixture(string $filename): array
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/Fixtures/Meta/'.$filename);

        if ($contents === false) {
            self::fail("Unable to read Meta fixture: {$filename}");
        }

        return json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    }
}
