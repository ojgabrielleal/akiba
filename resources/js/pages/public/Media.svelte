<script>
    import { Link, page, router } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { AuthGuard, Button, EditorialTitle, MinimalEmptyState, Modal, Section } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { ListenerGalleryGrid } from "@/lib/widgets/public";
    import { publicAnimations } from "@/lib/constants";
    import { resolvePlaceholderImage, themeClass } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, events, listenerGallery, polls, latestPoll, enigmagame } = $page.props);
    $: pageUrl = $page.url;
    $: eventList = Array.isArray(events) ? events : events?.data ?? [];
    $: poll = latestPoll?.data ?? null;
    $: pollList = (Array.isArray(polls) ? polls : polls?.data ?? []).filter((item) => item.uuid !== poll?.uuid);
    $: selectedPoll = selectedPollUuid ? [poll, ...pollList].find((item) => item?.uuid === selectedPollUuid) : null;

    const resolveEventDate = (event) => event.metadata?.dates ?? "";
    const resolveEventPlace = (event) => event.metadata?.address ?? "";
    const optionPercent = (pollItem, option) => pollItem?.total_votes ? (option.votes / pollItem.total_votes) * 100 : 0;
    const enigmaGameStartedStorageKey = (uuid) => `akiba_enigmagame_started_${uuid}`;

    let pollModalRef;
    let mainSelectedOption = null;
    let selectedPollUuid = null;
    let selectedOption = null;
    let voting = false;
    let mainVoting = false;
    let enigmagameContent = "";
    let enigmagameSubmitting = null;
    let enigmaGameStarted = false;
    let enigmaGameHasSave = false;
    let enigmaGameStartedUuid = null;

    $: if (enigmagame?.data?.uuid !== enigmaGameStartedUuid) {
        enigmaGameStartedUuid = enigmagame?.data?.uuid ?? null;
        enigmaGameHasSave = enigmaGameStartedUuid && typeof sessionStorage !== "undefined"
            ? sessionStorage.getItem(enigmaGameStartedStorageKey(enigmaGameStartedUuid)) === "1"
            : false;
        enigmaGameStarted = false;
    }

    function startEnigmaGame() {
        enigmaGameStarted = true;
        enigmaGameHasSave = true;

        if (enigmaGameStartedUuid && typeof sessionStorage !== "undefined") {
            sessionStorage.setItem(enigmaGameStartedStorageKey(enigmaGameStartedUuid), "1");
        }
    }

    function restartEnigmaGame() {
        enigmaGameStarted = false;
        enigmaGameHasSave = false;

        if (enigmaGameStartedUuid && typeof sessionStorage !== "undefined") {
            sessionStorage.removeItem(enigmaGameStartedStorageKey(enigmaGameStartedUuid));
        }
    }

    function openPollModal(pollItem) {
        if (pollItem.has_voted) return;

        selectedPollUuid = pollItem.uuid;
        selectedOption = null;
        pollModalRef.open();
    }

    function submitVote() {
        if (!selectedOption || voting || selectedPoll?.has_voted) return;

        voting = true;
        router.post(`/poll/option/${selectedOption}/vote`, {}, {
            preserveScroll: true,
            onFinish: () => {
                voting = false;
                pollModalRef.close();
            },
        });
    }

    function submitMainVote() {
        if (!mainSelectedOption || mainVoting || poll?.has_voted) return;

        mainVoting = true;
        router.post(`/poll/option/${mainSelectedOption}/vote`, {}, {
            preserveScroll: true,
            onFinish: () => {
                mainVoting = false;
            },
        });
    }

    function submitEnigmaGameInteraction(type) {
        const currentEnigmaGame = enigmagame?.data;
        if (!currentEnigmaGame || !enigmagameContent.trim() || enigmagameSubmitting || !currentEnigmaGame.participation?.can_interact) return;

        enigmagameSubmitting = type;
        router.post(`/midias/enigma/${currentEnigmaGame.uuid}/interaction`, {
            type,
            content: enigmagameContent,
        }, {
            preserveScroll: true,
            onSuccess: () => enigmagameContent = "",
            onFinish: () => enigmagameSubmitting = null,
        });
    }
</script>

<Meta meta={{ title: "Mídias" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <h1 class="sr-only">Mídias</h1>
    <main class="public-page-background flex flex-col bg-blue-marinho">
        <div class="public-page-background order-1 bg-blue-night">
            <EditorialTitle title="Super conteúdos" padding="py-6" spacer />
        </div>

        <Section title="Eventos" styles="container-page order-3 mt-10 mb-12">
            {#if eventList.length > 0}
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                    {#each eventList as item (item.uuid)}
                        <Link href={item.href} class={["group overflow-hidden rounded-md bg-orange-amber text-blue-night focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber", publicAnimations.cardInteractive]}>
                            <div class="aspect-[16/9] overflow-hidden bg-neutral-gray">
                                <img
                                    src={resolvePlaceholderImage(item.cover || item.image, "placeholder")}
                                    alt={item.title}
                                    class={["h-full w-full object-cover", publicAnimations.imageZoom]}
                                    loading="lazy"
                                />
                            </div>
                            <div class="min-h-12 px-3 py-2 text-center font-noto-sans uppercase italic">
                                <h3 class="line-clamp-1 text-base font-black">{item.title}</h3>
                                <p class="line-clamp-1 text-sm font-bold">
                                    {resolveEventPlace(item)} {resolveEventDate(item)}
                                </p>
                            </div>
                        </Link>
                    {/each}
                </div>
            {:else}
                <MinimalEmptyState
                    title="Nenhum evento no radar"
                    message="Novos eventos aparecem aqui assim que entrarem na agenda."
                />
            {/if}
        </Section>

        <ListenerGalleryGrid
            {listenerGallery}
            styles="container-page order-4 mb-12"
            emptyTitle="Nenhuma mídia enviada"
            emptyMessage="As fotos da comunidade aparecem aqui quando forem publicadas."
        />

        <Section title="Enigma Game" styles="container-page order-2 mt-10 mb-12">
            {#if enigmagame?.data}
                <div class="relative min-h-[22rem] overflow-hidden bg-blue-night/25 px-3 py-4 [[data-public-theme=light]_&]:bg-transparent sm:px-5 lg:px-6">
                    {#if !enigmaGameStarted}
                        <div class="absolute inset-0 z-20 grid place-items-center overflow-hidden bg-blue-night/90 px-5 py-8 text-center [[data-public-theme=light]_&]:bg-neutral-light/90">
                            <div class="pointer-events-none absolute inset-0 opacity-[0.1]" style="background-image: repeating-linear-gradient(0deg, transparent 0 7px, rgba(255,255,255,0.08) 8px, transparent 9px);"></div>
                            <div class="pointer-events-none absolute inset-5 rounded-sm border border-orange-amber/35"></div>
                            <div class="pointer-events-none absolute left-8 top-8 grid grid-cols-2 gap-1">
                                <span class="size-2 bg-orange-amber/80"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                            </div>
                            <div class="pointer-events-none absolute right-8 top-8 grid grid-cols-2 gap-1">
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/80"></span>
                                <span></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                            </div>
                            <div class="pointer-events-none absolute bottom-8 left-8 grid grid-cols-2 gap-1">
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span></span>
                                <span class="size-2 bg-orange-amber/80"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                            </div>
                            <div class="pointer-events-none absolute bottom-8 right-8 grid grid-cols-2 gap-1">
                                <span></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/80"></span>
                            </div>
                            <div class="pointer-events-none absolute left-10 top-1/2 hidden -translate-y-1/2 grid-cols-2 gap-1 opacity-70 sm:grid">
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                            </div>
                            <div class="pointer-events-none absolute right-10 top-1/2 hidden -translate-y-1/2 grid-cols-2 gap-1 opacity-70 sm:grid">
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber"></span>
                            </div>
                            <div class="pointer-events-none absolute left-12 top-16 hidden w-28 gap-1 sm:grid">
                                <span class="h-1.5 w-full bg-orange-amber"></span>
                                <span class="h-1.5 w-3/4 bg-orange-amber/60"></span>
                                <span class="h-1.5 w-1/2 bg-orange-amber/35"></span>
                            </div>
                            <div class="pointer-events-none absolute right-12 top-16 hidden w-28 justify-items-end gap-1 sm:grid">
                                <span class="h-1.5 w-full bg-orange-amber"></span>
                                <span class="h-1.5 w-3/4 bg-orange-amber/60"></span>
                                <span class="h-1.5 w-1/2 bg-orange-amber/35"></span>
                            </div>
                            <div class="pointer-events-none absolute bottom-16 left-12 hidden items-end gap-1 opacity-65 sm:flex">
                                <span class="h-3 w-2 bg-orange-amber/35"></span>
                                <span class="h-5 w-2 bg-orange-amber/60"></span>
                                <span class="h-8 w-2 bg-orange-amber"></span>
                                <span class="h-4 w-2 bg-orange-amber/45"></span>
                            </div>
                            <div class="pointer-events-none absolute bottom-16 right-12 hidden grid-cols-3 gap-1 opacity-65 sm:grid">
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-transparent"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber"></span>
                                <span class="size-2 bg-transparent"></span>
                                <span class="size-2 bg-transparent"></span>
                                <span class="size-2 bg-orange-amber/45"></span>
                                <span class="size-2 bg-orange-amber"></span>
                            </div>

                            <div class="relative w-full max-w-xl font-noto-sans uppercase italic">
                                <div class="mb-5 flex items-center justify-center gap-2 text-[0.62rem] font-black tracking-[0.18em] text-orange-amber">
                                    <span class="inline-flex gap-1" aria-hidden="true">
                                        <span class="size-2 bg-orange-citric"></span>
                                        <span class="size-2 bg-orange-citric"></span>
                                        <span class="size-2 bg-orange-citric"></span>
                                    </span>
                                    <span>{enigmagame.data.solved ? "Clear" : "Lv. 01"}</span>
                                    <span class="text-suspense-aurora/45 [[data-public-theme=light]_&]:text-blue-night/45">
                                        {enigmagame.data.solved ? "Winner Found" : "Score 0000"}
                                    </span>
                                </div>
                                <div class="mx-auto mb-4 grid max-w-52 grid-cols-[1fr_auto_1fr] items-center gap-3 text-[0.58rem] font-black tracking-[0.16em] text-orange-amber/80">
                                    <span class="h-px bg-orange-amber/45"></span>
                                    <span>{enigmagame.data.solved ? "Game Clear" : "Stage Ready"}</span>
                                    <span class="h-px bg-orange-amber/45"></span>
                                </div>
                                <div class="mx-auto mb-5 grid size-14 place-items-center rounded-sm border-2 border-orange-amber/80 bg-blue-ocean/80 [[data-public-theme=light]_&]:bg-blue-night">
                                    <img src="/svg/search.svg" alt="" aria-hidden="true" class="size-8 filter-orange-amber" loading="lazy" />
                                </div>
                                <p class="text-xs font-black tracking-[0.28em] text-orange-amber">Akiba Game</p>
                                <h3 class="mt-3 text-3xl font-black text-suspense-aurora [[data-public-theme=light]_&]:text-blue-night sm:text-4xl">
                                    {enigmagame.data.solved ? "Game Clear" : "Enigma Game"}
                                </h3>
                                <p class="mx-auto mt-4 max-w-md text-sm font-black leading-6 text-suspense-aurora/75 [[data-public-theme=light]_&]:text-blue-night/70">
                                    {#if enigmagame.data.solved}
                                        {enigmagame.data.solved_by?.name ?? "Um jogador"} venceu esta fase. Abra o resultado para ver a solução.
                                    {:else}
                                        {enigmaGameHasSave ? "Continue sua investigação ou reinicie a fase." : "Aperte start para liberar a pista e entrar na investigação."}
                                    {/if}
                                </p>
                                <button
                                    type="button"
                                    class={[publicAnimations.buttonInteractive, "mt-7 inline-flex min-h-11 min-w-40 cursor-pointer items-center justify-center rounded-sm border-2 border-orange-morning bg-orange-citric px-7 py-2.5 text-sm font-black uppercase italic text-blue-night hover:bg-orange-morning active:translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric"]}
                                    on:click={startEnigmaGame}
                                >
                                    <span class="mr-3 text-blue-night/70" aria-hidden="true">▶</span>
                                    {#if enigmagame.data.solved}
                                        Ver Resultado
                                    {:else}
                                        {enigmaGameHasSave ? "Continue" : "Start"}
                                    {/if}
                                    <span class="ml-3 text-blue-night/70" aria-hidden="true">◀</span>
                                </button>
                                {#if enigmaGameHasSave && !enigmagame.data.solved}
                                    <button
                                        type="button"
                                        class={[publicAnimations.buttonInteractive, "mt-4 inline-flex min-h-9 cursor-pointer items-center justify-center rounded-sm border border-orange-amber/70 px-5 py-2 text-[0.68rem] font-black uppercase italic tracking-[0.12em] text-orange-amber hover:bg-orange-amber/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric"]}
                                        on:click={restartEnigmaGame}
                                    >
                                        Restart
                                    </button>
                                {/if}
                                <div class="mt-5 flex items-center justify-center gap-2 text-[0.62rem] font-black tracking-[0.16em] text-suspense-aurora/45 [[data-public-theme=light]_&]:text-blue-night/45">
                                    <span class="size-1.5 bg-orange-amber"></span>
                                    <span>{enigmagame.data.solved ? "Result Unlocked" : "1 Player"}</span>
                                    <span class="size-1.5 bg-orange-amber"></span>
                                </div>
                                <div class="mx-auto mt-3 flex max-w-44 items-center justify-center gap-1" aria-hidden="true">
                                    <span class="h-1.5 w-7 bg-orange-amber"></span>
                                    <span class="h-1.5 w-4 bg-orange-amber/45"></span>
                                    <span class="h-1.5 w-4 bg-orange-amber/45"></span>
                                    <span class="h-1.5 w-7 bg-orange-amber"></span>
                                </div>
                            </div>
                        </div>
                    {/if}

                    <div class={["grid gap-5 transition duration-300 lg:grid-cols-[minmax(0,1fr)_1px_minmax(16rem,0.48fr)] lg:items-stretch lg:gap-8", !enigmaGameStarted && "select-none opacity-30 blur-[1px]"]}>
                    <div class="grid gap-3">
                        {#if enigmagame.data.participation?.has_submitted_final_answer}
                            <div class={["border-l-2 border-orange-amber pl-3 font-noto-sans text-sm font-semibold uppercase italic text-orange-morning", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                {#if enigmagame.data.participation.final_answer_result === "correct"}
                                    Sua resposta definitiva esta certa.
                                {:else if enigmagame.data.participation.final_answer_result === "incorrect"}
                                    Sua resposta definitiva foi analisada, mas nao solucionou o enigma.
                                {:else}
                                    Sua resposta definitiva ja foi enviada para este enigma.
                                {/if}
                            </div>
                        {:else if enigmagame.data.solved}
                            <div class={["border-l-2 border-orange-amber pl-3 font-noto-sans text-sm font-semibold uppercase italic text-orange-morning", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                Este enigma ja foi resolvido.
                            </div>
                        {:else if !enigmagame.data.participation?.can_interact && enigmagame.data.participation?.next_interaction_at}
                            <div class={["border-l-2 border-orange-amber pl-3 font-noto-sans text-sm font-semibold uppercase italic text-orange-morning", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                Aguarde ate {enigmagame.data.participation.next_interaction_at} para interagir novamente.
                            </div>
                        {:else}
                            <AuthGuard
                                {oauth}
                                compact
                                buttonLabel="Entre para participar"
                                filters="filter-blue-night"
                                buttonClass="text-blue-night"
                                reason="enigmagame"
                            >
                                <form class="grid gap-3" on:submit|preventDefault>
                                    <textarea
                                        class={["min-h-28 resize-none rounded-[7px] border border-suspense-aurora/15 bg-blue-ocean px-4 py-3 font-noto-sans text-sm font-normal text-suspense-aurora outline-none ring-0 placeholder:text-suspense-aurora/55 focus:border-suspense-aurora/15 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0", themeClass("bg", "neutral-light", { fixed: true, theme: "light" }), themeClass("text", "blue-night", { fixed: true, theme: "light" }), themeClass("placeholder", "blue-night/45", { theme: "light" })]}
                                        bind:value={enigmagameContent}
                                        placeholder="Digite sua pergunta ou resposta para o enigma"
                                        required
                                    ></textarea>
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            variant="accent"
                                            shape="pill"
                                            size="sm"
                                            class="px-6"
                                            loading={enigmagameSubmitting === "question"}
                                            disabled={!enigmagameContent.trim() || enigmagameSubmitting}
                                            on:click={() => submitEnigmaGameInteraction("question")}
                                        >
                                            Perguntar
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="accent"
                                            shape="pill"
                                            size="sm"
                                            class="px-6"
                                            loading={enigmagameSubmitting === "final_answer"}
                                            disabled={!enigmagameContent.trim() || enigmagameSubmitting}
                                            on:click={() => submitEnigmaGameInteraction("final_answer")}
                                        >
                                            Responder enigma
                                        </Button>
                                    </div>
                                </form>
                            </AuthGuard>
                        {/if}

                        {#if enigmagame.data.solved && enigmagame.data.solved_by}
                            <div class={["flex items-center gap-3 rounded-[7px] border border-green-forest/40 bg-green-forest/10 px-4 py-3", themeClass("bg", "neutral-light", { fixed: true, theme: "light" })]}>
                                <img
                                    src={resolvePlaceholderImage(enigmagame.data.solved_by.avatar, "avatar", enigmagame.data.solved_by.gender)}
                                    alt=""
                                    aria-hidden="true"
                                    class="size-10 rounded-full border-2 border-neutral-gray/35 bg-transparent object-cover"
                                    loading="lazy"
                                />
                                <div class="min-w-0 font-noto-sans uppercase italic">
                                    <div class="truncate text-sm font-black text-green-forest">
                                        {enigmagame.data.solved_by.name} acertou o enigma
                                    </div>
                                    {#if enigmagame.data.solved_at}
                                        <div class={["text-xs font-bold text-suspense-aurora/70", themeClass("text", "blue-night/70", { theme: "light" })]}>
                                            {enigmagame.data.solved_at}
                                        </div>
                                    {/if}
                                    {#if enigmagame.data.solution}
                                        <div class={["mt-2 text-sm font-normal normal-case not-italic text-suspense-aurora", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                            {enigmagame.data.solution}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/if}

                        {#if enigmagame.data.interactions?.length}
                            <div class="public-themed-scrollbar mt-4 grid max-h-[13rem] gap-3 overflow-y-auto overscroll-contain lg:max-h-[16rem]">
                                {#each enigmagame.data.interactions as interaction (interaction.uuid)}
                                    <article class="grid gap-3">
                                        <div class="flex items-start gap-6">
                                            <div class="mt-0.5 size-12 shrink-0 overflow-hidden rounded-full border-[3px] border-neutral-gray/35 bg-transparent">
                                                <img
                                                    src={resolvePlaceholderImage(interaction.participant?.avatar, "avatar", interaction.participant?.gender)}
                                                    alt=""
                                                    aria-hidden="true"
                                                    class="h-full w-full scale-125 object-cover object-top"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div class={[
                                                "relative min-h-16 min-w-0 flex-1 rounded-[7px] border border-transparent px-[18px] py-[10px] before:absolute before:left-[-18px] before:top-[16px] before:size-0 before:border-y-[13px] before:border-r-[19px] before:border-y-transparent before:content-['']",
                                                interaction.result === "correct"
                                                    ? `bg-orange-amber ${themeClass("text", "blue-night", { fixed: true })} before:border-r-orange-amber [[data-public-theme=light]_&]:bg-orange-morning [[data-public-theme=light]_&]:before:border-r-orange-morning`
                                                    : `bg-blue-ocean text-suspense-aurora before:border-r-blue-ocean ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} ${themeClass("text", "blue-night", { fixed: true, theme: "light" })} [[data-public-theme=light]_&]:border-blue-night/10 [[data-public-theme=light]_&]:before:border-r-[#e8e8e8]`,
                                            ]}>
                                                <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 font-noto-sans leading-none">
                                                    <h3 class="truncate text-[12px] font-black uppercase italic leading-none">
                                                        {interaction.participant?.name ?? "Ouvinte"}
                                                    </h3>
                                                    <span class="text-[11px] font-black leading-none opacity-55">
                                                        • {interaction.created_at}
                                                    </span>
                                                </div>
                                                <p class="mt-3 whitespace-pre-line font-noto-sans text-sm font-normal leading-relaxed">
                                                    {interaction.content}
                                                </p>
                                            </div>
                                        </div>

                                        {#if interaction.admin_response}
                                            <div class="ml-12 flex items-start gap-5">
                                                <div class="mt-0.5 size-10 shrink-0 overflow-hidden rounded-full border-[3px] border-neutral-gray/35 bg-transparent">
                                                    <img
                                                        src={resolvePlaceholderImage(interaction.responder?.avatar, "avatar", interaction.responder?.gender)}
                                                        alt=""
                                                        aria-hidden="true"
                                                        class="h-full w-full scale-125 object-cover object-top"
                                                        loading="lazy"
                                                    />
                                                </div>
                                                <div class={["relative min-h-14 min-w-0 flex-1 rounded-[7px] border border-transparent bg-blue-ocean px-4 py-2.5 text-suspense-aurora before:absolute before:left-[-14px] before:top-[13px] before:size-0 before:border-y-[10px] before:border-r-[15px] before:border-y-transparent before:border-r-blue-ocean before:content-['']", themeClass("bg", "neutral-light", { fixed: true, theme: "light" }), themeClass("text", "blue-night", { fixed: true, theme: "light" }), "[[data-public-theme=light]_&]:border-blue-night/10 [[data-public-theme=light]_&]:before:border-r-[#e8e8e8]"]}>
                                                    <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 font-noto-sans leading-none">
                                                        <h4 class="truncate text-[11px] font-black uppercase italic leading-none">
                                                            {interaction.responder?.nickname ?? interaction.responder?.name ?? "Equipe Akiba"}
                                                        </h4>
                                                        <span class="text-[10px] font-black leading-none opacity-55">
                                                            • {interaction.responded_at}
                                                        </span>
                                                    </div>
                                                    <p class="mt-2 whitespace-pre-line font-noto-sans text-sm font-normal leading-relaxed">
                                                        {interaction.admin_response}
                                                    </p>
                                                </div>
                                            </div>
                                        {/if}
                                    </article>
                                {/each}
                            </div>
                        {/if}
                    </div>
                    <div class="hidden w-px bg-orange-amber/45 [[data-public-theme=light]_&]:bg-blue-cerulean lg:block"></div>
                    <div class={["p-1 text-suspense-aurora lg:sticky lg:top-24", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                        {#if enigmagame.data.author}
                            <div class="mb-4 flex items-center gap-3">
                                <img
                                    src={resolvePlaceholderImage(enigmagame.data.author.avatar, "avatar", enigmagame.data.author.gender)}
                                    alt=""
                                    aria-hidden="true"
                                    class="size-10 rounded-full border-2 border-neutral-gray/35 bg-transparent object-cover"
                                    loading="lazy"
                                />
                                <div class="min-w-0 font-noto-sans uppercase italic">
                                    <div class={["truncate text-sm font-black text-orange-morning", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                        {enigmagame.data.author.nickname ?? enigmagame.data.author.name}
                                    </div>
                                    <div class={["text-xs font-bold text-suspense-aurora", themeClass("text", "blue-night/70", { theme: "light" })]}>
                                        lançou um enigma
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <p class={["whitespace-pre-line font-noto-sans text-sm font-normal leading-relaxed text-suspense-aurora", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                            {enigmagame.data.content}
                        </p>
                    </div>
                    </div>
                </div>
            {:else}
                <MinimalEmptyState
                    title="Nenhum enigma no ar"
                    message="Quando uma nova investigação começar, ela aparece aqui."
                />
            {/if}
        </Section>

        <Section title="Enquetes" styles="public-polls-original container-page order-5 mt-10 mb-12">
            {#if poll}
                <div class="grid gap-3">
                    <form
                        on:submit|preventDefault={submitMainVote}
                        class={[
                            "public-default-gradient w-full rounded-md bg-gradient-blue-cerulean-glow px-4 py-5 sm:px-6 lg:px-8",
                            poll.has_voted && "pointer-events-none opacity-50",
                        ]}
                    >
                        <h2 class={["text-center font-noto-sans text-xl font-extrabold uppercase italic text-orange-morning lg:text-2xl", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                            {poll.question}
                        </h2>
                        <div class="my-7 grid gap-5 sm:grid-cols-2 lg:my-12 xl:grid-cols-4">
                            {#each poll.options as option (option.uuid)}
                                <div class="grid min-w-0 grid-cols-[1.25rem_minmax(0,1fr)] gap-2">
                                    <input
                                        id={option.uuid}
                                        bind:group={mainSelectedOption}
                                        name="option"
                                        type="radio"
                                        value={option.uuid}
                                        class="mt-1 h-5 w-5 cursor-pointer accent-orange-citric"
                                    />
                                    <div class="min-w-0">
                                        <label for={option.uuid} title={option.option} class={["block max-w-full break-words font-noto-sans text-base font-bold uppercase italic leading-tight text-suspense-aurora sm:text-lg", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                            {option.option}
                                        </label>
                                        <div class="relative mt-2 flex h-3.5 w-full select-none items-center rounded-full bg-black px-2">
                                            <div
                                                class={[
                                                    "h-1.5 rounded-sm bg-orange-500",
                                                    optionPercent(poll, option) > 0 ? "min-w-8" : "",
                                                ]}
                                                style={`width: ${optionPercent(poll, option)}%`}
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 md:flex-nowrap">
                            <AuthGuard
                                {oauth}
                                compact
                                buttonLabel="Entre para votar"
                                filters="filter-blue-night"
                                containerClass="order-1 md:order-3"
                                buttonClass="text-blue-night"
                            >
                                <Button
                                    aria-label="votar"
                                    type="submit"
                                    variant="primary"
                                    shape="pill"
                                    class="order-1 md:order-3"
                                    loading={mainVoting}
                                    disabled={!mainSelectedOption || poll.has_voted}
                                >
                                    {mainSelectedOption ? "Confirmar seu voto" : "Votar"}
                                </Button>
                            </AuthGuard>
                            <div class="order-2 font-noto-sans font-bold uppercase italic md:order-1">
                                <span class={["text-3xl font-extrabold text-suspense-aurora", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                    {poll.total_votes}
                                </span>
                                <span class={["text-sm text-orange-morning", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                    Votos
                                </span>
                            </div>
                            <span class={["order-3 w-full font-noto-sans text-sm font-normal uppercase italic text-orange-morning md:order-2 md:ml-auto md:w-auto", themeClass("text", "blue-night/70", { theme: "light" })]}>
                                ** Vote com sabedoria, após confirmar, o voto não pode ser mudado e você não pode votar novamente**
                            </span>
                        </div>
                    </form>

                    {#if pollList.length > 0}
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                            {#each pollList as item (item.uuid)}
                                <button
                                    type="button"
                                    class={[
                                        "min-h-20 cursor-pointer rounded-md bg-blue-ocean px-4 py-4 text-left font-noto-sans text-base font-extrabold uppercase italic leading-tight text-suspense-aurora hover:bg-blue-cerulean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                        themeClass("bg", "orange-morning", { theme: "light" }),
                                        themeClass("text", "blue-night", { fixed: true, theme: "light" }),
                                        publicAnimations.cardInteractive,
                                        item.has_voted && "pointer-events-none opacity-50",
                                    ]}
                                    disabled={item.has_voted}
                                    on:click={() => openPollModal(item)}
                                >
                                    <span class="line-clamp-2">{item.question}</span>
                                </button>
                            {/each}
                        </div>
                    {/if}
                </div>
            {:else}
                <MinimalEmptyState
                    title="Nenhuma enquete ativa"
                    message="Quando uma votação entrar no ar, ela aparece neste bloco."
                />
            {/if}
        </Section>

        <Modal bind:this={pollModalRef} title="Enquete" size="sm">
            {#if selectedPoll}
                <form on:submit|preventDefault={submitVote}>
                    <h2 class="font-noto-sans text-xl font-extrabold uppercase italic leading-tight text-blue-night">
                        {selectedPoll.question}
                    </h2>
                    <div class="mt-5 grid gap-2.5">
                        {#each selectedPoll.options as option (option.uuid)}
                            <label
                                class={[
                                    "grid cursor-pointer grid-cols-[1.35rem_1fr] gap-2.5 rounded-md border-2 bg-suspense-aurora px-3 py-2.5 text-blue-night hover:border-orange-amber hover:bg-orange-amber/5",
                                    publicAnimations.buttonInteractive,
                                    selectedOption === option.uuid ? "border-orange-amber shadow-[inset_0_0_0_1px_theme(colors.orange-amber)]" : "border-blue-ocean/25",
                                ]}
                            >
                                <input
                                    bind:group={selectedOption}
                                    type="radio"
                                    name="option"
                                    value={option.uuid}
                                    class="mt-0.5 size-5 accent-orange-amber"
                                />
                                <span class="min-w-0 text-center">
                                    <span class="block break-words font-noto-sans text-base font-extrabold uppercase italic leading-tight">{option.option}</span>
                                    <span class="mt-2 flex h-2 min-w-30 overflow-hidden rounded-full bg-blue-night/15">
                                        <span
                                            class={[
                                                "rounded-full bg-orange-amber",
                                                optionPercent(selectedPoll, option) > 0 ? "min-w-8" : "",
                                            ]}
                                            style={`width: ${optionPercent(selectedPoll, option)}%`}
                                        ></span>
                                    </span>
                                    <span class="mt-1 block text-right font-noto-sans text-[0.65rem] font-extrabold uppercase italic text-blue-ocean">
                                        {option.votes} votos
                                    </span>
                                </span>
                            </label>
                        {/each}
                    </div>
                    <div class="mt-6 border-t border-blue-night/10 pt-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="font-noto-sans font-bold uppercase italic">
                                <span class="text-3xl font-extrabold text-blue-night">{selectedPoll.total_votes}</span>
                                <span class="text-xs text-blue-ocean">Votos</span>
                            </div>
                            <AuthGuard {oauth} compact buttonLabel="Entre para votar" filters="filter-blue-night" buttonClass="text-blue-night">
                                <Button type="submit" variant="primary" shape="pill" loading={voting} disabled={!selectedOption || selectedPoll.has_voted}>
                                    {selectedOption ? "Confirmar seu voto" : "Votar"}
                                </Button>
                            </AuthGuard>
                        </div>
                        <p class="mt-4 font-noto-sans text-[0.7rem] font-bold uppercase italic leading-relaxed text-blue-ocean">
                            Vote com sabedoria. Apos confirmar, o voto nao pode ser mudado e voce nao podera votar novamente.
                        </p>
                    </div>
                </form>
            {/if}
        </Modal>
    </main>
</Layout>
