<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Locution\StartLocutionRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StartLocutionRequestTest extends TestCase
{
    public function test_it_requires_notifications_message_and_at_least_one_hashtag(): void
    {
        $request = new TestableStartLocutionRequest;
        $request->merge([
            'phrase' => ['text' => 'Ao vivo'],
            'program' => 'program-uuid',
            'send_notification' => false,
            'social' => [
                'text' => '',
                'hashtags' => [],
            ],
        ]);
        $request->normalizeSocialData();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('send_notification', $validator->errors()->toArray());
        $this->assertArrayHasKey('social.text', $validator->errors()->toArray());
        $this->assertArrayHasKey('social.hashtags', $validator->errors()->toArray());
    }

    public function test_it_normalizes_and_accepts_the_required_social_payload(): void
    {
        $request = new TestableStartLocutionRequest;
        $request->merge([
            'phrase' => ['text' => 'Ao vivo'],
            'program' => 'program-uuid',
            'send_notification' => true,
            'social' => [
                'text' => '  O Conexão Akiba começou!  ',
                'hashtags' => ['#Rádio', '#Anime', '#Rádio'],
            ],
        ]);
        $request->normalizeSocialData();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), $validator->errors()->first());
        $this->assertSame('O Conexão Akiba começou!', $request->input('social.text'));
        $this->assertSame(['rádio', 'anime'], $request->input('social.hashtags'));
    }

    public function test_it_rejects_more_than_thirty_hashtags(): void
    {
        $request = new TestableStartLocutionRequest;
        $request->merge([
            'phrase' => ['text' => 'Ao vivo'],
            'program' => 'program-uuid',
            'send_notification' => true,
            'social' => [
                'text' => 'O Conexão Akiba começou!',
                'hashtags' => array_map(fn (int $index): string => "tag{$index}", range(1, 31)),
            ],
        ]);
        $request->normalizeSocialData();

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('social.hashtags', $validator->errors()->toArray());
    }
}

class TestableStartLocutionRequest extends StartLocutionRequest
{
    public function normalizeSocialData(): void
    {
        $this->prepareForValidation();
    }
}
