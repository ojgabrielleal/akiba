@php
    $artwork = array_replace_recursive([
        'title' => 'Novo projeto de anime ganha janela de estreia e surpreende os fãs',
        'module' => 'post',
        'category' => 'Notícia',
        'cover' => asset('img/placeholders/placeholder.webp'),
        'author' => 'Equipe Rede Akiba',
        'author_avatar' => asset('img/placeholders/avatar.webp'),
        'live' => [
            'program' => 'Conexão Akiba',
            'program_image' => asset('img/placeholders/program.webp'),
            'host' => 'Yagami',
            'host_avatar' => asset('img/placeholders/avatar.webp'),
            'status' => 'Ao vivo',
            'call_to_action' => 'Sintonize agora',
            'background' => asset('img/social-artwork/backgrounds/featured.png'),
        ],
        'url' => url('/'),
        'published_at' => '24 OUT 2026',
        'brand' => [
            'name' => 'Rede Akiba',
            'logo' => asset('img/brand/logo.webp'),
        ],
    ], $artwork ?? []);

    $templates = [
        'instagram-feed' => [
            'label' => 'Feed',
            'width' => 1080,
            'height' => 1350,
        ],
        'instagram-live-feed' => [
            'label' => 'Locução no Feed',
            'width' => 1080,
            'height' => 1350,
        ],
        'facebook-feed' => [
            'label' => 'Facebook',
            'width' => 1080,
            'height' => 1080,
        ],
        'facebook-live-feed' => [
            'label' => 'Locução no Facebook',
            'width' => 1080,
            'height' => 1080,
        ],
        'discord-post' => [
            'label' => 'Discord',
            'width' => 1200,
            'height' => 630,
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artes sociais — {{ $artwork['brand']['name'] }}</title>
    <style>
        @font-face {
            font-family: "Akiba Sans";
            src: local("DejaVu Sans");
            font-display: block;
            font-style: normal;
            font-weight: 100 900;
        }

        :root {
            --akiba-ink: #050505;
            --akiba-night: #000014;
            --akiba-ocean: #002080;
            --akiba-skywave: #0091ff;
            --akiba-citric: #ffaa35;
            --akiba-amber: #ff8000;
            --akiba-aurora: #fffaf3;
            --akiba-crimson: #ed3237;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-width: max-content;
            min-height: 100%;
            background: #111827;
            color: var(--akiba-aurora);
            font-family: "Akiba Sans", "DejaVu Sans", sans-serif;
        }

        img {
            display: block;
        }

        .artwork-sheet {
            display: grid;
            align-items: start;
            gap: 64px;
            padding: 64px;
        }

        .social-artwork {
            isolation: isolate;
            position: relative;
            flex: none;
            overflow: hidden;
            background: var(--akiba-night);
            color: var(--akiba-aurora);
            container-type: size;
        }

        .social-artwork__cover,
        .social-artwork__shade,
        .social-artwork__texture {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .social-artwork__cover {
            z-index: -3;
            object-fit: cover;
        }

        .social-artwork__shade {
            z-index: -2;
            background:
                linear-gradient(180deg, rgba(0, 0, 20, .18) 0%, rgba(0, 0, 20, .34) 42%, rgba(0, 0, 20, .98) 100%),
                linear-gradient(110deg, rgba(0, 32, 128, .82), transparent 62%);
        }

        .social-artwork__texture {
            z-index: -1;
            opacity: .2;
            background-image:
                url("/img/textures/sakura_texture.svg"),
                radial-gradient(circle at 18% 15%, var(--akiba-skywave) 0 3px, transparent 4px),
                linear-gradient(125deg, transparent 0 72%, rgba(255, 170, 53, .18) 72% 73%, transparent 73%);
            background-position: right bottom, 0 0, 0 0;
            background-repeat: no-repeat, repeat, no-repeat;
            background-size: 54% auto, 42px 42px, 100% 100%;
        }

        .social-artwork__frame {
            position: absolute;
            inset: 0;
            border: 14px solid var(--akiba-citric);
            pointer-events: none;
        }

        .social-artwork__jp-stamp {
            position: absolute;
            z-index: 4;
            top: 38px;
            right: 38px;
            display: grid;
            place-items: center;
            width: 76px;
            height: 76px;
            border: 4px solid var(--akiba-aurora);
            background: var(--akiba-crimson);
            color: var(--akiba-aurora);
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            box-shadow: 8px 8px 0 rgba(0, 0, 20, .72);
            transform: rotate(4deg);
        }

        .social-artwork__vertical-copy {
            position: absolute;
            z-index: 3;
            top: 138px;
            right: 42px;
            color: rgba(255, 250, 243, .48);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            writing-mode: vertical-rl;
        }

        .social-artwork__content {
            position: absolute;
            display: flex;
            flex-direction: column;
        }

        .social-artwork__header,
        .social-artwork__meta,
        .social-artwork__footer {
            display: flex;
            align-items: center;
        }

        .social-artwork__header,
        .social-artwork__footer {
            justify-content: flex-start;
        }

        .social-artwork__logo {
            width: auto;
            height: 72px;
            object-fit: contain;
        }

        .social-artwork__body {
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: flex-end;
        }

        .social-artwork__meta {
            gap: 20px;
            margin-bottom: 22px;
            color: var(--akiba-citric);
            font-size: 28px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .social-artwork__meta span:first-child {
            padding: 9px 15px;
            background: var(--akiba-citric);
            color: var(--akiba-night);
            transform: skew(-5deg);
        }

        .social-artwork__meta span + span::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            margin: 0 20px 4px 0;
            border-radius: 999px;
            background: currentColor;
        }

        .social-artwork__title {
            max-width: 920px;
            margin: 0;
            overflow: hidden;
            font-size: 72px;
            font-weight: 900;
            letter-spacing: -.045em;
            line-height: 1.02;
            text-wrap: balance;
            text-shadow: 0 4px 20px rgba(0, 0, 20, .65);
        }

        .social-artwork__footer {
            margin-top: 42px;
            padding-top: 24px;
            border-top: 3px solid rgba(255, 250, 243, .35);
            color: rgba(255, 250, 243, .84);
            font-size: 24px;
            font-weight: 700;
        }

        .social-artwork__author {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .social-artwork__avatar {
            width: 58px;
            height: 58px;
            border: 3px solid var(--akiba-citric);
            border-radius: 999px;
            background: var(--akiba-ocean);
            object-fit: cover;
        }

        [data-social-template="instagram-feed"],
        [data-social-template="instagram-live-feed"] {
            width: 1080px;
            height: 1350px;
        }

        [data-social-template="facebook-feed"],
        [data-social-template="facebook-live-feed"] {
            width: 1080px;
            height: 1080px;
        }

        [data-social-template="instagram-feed"] .social-artwork__content,
        [data-social-template="instagram-live-feed"] .social-artwork__content {
            inset: 72px;
        }

        [data-social-template="instagram-feed"] {
            background: var(--akiba-aurora);
            color: var(--akiba-ocean);
        }

        [data-social-template="instagram-feed"] .social-artwork__cover {
            inset: 40px 40px auto;
            width: calc(100% - 80px);
            height: 740px;
        }

        [data-social-template="instagram-feed"] .social-artwork__shade {
            inset: 40px 40px auto;
            width: calc(100% - 80px);
            height: 740px;
            background: linear-gradient(180deg, rgba(0, 0, 20, .2), transparent 48%, rgba(0, 0, 20, .2));
        }

        [data-social-template="instagram-feed"] .social-artwork__texture,
        [data-social-template="instagram-feed"] .social-artwork__jp-stamp,
        [data-social-template="instagram-feed"] .social-artwork__vertical-copy {
            display: none;
        }

        [data-social-template="instagram-feed"] .social-artwork__frame {
            border: 18px solid var(--akiba-skywave);
            box-shadow: inset 0 0 0 8px var(--akiba-citric);
        }

        [data-social-template="instagram-feed"] .social-artwork__content {
            inset: 0;
        }

        [data-social-template="instagram-feed"] .social-artwork__header {
            position: absolute;
            right: 90px;
            bottom: 54px;
            width: 260px;
            height: 76px;
            justify-content: flex-end;
        }

        [data-social-template="instagram-feed"] .social-artwork__logo {
            max-width: 220px;
            height: 74px;
            mix-blend-mode: multiply;
        }

        [data-social-template="instagram-feed"] .social-artwork__body {
            position: absolute;
            inset: 700px 90px 150px;
            justify-content: flex-start;
            border-top: 14px solid var(--akiba-citric);
            padding-top: 36px;
        }

        [data-social-template="instagram-feed"] .social-artwork__meta {
            align-items: center;
            justify-content: space-between;
            color: var(--akiba-ocean);
            font-size: 24px;
        }

        [data-social-template="instagram-feed"] .social-artwork__meta span:first-child {
            color: var(--akiba-night);
        }

        [data-social-template="instagram-feed"] .social-artwork__meta span + span::before {
            display: none;
        }

        [data-social-template="instagram-feed"] .social-artwork__title {
            color: var(--akiba-ocean);
            font-size: 80px;
            line-height: 1.35;
            text-shadow: none;
        }

        [data-social-template="instagram-feed"] .social-artwork__footer {
            position: absolute;
            right: 90px;
            bottom: 54px;
            left: 90px;
            margin: 0;
            border-color: var(--akiba-skywave);
            color: var(--akiba-night);
            font-size: 28px;
        }

        [data-social-template="instagram-feed"] .social-artwork__author {
            min-height: 76px;
            align-items: center;
            gap: 20px;
        }

        [data-social-template="instagram-feed"] .social-artwork__avatar {
            width: 76px;
            height: 76px;
            border-radius: 999px;
        }

        [data-social-template="instagram-feed"] .social-artwork__title,
        [data-social-template="instagram-live-feed"] .social-artwork__title {
            display: -webkit-box;
            font-size: 82px;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 6;
        }

        [data-social-template="instagram-feed"] .social-artwork__title {
            font-size: 80px;
            -webkit-line-clamp: 6;
        }

        [data-social-template="instagram-story"],
        [data-social-template="instagram-live-story"] {
            width: 1080px;
            height: 1920px;
        }

        [data-social-template="instagram-story"] .social-artwork__content,
        [data-social-template="instagram-live-story"] .social-artwork__content {
            inset: 250px 80px;
        }

        [data-social-template="instagram-story"] .social-artwork__title,
        [data-social-template="instagram-live-story"] .social-artwork__title {
            display: -webkit-box;
            font-size: 92px;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 4;
        }

        [data-social-template="instagram-story"] .social-artwork__frame,
        [data-social-template="instagram-live-story"] .social-artwork__frame {
            border-width: 16px;
        }

        [data-social-template^="instagram-live-"] .social-artwork__frame {
            border-color: var(--akiba-skywave);
        }

        [data-social-template^="instagram-live-"] .social-artwork__meta {
            color: var(--akiba-citric);
        }

        [data-social-template^="instagram-live-"] .social-artwork__avatar {
            border-color: var(--akiba-skywave);
        }

        .live-artwork__body {
            position: relative;
            display: grid;
            flex: 1;
            align-items: center;
            gap: 42px;
        }

        .live-artwork__portrait {
            position: relative;
            z-index: 1;
            width: 430px;
            height: 430px;
            border: 14px solid var(--akiba-citric);
            border-radius: 999px;
            background: var(--akiba-ocean);
            box-shadow: 0 28px 70px rgba(0, 0, 20, .58);
            object-fit: cover;
        }

        .live-artwork__copy {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .live-artwork__eyebrow {
            margin: 0 0 10px;
            font-size: 72px;
            font-weight: 900;
            letter-spacing: -.045em;
            line-height: 1;
            text-transform: uppercase;
        }

        .live-artwork__status {
            margin: 0;
            padding: 16px 28px 20px;
            background: var(--akiba-citric);
            color: var(--akiba-night);
            font-size: 78px;
            font-weight: 900;
            letter-spacing: -.045em;
            line-height: .92;
            text-transform: uppercase;
        }

        .live-artwork__host {
            margin: 22px 0 0;
            color: var(--akiba-aurora);
            font-size: 38px;
            font-weight: 800;
            letter-spacing: .04em;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .live-artwork__program {
            position: absolute;
            z-index: 3;
            right: 0;
            bottom: 0;
            display: grid;
            grid-template-columns: 190px 1fr;
            align-items: center;
            width: 560px;
            min-height: 210px;
            overflow: hidden;
            border: 6px solid var(--akiba-skywave);
            border-radius: 28px;
            background: rgba(0, 0, 20, .9);
            box-shadow: 0 22px 55px rgba(0, 0, 20, .62);
        }

        .live-artwork__program-image {
            width: 190px;
            height: 100%;
            min-height: 210px;
            object-fit: cover;
        }

        .live-artwork__program-copy {
            padding: 24px;
        }

        .live-artwork__program-label {
            display: block;
            margin-bottom: 8px;
            color: var(--akiba-citric);
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .live-artwork__program-name {
            display: -webkit-box;
            margin: 0;
            overflow: hidden;
            font-size: 35px;
            font-weight: 900;
            letter-spacing: -.035em;
            line-height: 1.04;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        [data-social-template="instagram-live-feed"] .live-artwork__body {
            grid-template-columns: 46% 54%;
            padding-bottom: 180px;
        }

        [data-social-template="instagram-live-feed"] .live-artwork__program {
            bottom: 10px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__body {
            grid-template-rows: auto 1fr;
            justify-items: center;
            padding: 70px 0 270px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__portrait {
            width: 560px;
            height: 560px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__copy {
            align-items: center;
            text-align: center;
        }

        [data-social-template="instagram-live-story"] .live-artwork__eyebrow {
            font-size: 96px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__status {
            font-size: 108px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__host {
            font-size: 48px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__program {
            right: 50%;
            width: 650px;
            transform: translateX(50%);
        }

        /* A locução usa uma linguagem própria de transmissão, sem repetir o card editorial. */
        [data-social-template^="instagram-live-"] .social-artwork__shade {
            background:
                linear-gradient(135deg, rgba(0, 0, 54, .2) 0 42%, rgba(0, 0, 20, .92) 42.2% 100%),
                radial-gradient(circle at 18% 26%, rgba(0, 145, 255, .55), transparent 34%),
                linear-gradient(145deg, var(--akiba-ocean), var(--akiba-night));
        }

        [data-social-template^="instagram-live-"] .social-artwork__texture {
            opacity: .32;
            background-image:
                url("/img/textures/leque_texture.svg"),
                radial-gradient(circle, rgba(0, 145, 255, .92) 0 3px, transparent 4px),
                repeating-linear-gradient(120deg, transparent 0 86px, rgba(255, 170, 53, .12) 87px 89px, transparent 90px 176px);
            background-position: right bottom, 0 0, 0 0;
            background-repeat: no-repeat, repeat, no-repeat;
            background-size: 60% auto, 42px 42px, 100% 100%;
        }

        [data-social-template^="instagram-live-"] .social-artwork__jp-stamp {
            background: var(--akiba-citric);
            color: var(--akiba-night);
        }

        [data-social-template^="instagram-live-"] .social-artwork__vertical-copy::before {
            content: "生放送 · ";
            color: var(--akiba-citric);
        }

        [data-social-template^="instagram-live-"] .social-artwork__frame {
            border-color: var(--akiba-skywave);
            box-shadow: inset 0 0 0 8px rgba(0, 0, 20, .82);
        }

        .live-artwork__portrait-shell {
            position: relative;
            z-index: 2;
            width: 450px;
            height: 500px;
            transform: rotate(-4deg);
        }

        .live-artwork__portrait {
            position: absolute;
            inset: 30px 22px 22px 30px;
            width: calc(100% - 52px);
            height: calc(100% - 52px);
            border: 0;
            border-radius: 76px 22px 76px 22px;
            outline: 12px solid var(--akiba-citric);
            outline-offset: 8px;
            background: var(--akiba-ocean);
            box-shadow: 24px 28px 0 var(--akiba-skywave), 0 34px 70px rgba(0, 0, 20, .66);
            object-fit: cover;
        }

        .live-artwork__orbit {
            position: absolute;
            z-index: -1;
            border: 5px solid var(--akiba-skywave);
            border-left-color: transparent;
            border-radius: 999px;
        }

        .live-artwork__orbit--one {
            inset: -36px -50px 34px 18px;
            transform: rotate(18deg);
        }

        .live-artwork__orbit--two {
            inset: 24px -86px -28px -30px;
            border-color: rgba(255, 170, 53, .7);
            border-right-color: transparent;
            transform: rotate(-12deg);
        }

        .live-artwork__portrait-tag {
            position: absolute;
            right: -44px;
            bottom: 2px;
            z-index: 3;
            max-width: 380px;
            padding: 14px 22px;
            overflow: hidden;
            background: var(--akiba-aurora);
            color: var(--akiba-night);
            font-size: 28px;
            font-weight: 900;
            letter-spacing: .04em;
            line-height: 1;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
            transform: rotate(4deg);
        }

        .live-artwork__status {
            position: relative;
            padding: 12px 28px 18px;
            background: var(--akiba-citric);
            box-shadow: none;
            font-size: 86px;
        }

        .live-artwork__now {
            margin: 0 0 12px;
            color: var(--akiba-aurora);
            font-size: 86px;
            font-weight: 900;
            letter-spacing: -.045em;
            line-height: .92;
            text-transform: uppercase;
        }

        [data-social-template="instagram-live-feed"] .live-artwork__body {
            grid-template-columns: 49% 51%;
            padding: 34px 0 210px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__body {
            grid-template-rows: 610px 1fr;
            padding: 44px 0 280px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__portrait-shell {
            width: 560px;
            height: 610px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__portrait {
            width: calc(100% - 52px);
            height: calc(100% - 52px);
        }

        [data-social-template="instagram-live-story"] .live-artwork__copy {
            align-items: center;
            justify-self: center;
            margin-left: 0;
            text-align: center;
        }

        [data-social-template="instagram-live-story"] .live-artwork__status {
            font-size: 118px;
        }

        [data-social-template="instagram-live-story"] .live-artwork__now {
            font-size: 118px;
        }

        [data-social-template="discord-post"] {
            width: 1200px;
            height: 630px;
        }

        [data-social-template="discord-post"] .social-artwork__content {
            inset: 48px;
        }

        [data-social-template="discord-post"] .social-artwork__logo {
            height: 52px;
        }

        [data-social-template="discord-post"] .social-artwork__meta {
            margin-bottom: 12px;
            font-size: 18px;
        }

        [data-social-template="discord-post"] .social-artwork__title {
            display: -webkit-box;
            max-width: 1040px;
            font-size: 52px;
            line-height: 1.06;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }

        [data-social-template="discord-post"] .social-artwork__footer {
            display: none;
        }

        [data-social-template="discord-post"] .social-artwork__frame {
            border-width: 8px;
        }

        [data-social-template="discord-post"] .social-artwork__jp-stamp {
            top: 24px;
            right: 24px;
            width: 58px;
            height: 58px;
            border-width: 3px;
            font-size: 22px;
            box-shadow: 5px 5px 0 rgba(0, 0, 20, .72);
        }

        [data-social-template="discord-post"] .social-artwork__vertical-copy {
            display: none;
        }

    </style>
</head>
<body>
    <main class="artwork-sheet" aria-label="Modelos de artes sociais da Rede Akiba">
        @foreach ($templates as $template => $dimensions)
            @php($isLive = str_contains($template, '-live-'))
            <section
                id="{{ $template }}"
                class="social-artwork"
                data-social-template="{{ $template }}"
                data-width="{{ $dimensions['width'] }}"
                data-height="{{ $dimensions['height'] }}"
                aria-label="Modelo {{ $dimensions['label'] }} — {{ $dimensions['width'] }} por {{ $dimensions['height'] }} pixels"
            >
                <img
                    class="social-artwork__cover"
                    src="{{ $isLive ? $artwork['live']['background'] : $artwork['cover'] }}"
                    alt=""
                    width="{{ $dimensions['width'] }}"
                    height="{{ $dimensions['height'] }}"
                    decoding="sync"
                >
                <div class="social-artwork__shade" aria-hidden="true"></div>
                <div class="social-artwork__texture" aria-hidden="true"></div>
                <div class="social-artwork__frame" aria-hidden="true"></div>
                <span class="social-artwork__jp-stamp" aria-hidden="true">秋葉</span>
                <span class="social-artwork__vertical-copy" aria-hidden="true">アニメ · マンガ · 音楽 · 日本</span>

                <div class="social-artwork__content">
                    <header class="social-artwork__header">
                        <img
                            class="social-artwork__logo"
                            src="{{ $artwork['brand']['logo'] }}"
                            alt="{{ $artwork['brand']['name'] }}"
                        >
                    </header>

                    @if ($isLive)
                        <div class="live-artwork__body">
                            <div class="live-artwork__portrait-shell">
                                <span class="live-artwork__orbit live-artwork__orbit--one" aria-hidden="true"></span>
                                <span class="live-artwork__orbit live-artwork__orbit--two" aria-hidden="true"></span>
                                <img
                                    class="live-artwork__portrait"
                                    src="{{ $artwork['live']['host_avatar'] }}"
                                    alt=""
                                >
                                <span class="live-artwork__portrait-tag">DJ {{ $artwork['live']['host'] }}</span>
                            </div>
                            <div class="live-artwork__copy">
                                <p class="live-artwork__now">Agora</p>
                                <p class="live-artwork__status">No ar</p>
                            </div>
                        </div>
                    @else
                        <div class="social-artwork__body">
                            <div class="social-artwork__meta">
                                <span>{{ $artwork['category'] }}</span>
                                <span>{{ $artwork['published_at'] }}</span>
                            </div>
                            <h1 class="social-artwork__title">{{ $artwork['titles'][$template] ?? $artwork['title'] }}</h1>
                        </div>

                        <footer class="social-artwork__footer">
                            <div class="social-artwork__author">
                                <img
                                    class="social-artwork__avatar"
                                    src="{{ $artwork['author_avatar'] }}"
                                    alt=""
                                    width="58"
                                    height="58"
                                >
                                <span>{{ $artwork['author'] }}</span>
                            </div>
                        </footer>
                    @endif
                </div>
            </section>
        @endforeach
    </main>

    <script>
        Promise.all([
            document.fonts.ready,
            ...Array.from(document.images).map((image) => image.complete
                ? Promise.resolve()
                : new Promise((resolve) => {
                    image.addEventListener("load", resolve, { once: true });
                    image.addEventListener("error", resolve, { once: true });
                })),
        ]).then(() => {
            document.documentElement.dataset.socialArtworkReady = "true";
        });
    </script>
</body>
</html>
