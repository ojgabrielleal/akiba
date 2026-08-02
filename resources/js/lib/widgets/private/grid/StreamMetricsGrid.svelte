<script>
    import { page } from "@inertiajs/svelte";
    import { player, setVolume, toggleAudio } from "@/lib/stores";
    import { LoadingSpinner, Offcanvas } from "@/lib/components/private";

    const maxVisibleVisitors = 3;
    let visitorsOffcanvasRef;

    $: ({ publicVisitors, stream } = $page.props);
    $: streamData = stream ?? {};
    $: connectedVisitors = publicVisitors?.visitors ?? [];
    $: listeningVisitors = connectedVisitors.filter((visitor) => visitor.listening);
    $: recognizedListeningVisitors = listeningVisitors.filter((visitor) => visitor.identity?.type !== "anonymous");
    $: recognizedConnectedVisitors = connectedVisitors.filter((visitor) => visitor.identity?.type !== "anonymous");
    $: anonymousListeners = listeningVisitors.filter((visitor) => visitor.identity?.type === "anonymous").length;
    $: visibleVisitors = recognizedListeningVisitors.slice(0, maxVisibleVisitors);
    $: visibleConnectedVisitors = recognizedConnectedVisitors.slice(0, maxVisibleVisitors);
    $: connectedTotal = publicVisitors?.listeners ?? 0;
    $: browsingTotal = publicVisitors?.total_conected ?? 0;
    $: anonymousConnectedVisitors = connectedVisitors.filter((visitor) => visitor.identity?.type === "anonymous").length;
    $: anonymousBrowsingVisitors = Math.max(0, anonymousConnectedVisitors - anonymousListeners);
    $: externalListeners = Math.max(0, Number(streamData.listeners ?? 0) - (publicVisitors?.listeners ?? 0));

    const visitorName = (visitor) => visitor?.identity?.name ?? "Anônimo";
    const visitorAvatar = (visitor) => visitor?.identity?.avatar;
    const visitorPage = (visitor) => {
        const title = visitor?.page_title;
        const path = visitor?.page_path ?? "";

        if (visitor?.page_title === "Rede Akiba - O Paraíso dos Otakus! | Sua Melhor Fonte de Animes (e Mangás) no Brasil!") {
            return "Vendo a página: Inicial";
        }

        if (path.startsWith("/materia/")) {
            return `Lendo a matéria ${title || path}`;
        }

        if (path.startsWith("/review/")) {
            return `Lendo o review ${title || path}`;
        }

        if (path.startsWith("/event/") || path.startsWith("/evento/")) {
            return `Lendo o evento ${title || path}`;
        }

        return `Vendo a página: ${title || path}`;
    };

    const visitorDescription = (visitor) => {
        const identityLabel = visitor?.identity?.type === "member"
            ? "Membro da Akiba"
            : visitor?.identity?.provider;

        return identityLabel;
    };

    const visitorActivity = (visitor) => visitor?.listening ? "Ouvindo" : "Navegando";
</script>

<section class="container-page bg-blue-marinho">
    <div class="border-t border-orange-amber py-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center">
                <div class="hidden gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase pr-6 border-r border-r-[rgba(229,231,235,0.3)] lg:flex">
                    <img
                        src="/svg/kbps.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    {streamData.bitrate ?? "N/A"}
                </div>
                <div class="hidden gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase px-6 border-r border-r-[rgba(229,231,235,0.3)] lg:flex">
                    <img
                        src="/svg/satelite.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    {streamData.status ?? "N/A"}
                </div>
                <div class="flex gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase lg:px-6">
                    <img
                        src="/svg/listeners.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    <span>{streamData.listeners ?? "N/A"} Ouvintes</span>
                    {#if connectedTotal > 0}
                        <button
                            type="button"
                            class="ml-1 flex cursor-pointer items-center -space-x-3 self-center rounded-full focus:outline-none"
                            aria-label={`${connectedTotal} ouvintes conectados no site`}
                            on:click={() => visitorsOffcanvasRef?.open()}
                        >
                            <div class="flex -space-x-3">
                                {#each visibleVisitors as visitor, index (`${visitor.identity?.uuid ?? "anonymous"}-${index}`)}
                                    <img
                                        src={visitorAvatar(visitor)}
                                        alt={visitorName(visitor)}
                                        title={visitorName(visitor)}
                                        class="h-7 w-7 rounded-full border-2 border-blue-marinho object-cover"
                                        loading="lazy"
                                    />
                                {/each}
                            </div>
                            <span class="relative z-10 grid h-7 min-w-7 place-items-center rounded-full border-2 border-blue-marinho bg-orange-amber px-1 text-xs font-black leading-none text-blue-night">
                                {connectedTotal}
                            </span>
                        </button>
                    {/if}
                </div>
                <div class="hidden gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase pl-6 border-l border-l-[rgba(229,231,235,0.3)] sm:flex">
                    <img
                        src="/svg/globe.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    <span>{browsingTotal} Online</span>
                    {#if browsingTotal > 0}
                        <button
                            type="button"
                            class="ml-1 flex cursor-pointer items-center -space-x-3 self-center rounded-full focus:outline-none"
                            aria-label={`${browsingTotal} visitantes no site`}
                            on:click={() => visitorsOffcanvasRef?.open()}
                        >
                            <div class="flex -space-x-3">
                                {#each visibleConnectedVisitors as visitor, index (`${visitor.identity?.uuid ?? "connected"}-${index}`)}
                                    <img
                                        src={visitorAvatar(visitor)}
                                        alt={visitorName(visitor)}
                                        title={visitorName(visitor)}
                                        class="h-7 w-7 rounded-full border-2 border-blue-marinho object-cover"
                                        loading="lazy"
                                    />
                                {/each}
                            </div>
                            <span class="relative z-10 grid h-7 min-w-7 place-items-center rounded-full border-2 border-blue-marinho bg-orange-amber px-1 text-xs font-black leading-none text-blue-night">
                                {browsingTotal}
                            </span>
                        </button>
                    {/if}
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button
                    type="button"
                    class="cursor-pointer grid h-10 w-10 place-items-center rounded-full bg-orange-amber transition hover:brightness-110 focus:outline-none"
                    aria-label={$player.loading ? "Carregando rádio" : $player.playing ? "Pausar radio" : "Tocar radio"}
                    aria-pressed={$player.playing}
                    aria-busy={$player.loading}
                    disabled={$player.loading && !$player.playing}
                    on:click={toggleAudio}
                >
                    {#if $player.loading}
                        <LoadingSpinner size="sm" tone="dark" label="Carregando rádio" />
                    {:else}
                        <img
                            src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                            alt=""
                            aria-hidden="true"
                            class="h-4 w-4"
                        />
                    {/if}
                </button>
                <div class="group relative flex h-10 w-10 items-center justify-center">
                    <button
                        type="button"
                        class="cursor-pointer grid h-10 w-10 place-items-center rounded-full bg-orange-amber transition hover:brightness-110 focus:outline-none"
                        aria-label="Ajustar volume"
                    >
                        <img
                            src="/svg/volume.svg"
                            alt=""
                            aria-hidden="true"
                            class="h-4 w-4"
                            loading="lazy"
                        />
                    </button>
                    <div class="pointer-events-none absolute bottom-full left-1/2 z-10 flex h-38.25 w-10 -translate-x-1/2 items-start justify-center opacity-0 transition group-hover:pointer-events-auto group-hover:opacity-100 group-focus-within:pointer-events-auto group-focus-within:opacity-100">
                        <div class="flex h-36 w-10 items-center justify-center rounded-md bg-slate-700/95 pb-5 pt-3 shadow-lg">
                            <label class="sr-only" for="stream-volume">Volume do player</label>
                            <input
                                id="stream-volume"
                                class="h-4 w-24 -rotate-90 cursor-pointer appearance-none bg-transparent accent-orange-amber [&::-moz-range-thumb]:h-3 [&::-moz-range-thumb]:w-3 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:bg-orange-amber [&::-moz-range-track]:h-[3px] [&::-moz-range-track]:rounded-full [&::-moz-range-track]:bg-orange-amber [&::-webkit-slider-runnable-track]:h-[3px] [&::-webkit-slider-runnable-track]:rounded-full [&::-webkit-slider-runnable-track]:bg-orange-amber [&::-webkit-slider-thumb]:mt-[-4.5px] [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-0 [&::-webkit-slider-thumb]:bg-orange-amber"
                                type="range"
                                min="0"
                                max="1"
                                step="0.01"
                                value={$player.volume}
                                aria-label="Volume do player"
                                on:input={(event) => setVolume(Number(event.currentTarget.value))}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<Offcanvas bind:this={visitorsOffcanvasRef} title="Agora na Akiba">
    <div slot="content" class="font-noto-sans">
        <div class="mb-3 grid grid-cols-3 gap-2.5">
            <div class="rounded-md bg-white/70 px-3 py-3 text-blue-night">
                <div class="mb-2 flex items-center gap-2">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-blue-night/10">
                        <img
                            src="/svg/listeners.svg"
                            alt=""
                            aria-hidden="true"
                            class="h-4.5 w-4.5 filter-blue-marinho opacity-70"
                            loading="lazy"
                        />
                    </span>
                    <strong class="text-xl leading-none text-blue-night">{anonymousListeners}</strong>
                </div>
                <span class="block text-[0.72rem] font-bold uppercase leading-tight text-blue-night/55">Anônimos ouvindo</span>
            </div>
            <div class="rounded-md bg-white/70 px-3 py-3 text-blue-night">
                <div class="mb-2 flex items-center gap-2">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-blue-night/10">
                        <img
                            src="/svg/globe.svg"
                            alt=""
                            aria-hidden="true"
                            class="h-4.5 w-4.5 filter-blue-marinho opacity-70"
                            loading="lazy"
                        />
                    </span>
                    <strong class="text-xl leading-none text-blue-night">{anonymousBrowsingVisitors}</strong>
                </div>
                <span class="block text-[0.72rem] font-bold uppercase leading-tight text-blue-night/55">Anônimos navegando</span>
            </div>
            <div class="rounded-md bg-white/70 px-3 py-3 text-blue-night">
                <div class="mb-2 flex items-center gap-2">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-blue-night/10">
                        <img
                            src="/svg/radio.svg"
                            alt=""
                            aria-hidden="true"
                            class="h-4.5 w-4.5 filter-blue-marinho opacity-70"
                            loading="lazy"
                        />
                    </span>
                    <strong class="text-xl leading-none text-blue-night">{externalListeners}</strong>
                </div>
                <span class="block text-[0.72rem] font-bold uppercase leading-tight text-blue-night/55">Ouvintes de fora do site</span>
            </div>
        </div>

        <hr class="mb-4 border-blue-night/10" />

        {#if recognizedConnectedVisitors.length}
            <div class="space-y-3">
                {#each recognizedConnectedVisitors as visitor, index (`${visitor.identity?.uuid ?? "recognized"}-${index}`)}
                    <article class="rounded-md bg-white p-3 text-blue-night">
                        <div class="flex items-center gap-3">
                            <div class="flex shrink-0 items-center gap-2">
                                <img
                                    src={visitorAvatar(visitor)}
                                    alt={visitorName(visitor)}
                                    class="h-11 w-11 rounded-full object-cover"
                                    loading="lazy"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <strong class="mb-0.5 block truncate text-sm leading-tight">
                                    {visitorName(visitor)}
                                </strong>
                                <span class="block whitespace-normal break-words text-xs leading-tight text-blue-night/60">
                                    {visitorDescription(visitor)}
                                </span>
                            </div>
                            <span class={[
                                "rounded px-2.5 py-1.5 text-xs font-bold leading-none",
                                visitor.listening ? "bg-orange-amber text-blue-night" : "bg-blue-night/10 text-blue-night/60",
                            ].filter(Boolean).join(" ")}>
                                {visitorActivity(visitor)}
                            </span>
                        </div>
                        <hr class="my-2 border-blue-night/10" />
                        <div class="flex items-start gap-1.5 rounded bg-blue-night/[0.03] px-2 py-1.5 text-xs leading-tight text-blue-night/55">
                            <img
                                src="/svg/globe.svg"
                                alt=""
                                aria-hidden="true"
                                class="mt-0.5 h-3.5 w-3.5 shrink-0 filter-blue-marinho opacity-50"
                                loading="lazy"
                            />
                            <span class="min-w-0 whitespace-normal break-words">
                                {visitorPage(visitor)}
                            </span>
                        </div>
                    </article>
                {/each}
            </div>
        {:else}
            <div class="rounded-md bg-white px-4 py-8 text-center text-sm text-blue-night/60">
                Nenhum usuário reconhecido conectado agora.
            </div>
        {/if}
    </div>
</Offcanvas>
