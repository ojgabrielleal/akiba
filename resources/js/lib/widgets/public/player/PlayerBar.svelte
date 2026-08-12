<script>
    import { onDestroy, onMount, tick } from "svelte";
    import { fly } from "svelte/transition";
    import { AuthGuard, CustomModal, LoadingSpinner } from "@/lib/components/public";
    import { player, setVolume, toggleAudio } from "@/lib/stores";
    import { SongRequestForm } from "@/lib/widgets/public";
    import {
        listenForOAuthAction,
        OAuthAction,
    } from "@/lib/utils";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let onair = null;
    export let stream = null;
    export let pageUrl = null;
    export let oauth = {};

    let visible = true;
    let observer;
    let mounted = false;
    let modalRef;
    let stopListeningForOAuthAction = () => {};

    $: air = onair?.data?.[0] ?? {};
    $: currentSong = stream?.current_song ?? {};
    $: program = air?.program ?? {};
    $: host = program?.host ?? {};
    $: canRender = Boolean(onair?.data?.[0]);

    const observeMainPlayer = async () => {
        await tick();
        observer?.disconnect();

        const mainPlayer = document.querySelector("[data-main-player]");

        if (!mainPlayer) {
            visible = true;
            return;
        }

        observer = new IntersectionObserver(
            ([entry]) => {
                visible = !entry.isIntersecting;
            },
            { threshold: 0.15 },
        );

        observer.observe(mainPlayer);
    };

    $: if (mounted && pageUrl) {
        observeMainPlayer();
    }

    onMount(() => {
        mounted = true;
        observeMainPlayer();
        stopListeningForOAuthAction = listenForOAuthAction(
            OAuthAction.OPEN_SONG_REQUEST,
            () => modalRef.open(),
        );
    });

    onDestroy(() => {
        observer?.disconnect();
        stopListeningForOAuthAction();

        if (typeof window !== "undefined") {
            window.dispatchEvent(new CustomEvent("akiba:player-bar-visibility", {
                detail: { visible: false },
            }));
        }
    });

    $: if (mounted && typeof window !== "undefined") {
        window.dispatchEvent(new CustomEvent("akiba:player-bar-visibility", {
            detail: { visible: canRender && visible },
        }));
    }
</script>

{#if canRender && visible}
    <CustomModal bind:this={modalRef}>
        <div slot="content" let:close>
            <AuthGuard
                title="Entre para pedir sua música"
                description="Use sua conta para continuar."
                action={OAuthAction.OPEN_SONG_REQUEST}
                {oauth}
            >
                <SongRequestForm {close} {oauth} />
            </AuthGuard>
        </div>
    </CustomModal>

    <aside
        class="fixed inset-x-0 bottom-0 z-60 border-t border-blue-skywave/20 bg-blue-night/95 backdrop-blur-md"
        transition:fly={{ y: 88, duration: 240 }}
    >
        {#if $player.playing && !$player.loading}
            <div
                class="player-wave pointer-events-none absolute inset-x-0 top-3 bottom-0 hidden items-end sm:flex"
                aria-hidden="true"
            >
                {#each $player.waveLevels as level}
                    <span
                        class="player-wave__bar"
                            style={`height: ${Math.round(8 + level * 82)}%;`}
                    ></span>
                {/each}
            </div>
        {/if}

        <div class="container-page relative grid min-h-18 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 py-2 lg:grid-cols-[auto_minmax(0,1fr)_auto_auto_auto]">
            <img
                src={resolvePlaceholderImage(currentSong.cover, "placeholder")}
                alt=""
                aria-hidden="true"
                class="relative z-10 size-12 rounded-md object-cover"
            />

            <div class="relative z-10 min-w-0 font-noto-sans uppercase italic">
                <p class="text-[0.65rem] font-black tracking-[0.12em] text-orange-amber">
                    Tocando agora
                </p>
                <p class="truncate text-sm font-black text-suspense-aurora sm:text-base">
                    {currentSong.music || "Estamos offline"}
                </p>
                <p class="truncate text-[0.7rem] font-bold text-suspense-aurora/50">
                    {program.name} {host.nickname ? `· ${host.nickname}` : ""}
                </p>
            </div>

            <button
                type="button"
                aria-label="Faça seu pedido"
                disabled={!air?.allows_song_requests}
                class={[
                    "relative z-10 hidden size-11 cursor-pointer items-center justify-center rounded-full transition duration-200 ease-out hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:scale-95 disabled:cursor-not-allowed disabled:bg-suspense-aurora/20 disabled:opacity-45 motion-reduce:transform-none motion-reduce:transition-none lg:flex",
                    { "bg-orange-citric": air?.allows_song_requests },
                    { "bg-suspense-aurora/20": !air?.allows_song_requests },
                ]}
                on:click={() => modalRef.open()}
            >
                <img
                    src="/svg/telegram.svg"
                    alt=""
                    aria-hidden="true"
                    class="size-5 brightness-0"
                />
            </button>

            <div class="group/volume relative z-10 hidden py-3 lg:block">
                <button
                    type="button"
                    aria-label={`Volume ${Math.round($player.volume * 100)}%`}
                    class={[
                        "flex size-11 cursor-pointer items-center justify-center rounded-full transition duration-200 ease-out hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:scale-95 motion-reduce:transform-none motion-reduce:transition-none",
                        { "bg-orange-citric": !$player.playing },
                        { "bg-blue-skywave": $player.playing },
                        { "cursor-wait": $player.loading },
                    ]}
                >
                    <img
                        src="/svg/volume.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-5 brightness-0"
                    />
                </button>
                <div class="absolute top-1/2 right-full z-30 hidden w-44 -translate-y-1/2 pr-3 group-hover/volume:block group-focus-within/volume:block">
                    <div class="rounded-md border border-blue-skywave/20 bg-blue-night px-3 py-2 shadow-lg shadow-blue-night/40">
                        <div class="mb-2 flex items-center justify-between font-noto-sans text-[0.65rem] font-black uppercase">
                            <span class="text-suspense-aurora/45">Volume</span>
                            <span class="text-orange-morning">{Math.round($player.volume * 100)}%</span>
                        </div>
                        <input
                            id="player-bar-volume"
                            name="player-bar-volume"
                            type="range"
                            min="0"
                            max="1"
                            step="0.01"
                            value={$player.volume}
                            class="w-full cursor-pointer accent-orange-citric"
                            on:input={(event) => setVolume(event.currentTarget.value)}
                        />
                    </div>
                </div>
            </div>

            <button
                type="button"
                aria-label={$player.loading ? "Carregando rádio" : $player.playing ? "Pausar rádio" : "Tocar rádio"}
                aria-busy={$player.loading}
                disabled={$player.loading && !$player.playing}
                class={[
                    "relative z-10",
                    "flex size-11 cursor-pointer items-center justify-center rounded-full transition duration-200 ease-out hover:scale-105 active:scale-95 motion-reduce:transform-none motion-reduce:transition-none",
                    { "bg-orange-citric": !$player.playing },
                    { "bg-blue-skywave": $player.playing },
                    { "cursor-wait": $player.loading },
                ]}
                on:click={toggleAudio}
            >
                {#if $player.loading}
                    <LoadingSpinner size="sm" tone="dark" label="Carregando rádio" />
                {:else}
                    <img
                        src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                        alt=""
                        aria-hidden="true"
                        class="w-4"
                    />
                {/if}
            </button>
        </div>
    </aside>
{/if}

<style>
    .player-wave {
        gap: 1px;
    }

    .player-wave__bar {
        min-width: 1px;
        flex: 1;
        position: relative;
        background: rgba(39, 55, 82, 0.36);
        transform-origin: bottom;
        transition: height 74ms ease-out;
    }

    .player-wave__bar::after {
        content: "";
        position: absolute;
        inset-inline: 0;
        top: -0.35rem;
        height: 1px;
        background: rgba(86, 104, 132, 0.32);
    }
</style>
