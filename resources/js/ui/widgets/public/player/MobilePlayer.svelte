<script>
    import { page } from "@inertiajs/svelte";
    import { Modal } from "@/ui/components/public";
    import { SongRequestForm } from "@/ui/widgets/public";
    import { player, toggleAudio, setVolume } from "@/store";

    $: ({ onair } = $page.props);

    $: air = onair.data[0];

    let modalRef;
</script>

<Modal bind:this={modalRef}>
    <div slot="content">
        <SongRequestForm />
    </div>
</Modal>

<section class="container-page flex flex-col items-center justify-center gap-8 overflow-hidden relative">
    <div class="w-full relative z-10 bg-suspense-aurora/5 backdrop-blur-xl border border-white/10 rounded-4xl p-6 md:p-8 flex flex-col md:flex-row md:items-center gap-6 shadow-2xl">
        <div class="flex items-center gap-5 md:flex-1">
            <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden border-2 border-white/10 shrink-0 shadow-lg">
                <img
                    src={air.program.host.avatar}
                    alt="Programa"
                    class="w-full h-full object-cover object-top scale-150"
                />
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-orange-amber text-[10px] md:text-xs uppercase font-noto-sans font-extrabold italic">
                    PROGRAMA
                </span>
                <h3 class="text-suspense-aurora text-xl md:text-2xl font-noto-sans font-extrabold uppercase italic truncate">
                    {air.program.name}
                </h3>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class={["text-[10px] px-2.5 py-1 rounded-md text-suspense-aurora font-noto-sans font-extrabold uppercase italic",
                        { "bg-neutral-gray": air.type === "auto_dj" || air.type === "playlist" },
                        { "bg-green-mint": air.type === "live" },
                        { "bg-orange-citric": air.type === "scheduled" },
                    ]}>
                        {#if air.type === "auto_dj" || air.type === "playlist"}
                            Robô
                        {:else if air.type === "live"}
                            Human{air.program.host.gender === "male" ? "o" : "a"}
                        {:else}
                            Gravado
                        {/if}
                    </span>
                    <span class="text-suspense-aurora/50 text-xs md:text-sm font-noto-sans font-medium italic">
                        com {air.program.host.nickname}
                    </span>
                </div>
            </div>
        </div>

        <div class="hidden md:block w-px h-16 bg-suspense-aurora/10"></div>
        <div class="block md:hidden w-full h-px bg-suspense-aurora/10"></div>

        <div class="flex flex-col gap-1.5 md:flex-1">
            <span class="text-orange-amber text-[10px] md:text-xs italic uppercase font-noto-sans font-extrabold">
                TOCANDO AGORA
            </span>
            <p class="text-suspense-aurora text-lg md:text-xl font-noto-sans font-extrabold italic line-clamp-2 leading-snug">
                {air?.current_song?.music}
            </p>
        </div>
    </div>

    <div class="w-full relative z-10 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
        <div class="md:col-span-3 flex justify-center">
            <button type="button"
                aria-label=""
                class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-blue-skywave flex items-center justify-center hover:scale-105 active:scale-95 transition-all group"
                on:click={toggleAudio}
            >
                <div class="bg-suspense-aurora rounded-full p-4 md:p-5 flex items-center justify-center shadow-inner">
                    <img
                        src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                        alt={$player.playing ? "Pause" : "Play"}
                        class="w-8 md:w-10 filter-blue-skywave"
                    />
                </div>
            </button>
        </div>

        <div class="md:col-span-9 flex flex-col justify-start gap-6">
            <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center px-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] md:text-xs text-suspense-aurora/40 font-noto-sans font-extrabold italic uppercase">
                            Volume
                        </span>
                    </div>
                    <span class="text-[10px] md:text-xs text-orange-amber font-noto-sans font-extrabold italic">
                        {Math.round($player.volume * 100)}%
                    </span>
                </div>
                <input
                    type="range"
                    min="0"
                    max="1"
                    step="0.01"
                    value={$player.volume}
                    class="w-full accent-orange-amber h-2 rounded-full bg-suspense-aurora/10 cursor-pointer transition-all hover:bg-suspense-aurora/20"
                    on:input={(e) => setVolume(e.target.value)}
                />
            </div>

            <button type="button" class="group cursor-pointer w-full py-3 px-6 border-2 border-suspense-aurora/20 hover:border-orange-amber/50 rounded-full text-blue-skywave text-lg md:text-xl text-center font-noto-sans font-extrabold italic uppercase transition-all" on:click={() => modalRef.open()}>
                & Faça seu <strong
                    class="text-orange-amber group-hover:underline"
                    >Pedido</strong
                >
            </button>
        </div>
    </div>
</section>
