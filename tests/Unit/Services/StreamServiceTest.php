<?php

namespace Tests\Unit\Services;

use App\Integrations\StreamService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StreamServiceTest extends TestCase
{
    #[DataProvider('metadataProvider')]
    public function test_it_normalizes_radio_metadata_without_corrupting_valid_text(string $input, string $expected): void
    {
        $this->assertSame($expected, (new StreamService)->normalizeRadioMetadata($input));
    }

    public static function metadataProvider(): array
    {
        return [
            'ascii text' => [
                'Avril Lavigne - Complicated',
                'Avril Lavigne - Complicated',
            ],
            'valid utf8 text' => [
                '矢田悠祐 - 晴レ色メロディー',
                '矢田悠祐 - 晴レ色メロディー',
            ],
            'escaped utf8 text' => [
                '\xE7\x9F\xA2\xE7\x94\xB0\xE6\x82\xA0\xE7\xA5\x90 - \xE6\x99\xB4\xE3\x83\xAC\xE8\x89\xB2\xE3\x83\xA1\xE3\x83\xAD\xE3\x83\x87\xE3\x82\xA3\xE3\x83\xBC',
                '矢田悠祐 - 晴レ色メロディー',
            ],
            'mixed ascii and escaped utf8 text' => [
                'MARGINAL#4\xE3\x80\x90\xE6\x96\xB0\xE6\x9B\xB2\xE3\x80\x91',
                'MARGINAL#4【新曲】',
            ],
            'invalid incomplete escape text' => [
                'Teste \xE7\xZZ exemplo',
                'Teste \xE7\xZZ exemplo',
            ],
        ];
    }

    public function test_it_normalizes_current_and_next_song_metadata_from_stream_response(): void
    {
        Config::set('services.stream.metadata', 'https://stream.example.test/metadata');

        Http::fake([
            'https://stream.example.test/metadata' => Http::response([
                'status' => 'Ligado',
                'ouvintes_conectados' => 12,
                'plano_bitrate' => '128Kbps',
                'musica_atual' => '\xE7\x9F\xA2\xE7\x94\xB0\xE6\x82\xA0\xE7\xA5\x90 - \xE6\x99\xB4\xE3\x83\xAC\xE8\x89\xB2\xE3\x83\xA1\xE3\x83\xAD\xE3\x83\x87\xE3\x82\xA3\xE3\x83\xBC',
                'proxima_musica' => 'MARGINAL#4\xE3\x80\x90\xE6\x96\xB0\xE6\x9B\xB2\xE3\x80\x91',
                'capa_musica' => 'https://example.test/cover.jpg',
            ]),
        ]);

        $data = (new StreamService)->data();

        $this->assertSame('矢田悠祐 - 晴レ色メロディー', $data['current_song']['music']);
        $this->assertSame('MARGINAL#4【新曲】', $data['next_song']['music']);
        $this->assertSame('https://example.test/cover.jpg', $data['current_song']['cover']);
    }
}
