<script>
    import { page } from "@inertiajs/svelte";
    import { Modal } from "@/ui/components/public";
    import { SongRequestForm } from "@/ui/widgets/public";
    import { player, toggleAudio, setVolume } from "@/store";
    import { locutionIcons, locutionTextures, locutionDecorations } from "@/data";

    $: ({ onair: { data: [air] }, stream } = $page.props);

    let modalRef;

    $: playerData = {
        program: {
            image: air.program?.image,
        },
        host: {
            nickname: air.program?.host?.nickname,
            avatar: air.program?.host?.avatar,
            gender: air.program?.host?.gender,
        },
        execution_mode: air.execution_mode,
        current_song: {
            cover: stream.current_song.cover,
            music: stream.current_song.music,
        },
        phrase: {
            text: air.phrase.text,
            icon: air.phrase.icon ?? locutionIcons[10].url,
            texture: air.phrase.texture ?? locutionTextures[0].url,
            decoration: {
                left: air.phrase.decoration?.left ?? locutionDecorations[0].left,
                right: air.phrase.decoration?.right ?? locutionDecorations[0].right,
            },
        },
    };
    
    function splitHighlightedText(text) {
        return String(text).split(/(\[[^\]]+\])/g).filter(Boolean).map((part) => ({
            text: part.startsWith("[") && part.endsWith("]")
                ? part.slice(1, -1)
                : part,
            highlighted: part.startsWith("[") && part.endsWith("]"),
        }));
    }

</script>

<Modal bind:this={modalRef}>
    <div slot="content">
        <SongRequestForm />
    </div>
</Modal>

<!-- Phrase Section -->
<section class="w-full bg-contain bg-right bg-no-repeat mt-5 mb-7"  style={`background-image: url('${playerData.phrase.texture}'), var(--gradient-blue-ocean-cerulean);`}>
    <div class="container-player h-28  relative">
        <div class="absolute -top-7 left-0 xl:-left-25 z-10">
            <img
                src={playerData.phrase.decoration.left}
                alt=""
                aria-hidden="true"
                class="w-25"
                loading="lazy"
            />
        </div>
        <div class="w-full min-w-0 h-28 pr-32 xl:pr-40 flex items-center text-suspense-aurora text-3xl font-noto-sans font-extrabold uppercase italic">
            <span class="block max-w-full overflow-hidden text-ellipsis whitespace-nowrap leading-9">
                {#each splitHighlightedText(playerData.phrase.text) as phrasePart}
                    <span class:text-orange-amber={phrasePart.highlighted}>
                        {phrasePart.text}
                    </span>
                {/each}
            </span>
        </div>
        <div class="absolute bottom-0 right-5 xl:-right-15 z-10">
            <img
                src={playerData.phrase.icon}
                alt=""
                aria-hidden="true"
                class="w-35"
                loading="lazy"
            />
        </div>
        <div class="absolute -top-6 right-0 xl:-right-25 z-10">
            <img
                src={playerData.phrase.decoration.right}
                alt=""
                aria-hidden="true"
                class="w-25"
                loading="lazy"
            />
        </div>
    </div>
</section>

<!-- Main Player Section -->
<section class="container-player grid grid-cols-[3fr_1fr_1.2fr] items-center gap-5">
    <!-- First Column-->
    <div class="block">
        <!--Program and Host Information-->
        <div class="flex items-center gap-5 mb-5">
            <div class="w-60">
                <img
                src={playerData.program.image}
                    alt="Programa no ar"
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
                <div class="text-neutral-gray text-sm font-noto-sans uppercase">
                    COM DJ
                </div>
                <div class="w-full text-suspense-aurora text-3xl font-noto-sans font-extrabold uppercase italic">
                    {playerData.host.nickname}
                </div>
                <div class={["mt-[0.4rem] w-24 rounded-xl float-end text-center text-sm text-suspense-aurora font-noto-sans font-extrabold italic uppercase",
                    { "bg-neutral-gray": playerData.execution_mode === "auto_dj" || playerData.execution_mode === "playlist" },
                    { "bg-green-mint": playerData.execution_mode === "live" },
                    { "bg-orange-amber": playerData.execution_mode === "scheduled" },
                ]}>
                    {#if playerData.execution_mode === "auto_dj" || playerData.execution_mode === "playlist"}
                        Robô
                    {:else if playerData.execution_mode === "live"}
                        Human{playerData.host.gender === "male" ? "o" : "a"}
                    {:else}
                        Agendado
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
                    src={playerData.current_song.cover}
                    alt=""
                    aria-hidden="true"
                    class="rounded-md"
                    loading="lazy"
                />
            </div>
            <div class="w-full srink-0">
                <div class="text-orange-amber font-noto-sans uppercase italic">
                    Tocando agora:
                </div>
                <div class="w-full text-suspense-aurora text-lg font-noto-sans font-extrabold uppercase italic line-clamp-2 leading-6">
                    {decodeURIComponent(
                        escape(playerData.current_song.music || "Estamos offline"),
                    )}
                </div>
            </div>
        </div>
    </div>
    <!--Second Column-->
    <div class="block">
        <!--Host Image-->
        <div class="w-65">
            <img
                src={playerData.host.avatar}
                alt={playerData.host.nickname || "Locutor atual"}
                class="w-full h-full"
                loading="lazy"
            />
        </div>
    </div>
    <!--Third Column-->
    <div class="block">
        <!-- Player Type Information-->
        <div class={["h-10 mb-5 flex justify-center gap-2 items-center rounded-md",
            { "bg-neutral-gray": playerData.execution_mode === "auto_dj" || playerData.execution_mode === "playlist" },
            { "bg-green-mint": playerData.execution_mode === "live" },
            { "bg-orange-amber": playerData.execution_mode === "scheduled" },
        ]}>
            <div class="flex size-6 shrink-0">
                {#if playerData.execution_mode === "auto_dj"}
                    <img
                        src="/svg/robot.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-6 object-contain brightness-0 -mt-[0.1rem]"
                        loading="lazy"
                    />
                {:else if playerData.execution_mode === "playlist"}
                    <img
                        src="/svg/disc.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-6 object-contain brightness-0"
                        loading="lazy"
                    />
                {:else if playerData.execution_mode === "live"}
                    <img
                        src="/svg/onair.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-6 object-contain brightness-0"
                        loading="lazy"
                    />
                {:else}
                    <img
                        src="/svg/disc.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-6 object-contain brightness-0"
                        loading="lazy"
                    />
                {/if}
            </div>
            <div class="shrink-0 font-noto-sans font-bold italic uppercase text-center text-[0.9rem] text-blue-night leading-4">
                {#if playerData.execution_mode === "auto_dj"}
                    Playlist automática
                {:else if playerData.execution_mode === "playlist"}
                    Playlist personalizada
                {:else if playerData.execution_mode === "live"}
                    Locut{playerData.host.gender === "male" ? "or" : "ora"} ao vivo
                {:else}
                    Programa agendado
                {/if}
            </div>
        </div>
        <!-- Player Controls-->
        <div class="h-25 flex items-center justify-center">
            <div>
                <div class={["text-suspense-aurora text-lg font-noto-sans font-extrabold uppercase italic",
                    {"ml-3": !$player.playing},
                    {"ml-2": $player.playing},
                ]}>
                    Dê o
                </div>
                <div class={["font-noto-sans font-extrabold uppercase italic",
                    { "text-orange-citric text-[3.9rem] -mt-6": !$player.playing },
                    { "text-blue-skywave text-[3.1rem] -mt-5": $player.playing },
                ]}>
                    {$player.playing ? "Pause" : "Play"}
                </div>
            </div>
            <button type="button"
                aria-label={$player.playing ? "Pausar radio" : "Tocar radio"}
                class={["cursor-pointer shrink-0 w-14 h-14 rounded-full flex justify-center items-center",
                    { "bg-orange-citric": !$player.playing },
                    { "bg-blue-skywave": $player.playing },
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
                <span class="text-[10px] text-orange-citric font-extrabold">
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
                class="w-full accent-orange-citric h-1.5 rounded-full cursor-pointer"
                on:input={(e) => setVolume(e.target.value)}
            />
        </div>
        <!-- Song Request Button-->
        <button type="button"
            aria-label="Faça seu pedido"
            class="cursor-pointer w-full py-2 px-1 border border-suspense-aurora rounded-full text-blue-skywave text-xl text-center font-noto-sans font-extrabold italic uppercase disabled:cursor-not-allowed"
            on:click={() => modalRef.open()}
        >
            & Faça seu <strong class="text-orange-citric">Pedido</strong>
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
