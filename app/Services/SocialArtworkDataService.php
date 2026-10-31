<?php

namespace App\Services;

use App\Models\Onair;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SocialArtworkDataService
{
    private const TITLE_LIMITS = [
        'instagram-feed' => null,
        'instagram-live-feed' => 72,
        'instagram-story' => 80,
        'instagram-live-story' => 72,
        'facebook-feed' => null,
        'facebook-live-feed' => 72,
        'discord-post' => 110,
    ];

    public function map(Post $post, ?Onair $onair = null): array
    {
        $post->loadMissing(['author', 'tags']);
        $onair?->loadMissing(['host', 'program.host']);

        $title = filled($post->title) ? $post->title : 'Conteúdo novo na Rede Akiba';
        $author = $post->author;
        $program = $onair?->program;
        $host = $onair?->host ?? $program?->host;

        return [
            'title' => $title,
            'titles' => collect(self::TITLE_LIMITS)
                ->map(fn (?int $limit) => $limit === null ? $title : $this->limit($title, $limit))
                ->all(),
            'module' => in_array($post->module, ['post', 'review', 'event'], true)
                ? $post->module
                : 'post',
            'category' => $post->tags->first()?->name ?: $this->category($post->module),
            'cover' => $this->image($post->cover ?: $post->image, 'img/placeholders/placeholder.webp'),
            'author' => $this->limit($this->userName($author, 'Rede Akiba'), 40),
            'author_avatar' => $this->image($author?->avatar, 'img/placeholders/avatar.webp'),
            'url' => $this->postUrl($post),
            'published_at' => $this->date($post->created_at),
            'theme' => in_array($post->module, ['post', 'review', 'event'], true)
                ? $post->module
                : 'post',
            'live' => [
                'program' => $this->limit($program?->name ?: 'Programa ao vivo', 72),
                'program_image' => $this->image($program?->image, 'img/placeholders/program.webp'),
                'host' => $this->limit($this->userName($host, 'Equipe Rede Akiba'), 40),
                'host_avatar' => $this->image($host?->avatar, 'img/placeholders/avatar.webp'),
                'status' => 'Ao vivo',
                'call_to_action' => 'Sintonize agora',
                'background' => asset('img/pages/home/backgrounds/featured.svg'),
            ],
            'brand' => [
                'name' => 'Rede Akiba',
                'logo' => asset('img/brand/logo.webp'),
            ],
        ];
    }

    public function mapLive(Onair $onair): array
    {
        $onair->loadMissing(['host', 'program.host']);
        $program = $onair->program;
        $host = $onair->host ?? $program?->host;

        return [
            'live' => [
                'program' => $this->limit($program?->name ?: 'Programa ao vivo', 72),
                'program_image' => $this->image($program?->image, 'img/placeholders/program.webp'),
                'host' => $this->limit($this->userName($host, 'Equipe Rede Akiba'), 40),
                'host_avatar' => $this->image($host?->avatar, 'img/placeholders/avatar.webp'),
                'status' => 'Ao vivo',
                'call_to_action' => 'Sintonize agora',
            ],
        ];
    }

    private function category(?string $module): string
    {
        return match ($module) {
            'review' => 'Review',
            'event' => 'Evento',
            default => 'Notícia',
        };
    }

    private function postUrl(Post $post): string
    {
        $route = match ($post->module) {
            'review' => 'review.read',
            'event' => 'event.read',
            default => 'post.read',
        };

        return route($route, ['slug' => $post->slug]);
    }

    private function date(mixed $date): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date ?? now());

        return mb_strtoupper($date->timezone('America/Sao_Paulo')->translatedFormat('d M Y'));
    }

    private function userName(?User $user, string $fallback): string
    {
        return $user?->nickname ?: $user?->name ?: $fallback;
    }

    private function image(?string $path, string $fallback): string
    {
        if (blank($path)) {
            return asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    private function limit(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        $cut = rtrim(mb_substr($value, 0, $limit - 1));
        $lastSpace = mb_strrpos($cut, ' ');

        if ($lastSpace !== false) {
            $cut = rtrim(mb_substr($cut, 0, $lastSpace));
        }

        return $cut.'…';
    }
}
