<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/public";

    const animeFrames = [
        {
            name: "Minha ideia",
            image: "https://konosuba.com/1st/story/img/03/1.jpg",
            lastSeen: "colocar a Aqua em uma gaiola",
            position: "object-center",
        },
        {
            name: "O que a Aqua fez",
            image: "https://konosuba.com/1st/story/img/05/3.jpg",
            lastSeen: "começou a purificar a água desesperada",
            position: "object-center",
        },
        {
            name: "Como sempre, a Megumin...",
            image: "https://konosuba.com/1st/story/img/02/4.jpg",
            lastSeen: "quis explodir tudo",
            position: "object-center",
        },
        {
            name: "Darkness sendo Darkness",
            image: "https://konosuba.com/1st/story/img/07/3.jpg",
            lastSeen: "achou o desastre suspeitamente interessante",
            position: "object-center",
        },
    ];

    $: ({ onair, stream, flash, oauth } = $page.props);
    $: pageUrl = $page.url;
</script>

<Meta meta={{
    title: "404 - Deu tudo errado!",
    description: "Kazuma tenta explicar como uma ideia boa virou Aqua dentro de um sapo e uma página 404.",
    robots: "noindex, follow",
}} />

<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="relative isolate overflow-hidden bg-blue-night text-suspense-aurora">
        <div class="absolute inset-0 opacity-[0.12]" style="background-image: url('/img/textures/stars.webp');" aria-hidden="true"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_74%_44%,rgba(0,145,255,0.38)_0%,rgba(0,32,128,0.2)_28%,rgba(0,0,20,0.96)_62%),linear-gradient(110deg,rgba(0,0,20,0.98)_0%,rgba(0,0,54,0.88)_54%,rgba(0,32,128,0.56)_100%)]" aria-hidden="true"></div>
        <div class="portal-ring absolute right-[7%] top-[10%] hidden size-72 rounded-full border border-blue-skywave/30 lg:block" aria-hidden="true"></div>
        <div class="portal-ring portal-ring-delay absolute right-[10%] top-[15%] hidden size-52 rounded-full border border-orange-amber/30 lg:block" aria-hidden="true"></div>

        <div class="container-page relative grid min-h-[calc(100vh-9rem)] items-center gap-6 py-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(20rem,1.05fr)] lg:py-8">
            <div class="min-w-0">
                <p class="font-noto-sans text-[clamp(4.2rem,12vw,8.4rem)] font-black leading-[0.78] text-suspense-aurora drop-shadow-[0_0_28px_rgba(0,145,255,0.35)]">
                    404
                </p>

                <h1 class="mt-5 max-w-2xl font-noto-sans text-[1.4rem] font-black leading-tight uppercase italic text-suspense-aurora sm:text-2xl lg:text-3xl">
                    Eu tive uma boa ideia: colocar a Aqua em uma gaiola.
                </h1>

                <p class="my-3 max-w-xl font-noto-sans text-sm font-semibold leading-6 text-blue-skywave">
                    Olá, aqui é o Kazuma. Eu explico: a ideia era perfeita, a Aqua se desesperou...
                </p>

                <div class="my-5 max-w-xl">
                    <div class="mb-2 flex items-center justify-between gap-3 border-b border-orange-amber/45 pb-1.5">
                        <h2 class="font-noto-sans text-[0.68rem] font-black uppercase italic tracking-[0.16em] text-orange-amber">
                            Minhas últimas provas antes da guilda ignorar meu depoimento
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {#each animeFrames as frame}
                            <div class="group/frame relative aspect-video overflow-hidden rounded-md border border-orange-amber/30 bg-blue-night shadow-[0_0_18px_rgba(0,145,255,0.12)]">
                                <img
                                    src={frame.image}
                                    alt={frame.name}
                                    class={["h-full w-full object-cover opacity-88 transition duration-300 ease-out group-hover/frame:scale-[1.04] group-hover/frame:opacity-100 motion-reduce:transform-none motion-reduce:transition-none", frame.position]}
                                />
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,20,0)_0%,rgba(0,0,20,0.38)_48%,rgba(0,0,20,0.88)_100%)]"></div>
                                <div class="absolute inset-x-1 bottom-1">
                                    <p class="font-noto-sans text-[0.58rem] font-black uppercase italic text-orange-amber">
                                        {frame.name}
                                    </p>
                                    <p class="truncate font-noto-sans text-[0.52rem] font-black uppercase italic text-suspense-aurora/80">
                                        {frame.lastSeen}
                                    </p>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <Link
                        href="/site"
                        class="inline-flex min-h-9 min-w-34 items-center justify-center rounded-full bg-orange-citric px-5 py-1.5 font-noto-sans text-sm font-extrabold uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-110 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                    >
                        Voltar com a derrota
                    </Link>
                </div>
            </div>

            <div class="relative mx-auto aspect-[4/3] w-full max-w-[41rem]" aria-hidden="true">
                <div class="absolute inset-0 overflow-hidden rounded-md border border-blue-skywave/45 bg-blue-marinho shadow-2xl shadow-blue-night/70">
                    <img
                        src="https://josefcd904.wordpress.com/wp-content/uploads/2017/01/konosuba-s2-ep-2-pic-3.jpg?w=736"
                        alt=""
                        class="h-full w-full object-cover object-center"
                    />
                    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(0,0,20,0.18)_0%,rgba(0,0,20,0.42)_100%),linear-gradient(180deg,rgba(0,0,20,0)_34%,rgba(0,0,20,0.88)_100%)]"></div>
                    <div class="absolute inset-x-0 top-1/2 h-px bg-orange-amber/80 shadow-[0_0_24px_rgba(255,128,0,0.75)]"></div>
                    <div class="absolute left-5 top-5 border-l-2 border-orange-amber pl-3 font-noto-sans text-xs font-black uppercase italic tracking-[0.14em] text-suspense-aurora">
                        você chegou tarde
                    </div>
                    <div class="absolute bottom-5 left-5 max-w-58 rounded-md border border-suspense-aurora/20 bg-blue-night/90 px-4 py-3 font-noto-sans text-sm font-black uppercase italic text-blue-skywave">
                        Plano original:
                        <span class="block text-orange-amber">resgatar a página. Não resgatar a Aqua do sapo</span>
                    </div>
                </div>

                <div class="absolute -right-4 top-5 z-20 rotate-[5deg] rounded-md border border-orange-amber bg-blue-night px-4 py-3 shadow-xl">
                    <p class="font-noto-sans text-lg font-black uppercase italic leading-none text-orange-amber">
                        minha ideia era boa
                    </p>
                </div>

                <div class="absolute -bottom-5 right-8 z-20 max-w-52 rotate-[4deg] rounded-md border border-orange-amber bg-blue-night px-3 py-2 text-center font-noto-sans text-[0.7rem] font-black uppercase italic leading-tight text-suspense-aurora shadow-xl">
                    será que ainda vamos receber?
                </div>
            </div>
        </div>
    </section>
</Layout>

<style>
    .rune-grid {
        background:
            repeating-linear-gradient(
                0deg,
                rgba(230, 242, 255, 0.12) 0,
                rgba(230, 242, 255, 0.12) 1px,
                transparent 1px,
                transparent 12px
            ),
            repeating-linear-gradient(
                90deg,
                rgba(255, 128, 0, 0.2) 0,
                rgba(255, 128, 0, 0.2) 1px,
                transparent 2px,
                transparent 16px
            );
        animation: static-shift 900ms steps(4) infinite;
    }

    .magic-stream {
        background:
            linear-gradient(
                180deg,
                transparent 0%,
                rgba(0, 145, 255, 0.18) 54%,
                rgba(255, 128, 0, 0.18) 100%
            ),
            repeating-linear-gradient(
                115deg,
                transparent 0,
                transparent 11px,
                rgba(255, 250, 243, 0.18) 12px,
                transparent 13px
            );
        animation: mana-flow 1.3s linear infinite;
    }

    .equalizer-bar {
        animation: bounce-signal 1.15s ease-in-out infinite alternate;
    }

    .equalizer-bar:nth-child(2n) {
        animation-delay: 160ms;
    }

    .equalizer-bar:nth-child(3n) {
        animation-delay: 310ms;
    }

    @keyframes static-shift {
        0% {
            transform: translate3d(0, 0, 0);
        }

        100% {
            transform: translate3d(-10px, 7px, 0);
        }
    }

    @keyframes mana-flow {
        from {
            background-position:
                0 0,
                0 0;
        }

        to {
            background-position:
                0 0,
                -22px 52px;
        }
    }

    @keyframes bounce-signal {
        from {
            transform: scaleY(0.55);
            opacity: 0.72;
        }

        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rune-grid,
        .magic-stream,
        .equalizer-bar {
            animation: none;
        }
    }
</style>
