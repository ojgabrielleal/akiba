<script>
    import { page } from "@inertiajs/svelte";
    import { player, setVolume, toggleAudio } from "@/store";

    $: ({ streaming } = $page.props);
    $: cast = streaming ?? {};
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
                    {cast.bitrate ?? "N/A"}
                </div>
                <div class="hidden gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase px-6 border-r border-r-[rgba(229,231,235,0.3)] lg:flex">
                    <img
                        src="/svg/satelite.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    {cast.status ?? "N/A"}
                </div>
                <div class="flex gap-2 items-end font-noto-sans text-orange-amber text-xl uppercase lg:px-6">
                    <img
                        src="/svg/listeners.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-8 filter-blue-skywave"
                        loading="lazy"
                    />
                    {cast.listeners ?? "N/A"} Ouvintes
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button
                    type="button"
                    class="cursor-pointer grid h-10 w-10 place-items-center rounded-full bg-orange-amber transition hover:brightness-110 focus:outline-none"
                    aria-label={$player.playing ? "Pausar radio" : "Tocar radio"}
                    aria-pressed={$player.playing}
                    on:click={toggleAudio}
                >
                    <img
                        src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                        alt=""
                        aria-hidden="true"
                        class="h-4 w-4"
                    />
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
                            <label class="sr-only" for="cast-volume">Volume do player</label>
                            <input
                                id="cast-volume"
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
