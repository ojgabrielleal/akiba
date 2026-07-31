<script>
    import { page } from "@inertiajs/svelte";
    import { player, setVolume, toggleAudio } from "@/lib/stores";
    import { LoadingSpinner, Offcanvas } from "@/lib/components/private";
    import { resolvePlaceholderImage } from "@/lib/utils";
    import OnlineAccountList from "@/lib/widgets/private/list/OnlineAccountList.svelte";

    $: ({ stream, online } = $page.props);
    
    $: streamData = stream ?? {};
    $: connectedNow = online?.conectados_agora ?? "N/A";
    $: connectedAccounts = online?.contas_conectadas ?? [];
    $: visibleConnectedAccounts = connectedAccounts.slice(0, 4);
    $: hiddenConnectedAccountsCount = Math.max(connectedAccounts.length - visibleConnectedAccounts.length, 0);
    $: identifiedConnectedCount = connectedAccounts.reduce((total, account) => total + (account.usuarios_online ?? 1), 0);
    $: anonymousConnectedCount = Math.max((Number(connectedNow) || 0) - identifiedConnectedCount, 0);
    $: listeningAccounts = online?.contas_ouvindo ?? [];
    $: visibleListeningAccounts = listeningAccounts.slice(0, 4);
    $: hiddenListeningAccountsCount = Math.max(listeningAccounts.length - visibleListeningAccounts.length, 0);
    $: listeningNow = online?.ouvindo_agora ?? 0;
    $: identifiedListeningCount = listeningAccounts.reduce((total, account) => total + (account.usuarios_online ?? 1), 0);
    $: anonymousListeningCount = Math.max((Number(listeningNow) || 0) - identifiedListeningCount, 0);
    const accountName = (account) => account.nickname ?? account.username ?? "Conta online";

    let listeningOffcanvasRef;
    let connectedOffcanvasRef;
</script>

<section class="container-page bg-blue-marinho">
    <Offcanvas bind:this={listeningOffcanvasRef}>
        <div slot="content">
            <div class="mb-5">
                <p class="font-noto-sans text-xs font-black uppercase text-neutral-gray/50">
                    Ouvindo agora
                </p>
                <p class="font-noto-sans text-2xl font-black italic uppercase text-blue-night">
                    {listeningAccounts.length} online
                </p>
                <p class="font-noto-sans text-xs font-black uppercase text-neutral-gray/50">
                    {identifiedListeningCount} logado{identifiedListeningCount === 1 ? "" : "s"} | {anonymousListeningCount} sem conta
                </p>
            </div>
            <OnlineAccountList
                accounts={listeningAccounts}
                emptyMessage="Nenhuma conta ouvindo agora."
            />
        </div>
    </Offcanvas>

    <Offcanvas bind:this={connectedOffcanvasRef}>
        <div slot="content">
            <div class="mb-5">
                <p class="font-noto-sans text-xs font-black uppercase text-neutral-gray/50">
                    No site agora
                </p>
                <p class="font-noto-sans text-2xl font-black italic uppercase text-blue-night">
                    {connectedAccounts.length} online
                </p>
                <p class="font-noto-sans text-xs font-black uppercase text-neutral-gray/50">
                    {identifiedConnectedCount} logado{identifiedConnectedCount === 1 ? "" : "s"} | {anonymousConnectedCount} sem conta
                </p>
            </div>
            <OnlineAccountList
                accounts={connectedAccounts}
                emptyMessage="Nenhuma conta conectada agora."
            />
        </div>
    </Offcanvas>

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
                    {streamData.listeners ?? "N/A"} Ouvintes
                    {#if visibleListeningAccounts.length}
                        <button
                            type="button"
                            class="flex h-7 cursor-pointer items-center rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                            aria-label="Ver contas ouvindo agora"
                            on:click={() => listeningOffcanvasRef.open()}
                        >
                            {#each visibleListeningAccounts as account (account.id)}
                                <span class="group/account relative -ml-1.5 first:ml-0">
                                    <img
                                        src={resolvePlaceholderImage(account.avatar, "avatar")}
                                        alt={accountName(account)}
                                        class="h-5 w-5 rounded-full border border-blue-night bg-suspense-aurora object-cover object-top shadow-[0_0_6px_rgba(0,0,0,0.35)]"
                                        loading="lazy"
                                    />
                                    <span class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 -translate-x-1/2 whitespace-nowrap rounded bg-blue-night px-2 py-1 text-[0.65rem] font-bold normal-case text-suspense-aurora opacity-0 shadow-lg transition group-hover/account:opacity-100">
                                        {accountName(account)}
                                    </span>
                                </span>
                            {/each}
                            {#if hiddenListeningAccountsCount}
                                <span class="-ml-1.5 grid h-5 min-w-5 place-items-center rounded-full border border-blue-night bg-lime-400 px-1 text-[0.55rem] font-black leading-none text-blue-night shadow-[0_0_6px_rgba(163,230,53,0.7)]">
                                    +{hiddenListeningAccountsCount}
                                </span>
                            {/if}
                        </button>
                    {/if}
                </div>
                <div class="hidden items-end gap-2 pl-6 border-l border-l-[rgba(229,231,235,0.3)] lg:flex">
                    <img
                        src="/svg/globe.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    <div class="flex items-end gap-2 font-noto-sans text-orange-amber uppercase">
                        <span class="text-xl">
                            {connectedNow} online
                        </span>
                    </div>
                    {#if visibleConnectedAccounts.length}
                        <button
                            type="button"
                            class="flex h-7 cursor-pointer items-center rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                            aria-label="Ver contas online agora"
                            on:click={() => connectedOffcanvasRef.open()}
                        >
                            {#each visibleConnectedAccounts as account (account.id)}
                                <span class="group/account relative -ml-1.5 first:ml-0">
                                    <img
                                        src={resolvePlaceholderImage(account.avatar, "avatar")}
                                        alt={accountName(account)}
                                        class="h-5 w-5 rounded-full border border-blue-night bg-suspense-aurora object-cover object-top shadow-[0_0_6px_rgba(0,0,0,0.35)]"
                                        loading="lazy"
                                    />
                                    <span class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 -translate-x-1/2 whitespace-nowrap rounded bg-blue-night px-2 py-1 text-[0.65rem] font-bold normal-case text-suspense-aurora opacity-0 shadow-lg transition group-hover/account:opacity-100">
                                        {accountName(account)}
                                    </span>
                                </span>
                            {/each}
                            {#if hiddenConnectedAccountsCount}
                                <span class="-ml-1.5 grid h-5 min-w-5 place-items-center rounded-full border border-blue-night bg-lime-400 px-1 text-[0.55rem] font-black leading-none text-blue-night shadow-[0_0_6px_rgba(163,230,53,0.7)]">
                                    +{hiddenConnectedAccountsCount}
                                </span>
                            {/if}
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
