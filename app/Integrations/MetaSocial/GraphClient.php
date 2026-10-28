<?php

namespace App\Integrations\MetaSocial;

use DateTimeImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class GraphClient implements Publisher
{
    private const BASE_URL = 'https://graph.instagram.com';

    public function __construct(
        private readonly PublicationConfiguration $configuration,
    ) {}

    public function publish(
        PublicationChannel $channel,
        PublicationData $publication,
    ): PublicationResult {
        $this->configuration->ensureReady($channel);

        return match ($channel) {
            PublicationChannel::Instagram => $this->publishInstagram($publication),
        };
    }

    private function publishInstagram(PublicationData $publication): PublicationResult
    {
        $containerData = [
            'image_url' => $publication->imageUrl,
        ];

        if ($publication->placement === PublicationPlacement::Story) {
            $containerData['media_type'] = 'STORIES';
        } else {
            $containerData['caption'] = $this->instagramCaption($publication);
        }

        $containerResponse = $this->post(
            $this->configuredPath('instagram_user_id', 'media'),
            $containerData,
        );
        $containerId = $this->requiredId($containerResponse);

        $this->waitForContainer($containerId);

        $publicationResponse = $this->post($this->configuredPath('instagram_user_id', 'media_publish'), [
            'creation_id' => $containerId,
        ]);

        return new PublicationResult(
            channel: PublicationChannel::Instagram,
            externalId: $this->requiredId($publicationResponse),
            publishedAt: new DateTimeImmutable,
        );
    }

    /**
     * @param  array<string, string>  $data
     */
    private function post(string $path, array $data): Response
    {
        try {
            return $this->validateResponse($this->request()->post($path, $data));
        } catch (ConnectionException $exception) {
            throw $this->connectionException($exception);
        }
    }

    /**
     * @param  array<string, string>  $query
     */
    private function get(string $path, array $query): Response
    {
        try {
            return $this->validateResponse($this->request()->get($path, $query));
        } catch (ConnectionException $exception) {
            throw $this->connectionException($exception);
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken((string) config('services.meta.access_token'))
            ->acceptJson()
            ->asForm()
            ->connectTimeout((float) config('services.meta.connect_timeout', 5))
            ->timeout((float) config('services.meta.timeout', 15));
    }

    private function validateResponse(Response $response): Response
    {
        $body = $response->json();

        if ($response->successful() && is_array($body) && ! isset($body['error'])) {
            return $response;
        }

        $error = is_array($body) && is_array($body['error'] ?? null)
            ? $body['error']
            : [];
        $failure = GraphErrorClassifier::classify($error);

        if ($failure === PublicationFailure::Unknown && $response->serverError()) {
            $failure = PublicationFailure::Temporary;
        }

        throw new PublicationException(
            message: $this->errorMessage($response, $error, $failure),
            failure: $failure,
            httpStatus: $response->status(),
            providerCode: isset($error['code']) ? (string) $error['code'] : null,
        );
    }

    private function requiredId(Response $response): string
    {
        $id = $response->json('id');

        if (! is_string($id) || $id === '') {
            throw new PublicationException(
                message: 'Meta Graph API returned an invalid response.',
                failure: PublicationFailure::Unknown,
                httpStatus: $response->status(),
            );
        }

        return $id;
    }

    private function waitForContainer(string $containerId): void
    {
        $attempts = max(1, (int) config('services.meta.container_poll_attempts', 10));
        $intervalMilliseconds = max(0, (int) config('services.meta.container_poll_interval_ms', 2000));

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $response = $this->get($containerId, [
                'fields' => 'status_code,status',
            ]);
            $status = $response->json('status_code');

            if ($status === 'FINISHED') {
                return;
            }

            if ($status !== 'IN_PROGRESS' || $attempt === $attempts) {
                $this->throwContainerNotReady($response, $status);
            }

            usleep($intervalMilliseconds * 1000);
        }
    }

    private function throwContainerNotReady(Response $response, mixed $status): never
    {
        $failure = in_array($status, ['IN_PROGRESS', 'EXPIRED'], true)
            ? PublicationFailure::Temporary
            : PublicationFailure::Validation;

        throw new PublicationException(
            message: 'Instagram media container is not ready for publication.',
            failure: $failure,
            httpStatus: $response->status(),
            providerCode: is_string($status) ? $status : null,
        );
    }

    private function connectionException(ConnectionException $exception): PublicationException
    {
        return new PublicationException(
            message: 'Meta Graph API is temporarily unavailable.',
            failure: PublicationFailure::Temporary,
            previous: $exception,
        );
    }

    private function baseUrl(): string
    {
        return self::BASE_URL.'/'.trim((string) config('services.meta.graph_version'), '/');
    }

    private function configuredPath(string $setting, string $endpoint): string
    {
        return sprintf('%s/%s', trim((string) config("services.meta.{$setting}"), '/'), $endpoint);
    }

    /**
     * @param  array<string, mixed>  $error
     */
    private function errorMessage(
        Response $response,
        array $error,
        PublicationFailure $failure,
    ): string {
        $details = array_filter([
            'HTTP '.$response->status(),
            isset($error['code']) ? 'code '.(string) $error['code'] : null,
            isset($error['error_subcode']) ? 'subcode '.(string) $error['error_subcode'] : null,
            isset($error['type']) ? 'type '.$this->sanitizeProviderText((string) $error['type']) : null,
            isset($error['message']) ? 'message: '.$this->sanitizeProviderText((string) $error['message']) : null,
            isset($error['fbtrace_id']) ? 'fbtrace_id '.$this->sanitizeProviderText((string) $error['fbtrace_id']) : null,
        ]);

        return sprintf(
            'Meta Graph API request failed (%s). %s',
            $failure->value,
            implode('; ', $details),
        );
    }

    private function sanitizeProviderText(string $value): string
    {
        $accessToken = (string) config('services.meta.access_token');

        $sanitized = $accessToken === ''
            ? $value
            : str_replace($accessToken, '[REDACTED]', $value);

        return mb_substr($sanitized, 0, 1000);
    }

    private function instagramCaption(PublicationData $publication): string
    {
        return $this->joinCaption([
            $publication->caption,
            $this->hashtags($publication),
        ]);
    }

    /**
     * @param  list<string>  $parts
     */
    private function joinCaption(array $parts): string
    {
        return implode("\n\n", array_values(array_filter($parts, fn (string $part): bool => $part !== '')));
    }

    private function hashtags(PublicationData $publication): string
    {
        return implode(' ', array_map(
            fn (string $hashtag): string => '#'.ltrim($hashtag, '#'),
            array_filter($publication->hashtags, fn (string $hashtag): bool => $hashtag !== ''),
        ));
    }
}
