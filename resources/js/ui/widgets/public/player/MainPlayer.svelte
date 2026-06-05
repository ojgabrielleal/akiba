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

<!-- Phrase Section -->
<section class="w-full bg-blue-ocean mb-5">
    <div class="container-player py-4 relative">
        <div class="block absolute -top-7 left-0 xl:-left-10 z-10">
            <img
                src="/img/player/rains.webp"
                alt=""
                aria-hidden="true"
                class="w-20 transform -scale-x-100 -scale-y-100"
                loading="lazy"
            />
        </div>
        <!-- svelte-ignore a11y_distracting_elements -->
        <marquee class="w-[95%] xl:w-5xl flex overflow-x-hidden relative marquee-cont">
            <div class="whitespace-nowrap w-full md:w-auto text-suspense-aurora text-3xl font-noto-sans font-extrabold uppercase italic">
                <span class="mx-4">
                    {air.phrase?.text ?? air.phrase?.phrase ?? air.phrase}
                </span>
            </div>
        </marquee>
        <div class="block absolute bottom-0 right-5 xl:-right-4 z-10">
            <img
                src={air.icon}
                alt=""
                aria-hidden="true"
                class="w-32"
                loading="lazy"
            />
        </div>
        <div class="block absolute -top-8 right-0 xl:-right-10 z-10">
            <img
                src="/img/player/rains.webp"
                alt=""
                aria-hidden="true"
                class="w-20"
                loading="lazy"
            />
        </div>
    </div>
</section>

<!-- Main Player Section -->
<section class="container-player grid grid-cols-[2fr_1fr_0.8fr] items-center gap-5">
    <!-- First Column-->
    <div class="block">
        <!--Program and Host Information-->
        <div class="flex items-center gap-5 mb-15">
            <div class="w-52">
                <img
                    src={air.program.image}
                    alt="Programa"
                    loading="lazy"
                />
            </div>
            <div class="text-gray-500">
                <img
                    src="/svg/arrowRightTwo.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 filter-neutral-gray"
                    loading="lazy"
                />
            </div>
            <div>
                <div class="text-orange-amber font-noto-sans uppercase">
                    COM DJ
                </div>
                <div class="w-full text-suspense-aurora text-2xl font-noto-sans font-extrabold uppercase italic line-clamp-1">
                    {air.program.host.nickname}
                </div>
                <div class={["mt-[0.4rem] w-24 rounded-xl float-end text-center text-sm text-suspense-aurora font-noto-sans font-extrabold italic uppercase",
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
                </div>
            </div>
            <div class="text-gray-500">
                <img
                    src="/svg/arrowRightTwo.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 filter-neutral-gray"
                    loading="lazy"
                />
            </div>
        </div>
        <!--Current Song Information-->
        <div class="flex gap-3 items-end">
            <div class="w-20 shrink-0">
                <img
                    src={air.current_song?.cover || "https://cdn.vectorstock.com/i/500p/57/71/music-note-icon-set-vector-2855771.jpg"}
                    alt=""
                    aria-hidden="true"
                    class="rounded-md"
                    loading="lazy"
                    on:error={(e) => { e.target.src = "https://cdn.vectorstock.com/i/500p/57/71/music-note-icon-set-vector-2855771.jpg"; }}
                />
            </div>
            <div class="w-full srink-0">
                <div class="text-orange-amber font-noto-sans uppercase italic">
                    Tocando agora:
                </div>
                <!-- svelte-ignore a11y_distracting_elements -->
                <marquee class="w-full text-suspense-aurora text-xl font-noto-sans font-extrabold uppercase italic line-clamp-1">
                    {decodeURIComponent(
                        escape(air.current_song?.music || "Estamos offline"),
                    )}
                </marquee>
            </div>
        </div>
    </div>
    <!--Second Column-->
    <div class="block">
        <!--Host Image-->
        <div class="w-70">
            <img
                src={air.program.host.avatar}
                alt=""
                aria-hidden="true"
                aria-label="hidden"
                class="w-full h-full"
                loading="lazy"
            />
        </div>
    </div>
    <!--Third Column-->
    <div class="block">
        <!-- Player Type Information-->
        <div class={["mx-4 mb-10 py-2 px-6 gap-2 flex justify-center items-center rounded-md",
            { "bg-neutral-gray": air.type === "auto_dj" || air.type === "playlist" },
            { "bg-green-mint": air.type === "live" },
            { "bg-orange-citric": air.type === "scheduled" },
        ]}>
            <div class="block">
                {#if air.type === "auto_dj" || air.type === "playlist"}
                    <img
                        src="/svg/playlist.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-10 filter-blue-night"
                        loading="lazy"
                    />
                {:else if air.type === "live"}
                    <img
                        src="/svg/onair.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-10 filter-blue-night"
                        loading="lazy"
                    />
                {:else}
                    <img
                        src="/svg/disc.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-9 filter-blue-night"
                        loading="lazy"
                    />
                {/if}
            </div>
            <div class="shrink-0 font-noto-sans font-extrabold italic uppercase text-center text-md text-blue-night leading-4">
                {#if air.type === "auto_dj" || air.type === "playlist"}
                    Playlist <br />automática
                {:else if air.type === "live"}
                    Locut{air.program.host.gender === "male" ? "or" : "ora"}
                    <br />ao vivo
                {:else}
                    Programa<br />gravado
                {/if}
            </div>
        </div>
        <!-- Player Controls-->
        <div class="w-55 h-20 flex items-center justify-center gap-1">
            <div>
                <div class="ml-2 text-suspense-aurora text-lg font-noto-sans font-extrabold uppercase italic">
                    Dê o
                </div>
                <div class={["-mt-4 font-noto-sans font-extrabold uppercase italic",
                    { "text-blue-skywave text-[3.5rem]": !$player.playing },
                    { "text-orange-amber text-[3rem]": $player.playing },
                ]}>
                    {$player.playing ? "Pause" : "Play"}
                </div>
            </div>
            <button type="button"
                aria-label=""
                class={["cursor-pointer shrink-0 w-14 h-14 rounded-full flex justify-center items-center",
                    { "bg-blue-skywave": !$player.playing },
                    { "bg-orange-amber": $player.playing },
                ]}
                on:click={toggleAudio}
            >
                <img
                    src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                    alt=""
                    aria-hidden="true"
                    class="w-5"
                    loading="lazy"
                />
            </button>
        </div>
        <div class="mx-3 mb-5 flex flex-col gap-2">
            <div class="flex justify-between items-center px-1">
                <span class="text-[10px] text-suspense-aurora/40 font-extrabold uppercase">
                    Volume
                </span>
                <span class="text-[10px] text-orange-amber font-extrabold">
                    {Math.round($player.volume * 100)}%
                </span>
            </div>
            <input
                id="volume"
                name="volume"
                type="range"
                min="0"
                max="1"
                step="0.01"
                value={$player.volume}
                class="w-full accent-orange-amber h-1.5 rounded-full bg-white/10 cursor-pointer transition-all hover:bg-white/20"
                on:input={(e) => setVolume(e.target.value)}
            />
        </div>
        <!-- Song Request Button-->
        <button type="button"
            aria-label="Faça seu pedido"
            class="cursor-pointer w-full py-2 px-1 border border-suspense-aurora rounded-full text-blue-skywave text-xl text-center font-noto-sans font-extrabold italic uppercase disabled:cursor-not-allowed"
            on:click={() => { modalRef.open(); }}
        >
            & Faça seu <strong class="text-orange-amber">Pedido</strong>
        </button>
    </div>
</section>

<section class="container-player">
    <div class="mb-10 grid grid-cols-2 gap-5">
        <div class="bg-suspense-aurora/10 h-30 rounded-md flex justify-center items-center text-suspense-aurora/40 text-lg font-extrabold uppercase italic">
            Anúncio
        </div>
        <div class="bg-suspense-aurora/10 h-30 rounded-md flex justify-center items-center text-suspense-aurora/40 text-lg font-extrabold uppercase italic">
            Anúncio
        </div>
    </div>
</section>
