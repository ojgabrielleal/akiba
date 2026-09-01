<script>
    import { onMount } from "svelte";
    import toast from "svelte-hot-french-toast";
    import {
        AdvertisementSlot,
        AuthGuard,
        CustomModal,
        LoadingSpinner,
    } from "@/lib/components/public";
    import { SongRequestForm } from "@/lib/widgets/public";
    import { player, toggleAudio, setVolume } from "@/lib/stores";
    import { locutionIcons, locutionTextures, locutionDecorations } from "@/lib/constants";
    import {
        listenForOAuthAction,
        OAuthAction,
        resolvePlaceholderImage,
        themeClass,
    } from "@/lib/utils";

    export let onair = null;
    export let stream = null;
    export let oauth = {};

    $: air = onair?.data?.[0] ?? {};
    $: currentSong = stream?.current_song ?? {};
    $: canRender = Boolean(onair?.data?.[0]);
    $: hasActiveHost = air?.execution_mode === "live";
    $: requestActionVisible = $player.playing && hasActiveHost;
    $: canRequestSong = requestActionVisible && air?.allows_song_requests;

    let modalRef;
    let coverLightboxOpen = false;

    onMount(() =>
        listenForOAuthAction(
            OAuthAction.OPEN_SONG_REQUEST,
            () => modalRef.open(),
        ),
    );

    $: playerData = {
        program: {
            name: air?.program?.name,
            image: air?.program?.image,
        },
        host: {
            nickname: air?.program?.host?.nickname,
            avatar: air?.program?.host?.avatar,
            gender: air?.program?.host?.gender,
        },
        execution_mode: air?.execution_mode,
        current_song: {
            cover: currentSong.cover,
            music: currentSong.music,
        },
        phrase: {
            text: air?.phrase?.text,
            icon: air?.phrase?.icon ?? locutionIcons[10].url,
            texture: air?.phrase?.texture ?? locutionTextures[0].url,
            decoration: {
                left: air?.phrase?.decoration?.left ?? locutionDecorations[0].left,
                right: air?.phrase?.decoration?.right ?? locutionDecorations[0].right,
            },
        },
    };

    const splitHighlightedText = (text) => {
        return String(text).split(/(\[[^\]]+\])/g).filter(Boolean).map((part) => ({
            text: part.startsWith("[") && part.endsWith("]")
                ? part.slice(1, -1)
                : part,
            highlighted: part.startsWith("[") && part.endsWith("]"),
        }));
    }

    const handlePlayerAction = () => {
        if (canRequestSong) {
            modalRef.open();
            return;
        }

        if (requestActionVisible) {
            toast.error("Pedidos fechados. Fica na escuta!");
            return;
        }

        toggleAudio();
    };

    const openCoverLightbox = () => {
        coverLightboxOpen = true;
    };

    const closeCoverLightbox = () => {
        coverLightboxOpen = false;
    };

    const handleKeydown = (event) => {
        if (coverLightboxOpen && event.key === "Escape") {
            closeCoverLightbox();
        }
    };

</script>

<svelte:window on:keydown={handleKeydown} />

<CustomModal bind:this={modalRef}>
    <div slot="content" let:close>
        {#if air?.allows_song_requests}
            <AuthGuard
                title="Entre para pedir sua música"
                description="Use sua conta para continuar."
                action={OAuthAction.OPEN_SONG_REQUEST}
                {oauth}
            >
                <SongRequestForm {close} {oauth} />
            </AuthGuard>
        {:else}
            <div class="px-2 py-4 text-center font-noto-sans">
                <h2 class="text-xl font-extrabold text-blue-night">
                    Pedidos musicais fechados
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    A locução ainda não abriu os pedidos.
                </p>
            </div>
        {/if}
    </div>
</CustomModal>

{#if coverLightboxOpen}
    <button
        type="button"
        class="fixed inset-0 z-[220] flex cursor-zoom-out items-center justify-center bg-blue-night/92 p-4 backdrop-blur-sm focus-visible:outline-none"
        aria-label="Fechar capa da música"
        on:click={closeCoverLightbox}
    >
        <img
            src={resolvePlaceholderImage(playerData.current_song.cover, "placeholder")}
            alt={playerData.current_song.music || "Capa da música atual"}
            class="max-h-[88vh] max-w-[92vw] rounded-md object-contain shadow-2xl shadow-blue-night/70"
        />
    </button>
{/if}

<!-- Phrase Section -->
{#if canRender}
<section class="main-player-phrase-background w-full bg-contain bg-right bg-no-repeat mt-5 mb-4"  style={`--main-player-phrase-texture: url('${playerData.phrase.texture}'); background-image: var(--main-player-phrase-texture), var(--main-player-phrase-gradient, var(--gradient-blue-ocean-cerulean));`}>
    <div class="container-player h-[90px] relative">
        <div class="absolute -top-6 left-0 z-10 xl:-left-24">
            <img
                src={playerData.phrase.decoration.left}
                alt=""
                aria-hidden="true"
                class="w-24"
                loading="lazy"
            />
        </div>
        <div class="main-player-phrase-text w-full min-w-0 h-[90px] pr-30 xl:pr-36 pl-18 xl:pl-0 flex items-center text-suspense-aurora text-[1.65rem] font-noto-sans font-extrabold uppercase italic">
            <span class="block w-full overflow-hidden text-ellipsis text-left whitespace-nowrap leading-9">
                {#each splitHighlightedText(playerData.phrase.text) as phrasePart}
                    <span class:main-player-phrase-highlight={phrasePart.highlighted} class:text-orange-amber={phrasePart.highlighted}>
                        {phrasePart.text}
                    </span>
                {/each}
            </span>
        </div>
        <div class="absolute right-4 bottom-0 z-10 xl:-right-18">
            <img
                src={playerData.phrase.icon}
                alt=""
                aria-hidden="true"
                class="w-32"
                loading="lazy"
            />
        </div>
        <div class="absolute -top-6 right-0 z-10 xl:-right-24">
            <img
                src={playerData.phrase.decoration.right}
                alt=""
                aria-hidden="true"
                class="w-24"
                loading="lazy"
            />
        </div>
    </div>
</section>

<!-- Main Player Section -->
<section class="main-player-content container-player grid grid-cols-[3fr_1fr_1.2fr] items-center gap-5">
    <!-- First Column-->
    <div class="block">
        <!--Program and Host Information-->
        <div class="flex items-center gap-5 mb-10">
            <div class="w-60">
                <img
                    src={resolvePlaceholderImage(playerData.program.image, "program")}
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
                <div class={["mt-[0.4rem] w-24 rounded-xl float-end text-center text-sm font-noto-sans font-extrabold italic uppercase", themeClass("text", "suspense-aurora", { fixed: true }),
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
        <div class="-mt-3 flex gap-3 items-end">
            <button
                type="button"
                class="group/cover w-20 shrink-0 cursor-zoom-in rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                aria-label="Ver capa da música em tela cheia"
                on:click={openCoverLightbox}
            >
                <img
                    src={resolvePlaceholderImage(playerData.current_song.cover, "placeholder")}
                    alt={playerData.current_song.music || "Capa da música atual"}
                    class="rounded-md transition duration-300 ease-out group-hover/cover:scale-[1.03] group-focus-visible/cover:scale-[1.03] motion-reduce:transform-none motion-reduce:transition-none"
                    loading="lazy"
                />
            </button>
            <div class="w-full srink-0">
                <div class="text-orange-amber font-noto-sans uppercase italic">
                    Tocando agora:
                </div>
                <div class="w-full text-suspense-aurora text-lg font-noto-sans font-extrabold uppercase italic line-clamp-2 leading-6">
                    {playerData.current_song.music || "Estamos offline"}
                </div>
            </div>
        </div>
    </div>
    <!--Second Column-->
    <div class="block">
        <!--Host Image-->
        <div class="w-60">
            <img
                src={resolvePlaceholderImage(playerData.host.avatar, "avatar", playerData.host.gender)}
                alt={playerData.host.nickname || "Locutor atual"}
                class="w-full h-full"
                loading="lazy"
            />
        </div>
    </div>
    <!--Third Column-->
    <div class="flex flex-col">
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
            <div class={["shrink-0 font-noto-sans font-bold italic uppercase text-center text-[0.9rem] leading-4", themeClass("text", "blue-night", { fixed: true })]}>
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
        <div class="h-25 flex items-center justify-center" data-player-controls>
            <div>
                <div class={["text-suspense-aurora text-lg font-noto-sans font-extrabold uppercase italic",
                    {"ml-3": !$player.playing},
                    {"ml-2": $player.playing},
                ]}>
                    {$player.playing ? "Não" : "Dê o"}
                </div>
                <div class={["font-noto-sans font-extrabold uppercase italic",
                    { "text-orange-amber text-[3.9rem] -mt-6": !$player.playing },
                    { "text-blue-skywave text-[3.1rem] -mt-5": $player.playing },
                ]}>
                    {$player.playing ? "Pause" : "Play"}
                </div>
            </div>
            <button type="button"
                aria-label={$player.loading ? "Carregando rádio" : $player.playing ? "Pausar radio" : "Tocar radio"}
                aria-busy={$player.loading}
                disabled={$player.loading && !$player.playing}
                class={["cursor-pointer shrink-0 w-14 h-14 rounded-full flex justify-center items-center shadow-lg transition duration-200 ease-out hover:scale-105 active:scale-95 motion-reduce:transform-none motion-reduce:transition-none",
                    { "bg-orange-amber": !$player.playing },
                    { "bg-blue-skywave": $player.playing },
                    { "cursor-wait": $player.loading },
                ]}
                on:click={toggleAudio}
            >
                {#if $player.loading}
                    <LoadingSpinner size="md" tone="dark" label="Carregando rádio" />
                {:else}
                    <img
                        src={$player.playing ? "/svg/pause.svg" : "/svg/play.svg"}
                        alt=""
                        aria-hidden="true"
                        class="w-5"
                        loading="lazy"
                    />
                {/if}
            </button>
        </div>
        <div class="mx-3 mb-5 flex flex-col gap-2" data-player-controls>
            <div class="flex justify-between items-center px-1">
                <span class="text-[10px] text-suspense-aurora/40 font-extrabold uppercase">
                    Volume
                </span>
                <span class={[
                    "text-[10px] font-extrabold",
                    { "text-orange-amber": !$player.playing },
                    { "text-blue-skywave": $player.playing },
                ]}>
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
                class={[
                    "w-full h-1.5 rounded-full cursor-pointer",
                    { "accent-orange-amber": !$player.playing },
                    { "accent-blue-skywave": $player.playing },
                ]}
                on:input={(e) => setVolume(e.target.value)}
            />
        </div>
        <!-- Song Request Button-->
        <button type="button"
            aria-label={requestActionVisible ? "Faça o seu pedido" : "Escute Akiba"}
            data-player-controls
            class={[
                "cursor-pointer w-full min-h-12 px-1 border-2 border-suspense-aurora rounded-full flex items-center justify-center text-blue-skywave text-lg leading-none text-center font-noto-sans font-extrabold italic uppercase transition-transform duration-200 ease-out hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] motion-reduce:transition-none",
                { "song-request-active": canRequestSong },
            ]}
            on:click={handlePlayerAction}
        >
            <span class="flex items-center justify-center gap-1">
                {#if requestActionVisible}
                    <span>& Faça o seu</span>
                    <strong class="text-orange-amber">Pedido</strong>
                {:else}
                    <span>& Escute</span>
                    <strong class="text-orange-amber">Akiba</strong>
                {/if}
            </span>
        </button>
    </div>
</section>
{/if}

{#if canRender}
<section class="container-player" aria-label="Publicidade">
    <div class="mb-10 grid grid-cols-2 gap-5">
        {#each Array(2) as _, index}
            <AdvertisementSlot mirrored={index === 1} />
        {/each}
    </div>
</section>
{/if}
