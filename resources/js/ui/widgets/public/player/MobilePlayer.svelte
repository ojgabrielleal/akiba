<script>
    import { page } from "@inertiajs/svelte";
    import { Modal } from "@/ui/components/public";
    import { SongRequestForm } from "@/ui/widgets/public";
    import { player, toggleAudio, setVolume } from "@/store";

    $: ({ onair: { data: [air] }, stream } = $page.props);

    let modalRef;

    $: playerData = {
        program: {
            name: air.program.name,
            image: air.program.image,
        },
        host: {
            nickname: air.program.host.nickname,
            avatar: air.program.host.avatar,
            gender: air.program.host.gender,
        },
        executionMode: air.execution_mode,
        currentSong: {
            cover: stream.current_song.cover,
            music: stream.current_song.music,
        },
    };

    $: status = getStatus(playerData.executionMode, playerData.host.gender);

    function getStatus(mode, gender) {
        if (mode === "auto_dj") {
            return { label: "Playlist automática", icon: "/svg/robot.svg" };
        }

        if (mode === "playlist") {
            return { label: "Playlist personalizada", icon: "/svg/disc.svg" };
        }

        if (mode === "live") {
            return {
                label: `Locut${gender === "male" ? "or" : "ora"} ao vivo`,
                icon: "/svg/onair.svg",
            };
        }

        return { label: "Programa agendado", icon: "/svg/disc.svg" };
    }
</script>

<Modal bind:this={modalRef}>
    <div slot="content">
        <SongRequestForm />
    </div>
</Modal>

<!-- Phone player -->
<section class="mt-10 container-page md:hidden">
    <div class="relative w-full max-w-[26rem] mx-auto overflow-hidden rounded-3xl border border-suspense-aurora/10 bg-blue-ocean/25 shadow-2xl">
        <div class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-skywave/20 via-blue-ocean/10 to-transparent"></div>
        <div class="absolute -top-16 -right-16 size-52 rounded-full bg-orange-citric/10 blur-3xl"></div>

        <div class="relative p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class={[
                    "min-w-0 h-8 px-3 flex items-center gap-2 rounded-full",
                    { "bg-neutral-gray": playerData.executionMode === "auto_dj" || playerData.executionMode === "playlist" },
                    { "bg-green-mint": playerData.executionMode === "live" },
                    { "bg-orange-amber": playerData.executionMode === "scheduled" },
                ]}>
                    <img
                        src={status.icon}
                        alt=""
                        aria-hidden="true"
                        class="size-4 shrink-0 object-contain brightness-0"
                    />
                    <span class="truncate text-blue-night text-[10px] font-noto-sans font-extrabold uppercase italic">
                        {status.label}
                    </span>
                </div>

                <span class="shrink-0 text-orange-amber text-[10px] font-noto-sans font-extrabold uppercase tracking-wider">
                    No ar
                </span>
            </div>

            <div class="relative min-h-56 mb-5 grid grid-cols-[minmax(0,1fr)_10rem] items-end border-b border-suspense-aurora/10">
                <div class="relative z-10 min-w-0 pb-6">
                    <p class="mb-1 text-orange-amber text-[10px] font-noto-sans font-extrabold uppercase tracking-widest">
                        Agora na Akiba
                    </p>
                    {#if playerData.program.image}
                        <img
                            src={playerData.program.image}
                            alt={playerData.program.name || "Programa no ar"}
                            class="mb-4 max-w-full max-h-24 object-contain object-left"
                            loading="lazy"
                        />
                    {:else}
                        <h2 class="mb-4 text-suspense-aurora text-3xl leading-8 font-noto-sans font-extrabold uppercase italic break-words">
                            {playerData.program.name || "Programação Akiba"}
                        </h2>
                    {/if}

                    <p class="text-suspense-aurora/55 text-[10px] font-noto-sans uppercase tracking-wider">
                        Com DJ
                    </p>
                    <p class="truncate text-blue-skywave text-base font-noto-sans font-extrabold uppercase italic">
                        {playerData.host.nickname}
                    </p>
                </div>

                <div class="relative w-40 h-full flex items-end justify-end">
                    <div class="absolute right-0 bottom-3 size-32 rounded-full bg-blue-skywave/15 blur-2xl"></div>
                    <img
                        src={playerData.host.avatar}
                        alt={playerData.host.nickname || "Locutor atual"}
                        class="relative z-10 w-40 max-h-56 object-contain object-bottom"
                        loading="lazy"
                    />
                </div>
            </div>

            <div class="mb-6 flex items-center gap-3 rounded-2xl bg-blue-night/35 p-3">
                {#if playerData.currentSong.cover}
                    <img
                        src={playerData.currentSong.cover}
                        alt=""
                        aria-hidden="true"
                        class="size-11 shrink-0 rounded-lg object-cover opacity-80"
                        loading="lazy"
                    />
                {/if}
                <div class="min-w-0">
                    <p class="text-orange-amber text-[9px] font-noto-sans font-extrabold uppercase italic">
                        Tocando agora
                    </p>
                    <p class="truncate text-suspense-aurora/75 text-xs font-noto-sans font-bold uppercase italic">
                        {decodeURIComponent(escape(playerData.currentSong.music || "Estamos offline"))}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-[4rem_minmax(0,1fr)] items-center gap-5 mb-6">
                <button
                    type="button"
                    aria-label={$player.playing ? "Pausar rádio" : "Tocar rádio"}
                    class={[
                        "relative size-16 rounded-full flex items-center justify-center active:scale-95 transition-transform shadow-xl",
                        { "bg-orange-citric shadow-orange-citric/20": !$player.playing },
                        { "bg-blue-skywave shadow-blue-skywave/20": $player.playing },
                    ]}
                    on:click={toggleAudio}
                >
                    <span class="absolute inset-2 rounded-full border border-blue-night/15"></span>
                    <img
                        src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                        alt=""
                        aria-hidden="true"
                        class="relative w-5"
                    />
                </button>
                <div class="min-w-0 flex-1">
                    <div class="mb-2 flex items-center justify-between">
                        <label for="mobile-player-volume" class="text-suspense-aurora/50 text-[10px] font-noto-sans font-extrabold uppercase">
                            Volume
                        </label>
                        <span class="text-orange-citric text-[10px] font-noto-sans font-extrabold">
                            {Math.round($player.volume * 100)}%
                        </span>
                    </div>
                    <input
                        id="mobile-player-volume"
                        name="volume"
                        type="range"
                        min="0"
                        max="1"
                        step="0.01"
                        value={$player.volume}
                        class="w-full h-1.5 rounded-full accent-orange-citric cursor-pointer"
                        on:input={(event) => setVolume(event.currentTarget.value)}
                    />
                </div>
            </div>

            <button
                type="button"
                class="w-full py-3 px-5 rounded-full border border-suspense-aurora/30 text-blue-skywave text-base text-center font-noto-sans font-extrabold italic uppercase active:scale-[0.98] transition-transform"
                on:click={() => modalRef.open()}
            >
                Faça seu <strong class="text-orange-citric">pedido</strong>
            </button>
        </div>
    </div>
</section>

<!-- Tablet player -->
<section class="container-page hidden md:block">
    <div class="relative w-full max-w-4xl mx-auto overflow-hidden rounded-3xl border border-suspense-aurora/10 bg-blue-ocean/25 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-skywave/15 via-transparent to-orange-citric/10"></div>

        <div class="relative grid grid-cols-[minmax(0,1.25fr)_minmax(17rem,0.75fr)] gap-8 p-7">
            <div class="min-w-0">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div class={[
                        "min-w-0 h-8 px-3 flex items-center gap-2 rounded-full",
                        { "bg-neutral-gray": playerData.executionMode === "auto_dj" || playerData.executionMode === "playlist" },
                        { "bg-green-mint": playerData.executionMode === "live" },
                        { "bg-orange-amber": playerData.executionMode === "scheduled" },
                    ]}>
                        <img
                            src={status.icon}
                            alt=""
                            aria-hidden="true"
                            class="size-4 shrink-0 object-contain brightness-0"
                        />
                        <span class="truncate text-blue-night text-[10px] font-noto-sans font-extrabold uppercase italic">
                            {status.label}
                        </span>
                    </div>
                    <span class="shrink-0 text-orange-amber text-[10px] font-noto-sans font-extrabold uppercase tracking-wider">
                        No ar
                    </span>
                </div>

                <div class="grid grid-cols-[minmax(0,1fr)_13rem] min-h-60 items-end border-b border-suspense-aurora/10">
                    <div class="relative z-10 min-w-0 pb-7">
                        <p class="mb-2 text-orange-amber text-xs font-noto-sans font-extrabold uppercase tracking-widest">
                            Agora na Akiba
                        </p>
                        {#if playerData.program.image}
                            <img
                                src={playerData.program.image}
                                alt={playerData.program.name || "Programa no ar"}
                                class="mb-5 max-w-72 max-h-28 object-contain object-left"
                                loading="lazy"
                            />
                        {:else}
                            <h2 class="mb-5 text-suspense-aurora text-4xl leading-10 font-noto-sans font-extrabold uppercase italic">
                                {playerData.program.name || "Programação Akiba"}
                            </h2>
                        {/if}

                        <p class="text-suspense-aurora/55 text-[10px] font-noto-sans uppercase tracking-wider">
                            Com DJ
                        </p>
                        <p class="truncate text-blue-skywave text-xl font-noto-sans font-extrabold uppercase italic">
                            {playerData.host.nickname}
                        </p>
                    </div>

                    <div class="relative w-52 h-full -translate-x-10 flex items-end justify-end">
                        <div class="absolute right-2 bottom-4 size-40 rounded-full bg-blue-skywave/15 blur-3xl"></div>
                        <img
                            src={playerData.host.avatar}
                            alt={playerData.host.nickname || "Locutor atual"}
                            class="relative z-10 w-52 max-h-64 object-contain object-bottom"
                            loading="lazy"
                        />
                    </div>
                </div>
            </div>

            <div class="min-w-0 flex flex-col justify-center">
                <div class="mb-7 flex items-center gap-3 rounded-2xl bg-blue-night/35 p-3">
                    {#if playerData.currentSong.cover}
                        <img
                            src={playerData.currentSong.cover}
                            alt=""
                            aria-hidden="true"
                            class="size-14 shrink-0 rounded-xl object-cover opacity-80"
                            loading="lazy"
                        />
                    {/if}
                    <div class="min-w-0">
                        <p class="text-orange-amber text-[9px] font-noto-sans font-extrabold uppercase italic">
                            Tocando agora
                        </p>
                        <p class="line-clamp-2 text-suspense-aurora/80 text-sm leading-4 font-noto-sans font-bold uppercase italic">
                            {decodeURIComponent(escape(playerData.currentSong.music || "Estamos offline"))}
                        </p>
                    </div>
                </div>

                <div class="mb-7 flex justify-center">
                    <button
                        type="button"
                        aria-label={$player.playing ? "Pausar rádio" : "Tocar rádio"}
                        class={[
                            "relative size-18 rounded-full flex items-center justify-center active:scale-95 transition-transform shadow-xl",
                            { "bg-orange-citric shadow-orange-citric/20": !$player.playing },
                            { "bg-blue-skywave shadow-blue-skywave/20": $player.playing },
                        ]}
                        on:click={toggleAudio}
                    >
                        <span class="absolute inset-2 rounded-full border border-blue-night/15"></span>
                        <img
                            src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                            alt=""
                            aria-hidden="true"
                            class="relative w-6"
                        />
                    </button>
                </div>

                <div class="mb-7">
                    <div class="mb-2 flex items-center justify-between">
                        <label for="tablet-player-volume" class="text-suspense-aurora/50 text-[10px] font-noto-sans font-extrabold uppercase">
                            Volume
                        </label>
                        <span class="text-orange-citric text-[10px] font-noto-sans font-extrabold">
                            {Math.round($player.volume * 100)}%
                        </span>
                    </div>
                    <input
                        id="tablet-player-volume"
                        name="volume"
                        type="range"
                        min="0"
                        max="1"
                        step="0.01"
                        value={$player.volume}
                        class="w-full h-1.5 rounded-full accent-orange-citric cursor-pointer"
                        on:input={(event) => setVolume(event.currentTarget.value)}
                    />
                </div>

                <button
                    type="button"
                    class="w-full py-3 px-5 rounded-full border border-suspense-aurora/30 text-blue-skywave text-base text-center font-noto-sans font-extrabold italic uppercase active:scale-[0.98] transition-transform"
                    on:click={() => modalRef.open()}
                >
                    Faça seu <strong class="text-orange-citric">pedido</strong>
                </button>
            </div>
        </div>
    </div>
</section>
