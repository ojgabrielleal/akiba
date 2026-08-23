<script>
    import { page } from "@inertiajs/svelte";
    import { player, setVolume, toggleAudio } from "@/lib/stores";
    import { LoadingSpinner } from "@/lib/components/private";

    $: ({ stream } = $page.props);
    $: streamData = stream ?? {};
</script>

<section class="container-page bg-blue-marinho">
    <div class="flex min-h-18 items-center border-t border-orange-amber py-2">
        <div class="flex w-full items-center justify-between gap-4">
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
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <div class="group relative flex h-11 w-11 items-center justify-center">
                    <button
                        type="button"
                        class="grid h-11 w-11 cursor-pointer place-items-center rounded-full bg-orange-amber transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                        aria-label="Ajustar volume"
                    >
                        <img
                            src="/svg/volume.svg"
                            alt=""
                            aria-hidden="true"
                            class="h-5 w-5 brightness-0"
                            loading="lazy"
                        />
                    </button>
                    <div class="absolute top-1/2 right-full z-30 hidden w-44 -translate-y-1/2 pr-3 group-hover:block group-focus-within:block">
                        <div class="rounded-md border border-blue-skywave/20 bg-blue-night px-3 py-2 shadow-lg shadow-blue-night/40">
                            <div class="mb-2 flex items-center justify-between font-noto-sans text-[0.65rem] font-black uppercase">
                                <label class="text-suspense-aurora/45" for="stream-volume">Volume</label>
                                <span class="text-orange-amber">{Math.round($player.volume * 100)}%</span>
                            </div>
                            <input
                                id="stream-volume"
                                name="stream-volume"
                                class="w-full cursor-pointer accent-orange-amber"
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
                <button
                    type="button"
                    class="grid h-11 w-11 cursor-pointer place-items-center rounded-full bg-orange-amber transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
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
                            class="h-4 w-4 brightness-0"
                        />
                    {/if}
                </button>
            </div>
        </div>
    </div>
</section>
