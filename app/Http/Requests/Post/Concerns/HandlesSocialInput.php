<?php

namespace App\Http\Requests\Post\Concerns;

use App\Rules\RespectfulContent;
use Illuminate\Support\Str;

trait HandlesSocialInput
{
    protected function prepareSocialForValidation(): void
    {
        $social = $this->input('social');

        if (! is_array($social)) {
            return;
        }

        if (is_string($social['text'] ?? null)) {
            $social['text'] = trim($social['text']);
        }

        if (is_array($social['hashtags'] ?? null)) {
            $normalizedHashtags = [];
            $seenHashtags = [];

            foreach ($social['hashtags'] as $hashtag) {
                if (! is_string($hashtag)) {
                    $normalizedHashtags[] = $hashtag;

                    continue;
                }

                $hashtag = Str::lower(trim($hashtag));

                if ($hashtag === '' || in_array($hashtag, $seenHashtags, true)) {
                    continue;
                }

                $seenHashtags[] = $hashtag;
                $normalizedHashtags[] = $hashtag;
            }

            $social['hashtags'] = $normalizedHashtags;
        }

        if (! array_key_exists('channels', $social)) {
            $social['channels'] = [
                'instagram' => true,
                'facebook' => true,
            ];
        } elseif (is_array($social['channels'])) {
            $social['channels'] = [
                'instagram' => true,
                'facebook' => true,
                ...$social['channels'],
            ];
        }

        $this->merge(['social' => $social]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function socialRules(): array
    {
        $required = $this->isDraft() ? 'nullable' : 'required';
        $channelRule = $this->isDraft() ? 'boolean' : 'accepted';

        return [
            'social' => [$required, 'array'],
            'social.text' => [$required, 'string', 'max:1800', new RespectfulContent],
            'social.hashtags' => ['nullable', 'array', 'max:30'],
            'social.hashtags.*' => ['string', 'max:51', 'regex:/^#[\pL\pN_]+$/u'],
            'social.artwork_cover' => ['nullable', 'image', 'dimensions:min_width=1200,min_height=400'],
            'social.channels' => [$required, 'array'],
            'social.channels.instagram' => [$required, $channelRule],
            'social.channels.facebook' => [$required, $channelRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function socialMessages(): array
    {
        return [
            'social.required' => 'Informe os dados para divulgação nas redes sociais.',
            'social.array' => 'Os dados das redes sociais são inválidos.',
            'social.text.required' => 'Informe o texto para as redes sociais.',
            'social.text.string' => 'O texto para as redes sociais é inválido.',
            'social.text.max' => 'O texto para as redes sociais pode ter no máximo 1.800 caracteres.',
            'social.hashtags.array' => 'As hashtags precisam ser enviadas como uma lista.',
            'social.hashtags.max' => 'Use no máximo 30 hashtags.',
            'social.hashtags.*.string' => 'A hashtag informada é inválida.',
            'social.hashtags.*.max' => 'Cada hashtag pode ter no máximo 50 caracteres além do #.',
            'social.hashtags.*.regex' => 'Comece cada hashtag com # e use apenas letras, números ou sublinhado.',
            'social.artwork_cover.uploaded' => 'A imagem das artes sociais não foi enviada corretamente.',
            'social.artwork_cover.image' => 'O arquivo das artes sociais precisa ser uma imagem.',
            'social.artwork_cover.dimensions' => 'A imagem das artes sociais precisa ter pelo menos 1200×400 pixels.',
            'social.channels.required' => 'Informe os canais de divulgação.',
            'social.channels.array' => 'Os canais de divulgação são inválidos.',
            'social.channels.instagram.required' => 'Informe se o conteúdo deve ser publicado no Instagram.',
            'social.channels.instagram.accepted' => 'A publicação no Instagram é obrigatória.',
            'social.channels.facebook.required' => 'Informe se o conteúdo deve ser publicado no Facebook.',
            'social.channels.facebook.accepted' => 'A publicação no Facebook é obrigatória.',
        ];
    }
}
