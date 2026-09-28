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

    let pollModalRef;
    let enigmaRulesModalRef;
    let mainSelectedOption = null;
    let selectedPollUuid = null;
    let selectedOption = null;
    let voting = false;
    let mainVoting = false;
    let enigmagameContent = "";
    let enigmagameSubmitting = null;
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

    $: answeredEnigmaInteractions = (enigmagame?.data?.interactions ?? [])
        .filter((interaction) => interaction.type === "question" && (interaction.admin_response || interaction.result))
        .slice(0, 12);

    function enigmaResultLabel(interaction) {
        return {
            yes: "Sim",
            no: "Não",
            banal: "Banal",
        }[interaction.result] ?? "Banal";
    }

    function enigmaResultClass(interaction) {
        return {
            yes: "bg-green-mint text-suspense-aurora",
            no: "bg-red-crimson text-suspense-aurora",
            banal: "bg-neutral-gray text-suspense-aurora",
        }[interaction.result] ?? "bg-neutral-gray text-suspense-aurora";
    }

    function enigmaStatusMessage(game) {
        if (game.participation?.has_submitted_final_answer) return "Sua resposta definitiva ja foi enviada.";
        if (game.solved) return "Este enigma ja foi resolvido.";
        if (!game.participation?.can_interact && game.participation?.next_interaction_at) {
            return `Aguarde ate ${game.participation.next_interaction_at} para interagir novamente.`;
        }

        return null;
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

        <Section title="Enigma Otaku" styles="container-page order-2 mt-10 mb-12">
            {#if enigmagame?.data}
                <div
                    class="grid overflow-hidden rounded-md bg-blue-night px-5 py-6 text-suspense-aurora [[data-public-theme=light]_&]:bg-[#d7dce3] lg:grid-cols-[minmax(0,1.25fr)_1px_minmax(24rem,0.9fr)] lg:px-7 lg:py-6"
                    style="--color-neutral-white:#ffffff; --color-neutral-gray:#808080; --color-suspense-aurora:#fffaf3; --color-suspense-honeycream:#ffe8bf; --color-red-crimson:#ed3237; --color-orange-amber:#ff8000; --color-orange-citric:#ffaa35; --color-blue-ocean:#002080; --color-blue-night:#000014; --color-green-mint:#00a859;"
                >
                    <section class="flex min-h-[21rem] flex-col items-center justify-center px-2 py-8 text-center font-noto-sans uppercase italic lg:min-h-[24rem] lg:px-4">
                        <h3 class="mb-5 max-w-2xl text-xl font-extrabold leading-tight text-neutral-white sm:text-2xl [[data-public-theme=light]_&]:text-blue-night">
                            {enigmagame.data.title}
                        </h3>

                        <div class="flex w-full flex-1 items-center justify-center">
                            <img
                                src={enigmagame.data.content}
                                alt={`Imagem do enigma ${enigmagame.data.title}`}
                                class="max-h-[18rem] w-auto max-w-full object-contain [[data-public-theme=light]_&]:drop-shadow-[0_2px_5px_rgba(0,0,0,0.85)] sm:max-h-[24rem] lg:max-h-[28rem]"
                                loading="lazy"
                            />
                        </div>

                        {#if enigmagame.data.solved && enigmagame.data.solution}
                            <p class="mt-6 max-w-2xl text-lg font-normal normal-case leading-snug text-suspense-honeycream sm:text-2xl [[data-public-theme=light]_&]:text-blue-night">
                                {enigmagame.data.solution}
                            </p>
                        {/if}

                    </section>

                    <div class="hidden bg-orange-amber lg:block"></div>

                    <aside class="border-t border-orange-amber pt-5 lg:border-t-0 lg:pl-7 lg:pt-0">
                        <AuthGuard
                            {oauth}
                            compact
                            buttonLabel="Entre para participar"
                            filters="filter-blue-night"
                            buttonClass="text-blue-night"
                            reason="enigmagame"
                        >
                            <form class="grid gap-2" on:submit|preventDefault>
                                <div class="flex flex-wrap items-end justify-between gap-2 font-noto-sans text-xs font-black uppercase italic">
                                    <label for="enigmagame-interaction" class="text-orange-amber">
                                        Faça uma pergunta ou responda o enigma
                                    </label>
                                    <button
                                        type="button"
                                        class="cursor-pointer text-orange-amber underline decoration-orange-amber/40 underline-offset-4 transition-colors hover:text-orange-citric hover:decoration-orange-citric/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora"
                                        on:click={() => enigmaRulesModalRef.open()}
                                    >
                                        Como jogar?
                                    </button>
                                </div>

                                <input
                                    id="enigmagame-interaction"
                                    class="h-10 w-full rounded-md border border-transparent bg-suspense-aurora px-4 font-noto-sans text-sm font-normal not-italic text-blue-night outline-none ring-0 placeholder:text-blue-night/45 focus:border-transparent focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                    bind:value={enigmagameContent}
                                    placeholder=""
                                    disabled={!enigmagame.data.participation?.can_interact || enigmagameSubmitting}
                                    required
                                />

                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <Button
                                        type="button"
                                        variant="accent"
                                        size="sm"
                                        class="min-h-7 rounded-sm px-3 py-1 text-xs"
                                        loading={enigmagameSubmitting === "final_answer"}
                                        disabled={!enigmagameContent.trim() || enigmagameSubmitting || !enigmagame.data.participation?.can_interact}
                                        on:click={() => submitEnigmaGameInteraction("final_answer")}
                                    >
                                        Enviar resposta
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="accent"
                                        size="sm"
                                        class="min-h-7 rounded-sm px-3 py-1 text-xs"
                                        loading={enigmagameSubmitting === "question"}
                                        disabled={!enigmagameContent.trim() || enigmagameSubmitting || !enigmagame.data.participation?.can_interact}
                                        on:click={() => submitEnigmaGameInteraction("question")}
                                    >
                                        Enviar pergunta
                                    </Button>
                                </div>

                                {#if enigmaStatusMessage(enigmagame.data)}
                                    <p class="font-noto-sans text-xs font-bold text-suspense-aurora">
                                        {enigmaStatusMessage(enigmagame.data)}
                                    </p>
                                {/if}
                            </form>
                        </AuthGuard>

                        <div class="mt-7">
                            <div class="mb-3 grid grid-cols-[auto_1fr] items-center gap-3 font-noto-sans text-xs font-bold uppercase italic text-orange-amber">
                                <h4>Perguntas respondidas</h4>
                                <span class="h-px bg-orange-amber"></span>
                            </div>

                            {#if answeredEnigmaInteractions.length}
                                <div class="public-themed-scrollbar grid max-h-60 gap-2 overflow-y-auto pr-1">
                                    {#each answeredEnigmaInteractions as interaction (interaction.uuid)}
                                        <article class="grid grid-cols-[minmax(0,1fr)_4rem] overflow-hidden rounded-md font-noto-sans text-sm font-normal">
                                            <p class="break-words bg-suspense-honeycream px-3 py-2 text-blue-night">
                                                {interaction.content}
                                            </p>
                                            <span class={["grid place-items-center px-2 py-2 text-center font-bold uppercase italic", enigmaResultClass(interaction)]}>
                                                {enigmaResultLabel(interaction)}
                                            </span>
                                        </article>
                                    {/each}
                                </div>
                            {:else}
                                <p class="font-noto-sans text-sm font-normal text-suspense-aurora/60">
                                    Nenhuma pergunta respondida ainda.
                                </p>
                            {/if}
                        </div>
                    </aside>
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
                            "public-default-gradient w-full rounded-md bg-gradient-blue-ocean-cerulean px-4 py-5 sm:px-6 lg:px-8",
                            poll.has_voted && "pointer-events-none opacity-50",
                        ]}
                    >
                        <h2 class="text-center font-noto-sans text-xl font-extrabold uppercase italic text-[#fffaf3] lg:text-2xl">
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
                                        <label for={option.uuid} title={option.option} class="block max-w-full break-words font-noto-sans text-base font-bold uppercase italic leading-tight text-[#fffaf3] sm:text-lg">
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
                                    class="order-1 !text-[#fffaf3] md:order-3"
                                    loading={mainVoting}
                                    disabled={!mainSelectedOption || poll.has_voted}
                                >
                                    {mainSelectedOption ? "Confirmar seu voto" : "Votar"}
                                </Button>
                            </AuthGuard>
                            <div class="order-2 font-noto-sans font-bold uppercase italic md:order-1">
                                <span class="text-3xl font-extrabold text-[#fffaf3]">
                                    {poll.total_votes}
                                </span>
                                <span class="text-sm text-[#fffaf3]">
                                    Votos
                                </span>
                            </div>
                            <span class="order-3 w-full font-noto-sans text-sm font-normal uppercase italic text-[#fffaf3] md:order-2 md:ml-auto md:w-auto">
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
                                        "min-h-20 cursor-pointer rounded-md bg-blue-ocean px-4 py-4 text-left font-noto-sans text-base font-extrabold uppercase italic leading-tight text-[#fffaf3] hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber [[data-public-theme=light]_&]:bg-blue-skywave [[data-public-theme=night]_&]:bg-blue-ocean",
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

        <Modal bind:this={enigmaRulesModalRef} title="Como jogar" size="md">
            <div class="font-noto-sans text-blue-night">
                <p class="text-sm leading-relaxed">
                    Um título, uma imagem e uma frase estranha serão apresentados. Sua missão é descobrir, usando essas informações e as respostas da comunidade, qual é o anime.
                </p>

                <div class="mt-5 grid gap-4">
                    <section>
                        <h3 class="font-black uppercase italic text-orange-amber">1º passo — Faça perguntas</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            Faça perguntas que possam ser respondidas com:
                        </p>
                        <ul class="mt-3 grid gap-2 text-sm leading-relaxed">
                            <li><span class="font-black uppercase italic text-green-mint">Sim</span> — a resposta é positiva e ajuda a descobrir o anime.</li>
                            <li><span class="font-black uppercase italic text-red-crimson">Não</span> — a resposta é negativa, mas ajuda a descobrir o anime.</li>
                            <li><span class="font-black uppercase italic text-neutral-gray">Banal</span> — a resposta não ajuda a descobrir o anime.</li>
                        </ul>
                        <p class="mt-3 rounded-md bg-orange-morning px-3 py-2 text-sm font-bold text-blue-night">
                            Atenção: Você pode fazer apenas uma pergunta por dia.
                        </p>
                    </section>

                    <section>
                        <h3 class="font-black uppercase italic text-orange-amber">2º passo — Junte as pistas</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            Todas as perguntas respondidas ficarão no Quadro de Investigação, então confira ele, sua pergunta pode já ter sido feita por outro jogador.
                        </p>
                        <p class="mt-2 text-sm leading-relaxed">
                            Cada resposta ajuda a eliminar possibilidades e revelar novos detalhes sobre o anime. Nem toda informação precisa ser descoberta diretamente. Use as respostas para formular novas hipóteses.
                        </p>
                    </section>

                    <section class="rounded-md bg-blue-ocean px-4 py-3 text-suspense-aurora">
                        <h3 class="font-black uppercase italic text-orange-citric">Dica</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            Não tente descobrir uma sinopse inteira de uma vez. Faça perguntas específicas, como:
                        </p>
                        <ul class="mt-3 grid gap-1 text-sm font-bold italic">
                            <li>“O protagonista é humano?”</li>
                            <li>“O anime tem mais de uma temporada?”</li>
                            <li>“O poder dele está relacionado a sangue?”</li>
                        </ul>
                        <p class="mt-3 text-sm leading-relaxed">
                            Caso contrário sua pergunta será considerada “Banal”.
                        </p>
                    </section>

                    <section>
                        <h3 class="font-black uppercase italic text-orange-amber">3º passo — Resolva o mistério</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            Use todas as pistas para formular suas hipóteses. Acha que descobriu? Envie o título do anime.
                        </p>
                    </section>

                    <section>
                        <h3 class="font-black uppercase italic text-orange-amber">4º passo — Descubra a resposta</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            O Enigma Otaku fica disponível durante uma semana, mesmo que alguém descubra a resposta antes. Quando a semana terminar, a solução será revelada.
                        </p>
                        <p class="mt-3 rounded-md bg-orange-morning px-3 py-2 text-sm font-bold text-blue-night">
                            Mas existe uma recompensa especial: a primeira pessoa a acertar o anime poderá dizer que é o Maior Otaku do Brasil. Além de ganhar uma Badge no seu Perfil da Rede Akiba.
                        </p>
                    </section>
                </div>
            </div>
        </Modal>

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
