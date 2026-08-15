<script>
    import { Link, page, router } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { AuthGuard, Button, EditorialTitle, Modal, Section } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { ListenerGalleryGrid } from "@/lib/widgets/public";
    import { publicAnimations } from "@/lib/constants";
    import { resolvePlaceholderImage } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, events, listenerGallery, polls, latestPoll } = $page.props);
    $: pageUrl = $page.url;
    $: eventList = Array.isArray(events) ? events : events?.data ?? [];
    $: poll = latestPoll?.data ?? null;
    $: pollList = (Array.isArray(polls) ? polls : polls?.data ?? []).filter((item) => item.uuid !== poll?.uuid);
    $: selectedPoll = selectedPollUuid ? [poll, ...pollList].find((item) => item?.uuid === selectedPollUuid) : null;

    const resolveEventDate = (event) => event.metadata?.dates ?? "";
    const resolveEventPlace = (event) => event.metadata?.address ?? "";
    const optionPercent = (pollItem, option) => pollItem?.total_votes ? (option.votes / pollItem.total_votes) * 100 : 0;

    let pollModalRef;
    let mainSelectedOption = null;
    let selectedPollUuid = null;
    let selectedOption = null;
    let voting = false;
    let mainVoting = false;

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
</script>

<Meta meta={{ title: "Mídias" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <h1 class="sr-only">Mídias</h1>
    <main>
        <div class="bg-blue-night pt-10">
            <EditorialTitle title="Super conteúdos" compact padding="py-6" spacer />
        </div>

        {#if eventList.length > 0}
            <Section title="Eventos" styles="container-page mt-10 mb-12">
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                    {#each eventList as item (item.uuid)}
                        <Link href={item.href} class={["group overflow-hidden rounded-md bg-orange-citric text-blue-night focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric", publicAnimations.cardInteractive]}>
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
            </Section>
        {/if}

        <ListenerGalleryGrid {listenerGallery} styles="container-page mb-12" />

        {#if poll}
            <Section title="Enquetes" styles="container-page mt-10 mb-12">
                <div class="grid gap-3">
                    <form
                        on:submit|preventDefault={submitMainVote}
                        class={[
                            "w-full rounded-md bg-gradient-blue-cerulean-glow p-4",
                            poll.has_voted && "pointer-events-none opacity-50",
                        ]}
                    >
                        <h2 class="text-center font-noto-sans text-xl font-extrabold uppercase italic text-orange-morning lg:text-2xl">
                            {poll.question}
                        </h2>
                        <div class="mt-5 mb-10 flex flex-col justify-center gap-5 lg:my-13 lg:flex-row lg:gap-8">
                            {#each poll.options as option (option.uuid)}
                                <div class="flex w-full gap-2 lg:w-44">
                                    <input
                                        id={option.uuid}
                                        bind:group={mainSelectedOption}
                                        name="option"
                                        type="radio"
                                        value={option.uuid}
                                        class="mt-2 h-5 w-5 shrink-0 cursor-pointer"
                                    />
                                    <div class="min-w-0">
                                        <label for={option.uuid} title={option.option} class="fonto-noto-sans block max-w-full break-words text-lg font-bold uppercase italic leading-5 text-suspense-aurora">
                                            {option.option}
                                        </label>
                                        <div class="relative mt-1 flex h-3.5 min-w-30 w-full select-none items-center rounded-full bg-black px-2">
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
                                <span class="text-3xl font-extrabold text-suspense-aurora">
                                    {poll.total_votes}
                                </span>
                                <span class="text-sm text-orange-morning">
                                    Votos
                                </span>
                            </div>
                            <span class="order-3 w-full font-noto-sans text-sm font-normal uppercase italic text-orange-morning md:order-2 md:ml-auto md:w-auto">
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
                                        "min-h-20 cursor-pointer rounded-md bg-blue-ocean px-4 py-4 text-left font-noto-sans text-base font-extrabold uppercase italic leading-tight text-suspense-aurora hover:bg-blue-cerulean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric",
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
            </Section>
        {/if}

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
                                    "grid cursor-pointer grid-cols-[1.35rem_1fr] gap-2.5 rounded-md border-2 bg-suspense-aurora px-3 py-2.5 text-blue-night hover:border-orange-citric hover:bg-orange-citric/5",
                                    publicAnimations.buttonInteractive,
                                    selectedOption === option.uuid ? "border-orange-citric shadow-[inset_0_0_0_1px_theme(colors.orange-citric)]" : "border-blue-ocean/25",
                                ]}
                            >
                                <input
                                    bind:group={selectedOption}
                                    type="radio"
                                    name="option"
                                    value={option.uuid}
                                    class="mt-0.5 size-5 accent-orange-citric"
                                />
                                <span class="min-w-0 text-center">
                                    <span class="block break-words font-noto-sans text-base font-extrabold uppercase italic leading-tight">{option.option}</span>
                                    <span class="mt-2 flex h-2 min-w-30 overflow-hidden rounded-full bg-blue-night/15">
                                        <span
                                            class={[
                                                "rounded-full bg-orange-citric",
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
