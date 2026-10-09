<script>
    import { router } from "@inertiajs/svelte";

    import { Button, EmptyState, GridList, IconButton, Modal, Offcanvas, Section } from "@/lib/components/private";
    import EnigmaGameForm from "../form/EnigmaGameForm.svelte";
    import { enigmagamePermissions, resolvePlaceholderImage, resolveStatusBackground } from "@/lib/utils";

    export let title = "Enigma Otaku";
    export let enigmagames = null;

    const can = enigmagamePermissions();

    let offcanvasRef;
    let interactionsModalRef;
    let enigmagameSelected = null;
    let interactionsEnigmaGameUuid = null;
    let enigmaView = "all";

    $: allList = (enigmagames?.data ?? []).filter((item) => !["ended", "inactive"].includes(item.status));
    $: solvedList = allList.filter((item) => isSolved(item));
    $: unresolvedList = allList.filter((item) => !isSolved(item));
    $: list = enigmaView === "solved" ? solvedList : unresolvedList;
    $: interactionsEnigmaGame = list.find((item) => item.uuid === interactionsEnigmaGameUuid);
    $: offcanvasTitle = enigmagameSelected ? "Atualizar enigma" : "Cadastrar enigma";
    $: interactionsModalTitle = interactionsEnigmaGame?.title ?? "Interações";
    $: hasUnsolvedEnigmaGame = allList.some((item) => !["ended", "inactive"].includes(item.status) && !isSolved(item));

    $: actions = [
        {
            title: enigmaView === "solved" ? "Todos os enigmas" : "Enigmas resolvidos",
            icon: "/svg/interactions.svg",
            permission: true,
            background: enigmaView === "solved" ? "bg-blue-ocean" : "bg-blue-skywave",
            textColor: "text-suspense-aurora",
            filter: "filter-suspense-aurora",
            onClick: () => enigmaView = enigmaView === "solved" ? "all" : "solved",
        },
        {
            title: "Criar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                enigmagameSelected = null;
                offcanvasRef.open();
            },
        },
    ];

    function edit(item) {
        enigmagameSelected = item;
        offcanvasRef.open();
    }

    function showInteractions(item) {
        interactionsEnigmaGameUuid = item.uuid;

        router.reload({
            only: ["enigmagames"],
            preserveScroll: true,
            onSuccess: () => interactionsModalRef.open(),
        });
    }

    function publish(item) {
        router.patch(`/panel/media/enigmagame/${item.uuid}/publish`, {}, { preserveScroll: true });
    }

    function deactivate(item) {
        router.patch(`/panel/media/enigmagame/${item.uuid}/deactivate`, {}, {
            preserveScroll: true,
            only: ["enigmagames", "flash"],
        });
    }

    function judge(interaction, result) {
        router.patch(`/panel/media/enigmagame/interaction/${interaction.uuid}/respond`, {
            result,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                if (!interactionsEnigmaGame) return;

                interactionsEnigmaGame.interactions = interactionsEnigmaGame.interactions.map((item) => item.uuid === interaction.uuid
                    ? { ...item, admin_response: null, result }
                    : item
                );

                if (result === "correct") {
                    interactionsModalRef.close();
                }
            },
        });
    }

    function cardStatusBackground(item) {
        if (isSolved(item)) {
            return "bg-blue-skywave";
        }

        if (item.status === "draft") {
            return "bg-green-mint";
        }

        if (item.status === "active") {
            return "bg-purple-mystic";
        }

        return resolveStatusBackground({ ...item, status: "published" }, { useValidity: false });
    }

    function typeLabel(type) {
        return type === "final_answer" ? "Resposta definitiva" : "Pergunta";
    }

    function resultLabel(interaction) {
        if (interaction.type === "question") {
            return {
                yes: "Sim",
                no: "Não",
                banal: "Banal",
            }[interaction.result] ?? null;
        }

        return {
            correct: "Acertou",
            incorrect: "Errou",
        }[interaction.result] ?? null;
    }

    function resultClass(interaction) {
        if (interaction.type === "question") {
            return {
                yes: "bg-green-forest text-suspense-aurora",
                no: "bg-red-crimson text-suspense-aurora",
                banal: "bg-neutral-gray text-suspense-aurora",
            }[interaction.result] ?? "bg-blue-night/10 text-blue-ocean";
        }

        return interaction.result === "correct" ? "bg-green-forest text-suspense-aurora" : "bg-red-crimson text-suspense-aurora";
    }

    function isSolved(item) {
        return item.interactions?.some((interaction) => interaction.type === "final_answer" && interaction.result === "correct");
    }
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <EnigmaGameForm {enigmagameSelected} {close} publishBlocked={!enigmagameSelected && hasUnsolvedEnigmaGame} />
    </div>
</Offcanvas>

<Modal bind:this={interactionsModalRef} title={interactionsModalTitle} size="xl">
    <div slot="content">
        {#if interactionsEnigmaGame?.interactions?.length}
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {#each interactionsEnigmaGame.interactions as interaction (interaction.uuid)}
                    <article class="flex h-full flex-col overflow-hidden rounded-md border border-blue-night/10 bg-suspense-aurora shadow-sm">
                        <div class="flex items-start gap-3 px-4 pt-3">
                            <div class="size-11 shrink-0 overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent">
                                <img
                                    src={resolvePlaceholderImage(interaction.participant?.avatar, "avatar", interaction.participant?.gender)}
                                    alt=""
                                    aria-hidden="true"
                                    class="h-full w-full scale-125 object-cover object-top"
                                    loading="lazy"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex min-w-0 flex-wrap items-center gap-2 font-noto-sans">
                                    <h3 class="truncate text-sm font-black uppercase italic leading-tight text-blue-night">
                                        {interaction.participant?.name ?? "Ouvinte"}
                                    </h3>
                                    <span class="rounded-sm bg-blue-ocean px-2 py-0.5 text-[0.65rem] font-black uppercase italic leading-none text-suspense-aurora">
                                        {typeLabel(interaction.type)}
                                    </span>
                                    {#if interaction.result}
                                        <span class={["rounded-sm px-2 py-0.5 text-[0.65rem] font-black uppercase italic leading-none", resultClass(interaction)]}>
                                            {resultLabel(interaction)}
                                        </span>
                                    {/if}
                                </div>
                                <p class="mt-1 font-noto-sans text-xs font-bold uppercase italic text-neutral-gray">
                                    {interaction.created_at}
                                </p>
                            </div>
                        </div>

                        {#if can.respond && !interaction.result}
                            <div class="px-4 pt-3">
                                {#if interaction.type === "final_answer"}
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            variant="success"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            on:click={() => judge(interaction, "correct")}
                                        >
                                            Acertou
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="danger"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            on:click={() => judge(interaction, "incorrect")}
                                        >
                                            Errou
                                        </Button>
                                    </div>
                                {:else}
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            variant="success"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            on:click={() => judge(interaction, "yes")}
                                        >
                                            Sim
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="danger"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            on:click={() => judge(interaction, "no")}
                                        >
                                            Não
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="dark"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            on:click={() => judge(interaction, "banal")}
                                        >
                                            Banal
                                        </Button>
                                    </div>
                                {/if}
                            </div>
                        {/if}

                        <div class="px-4 py-3">
                            <p class="whitespace-pre-line font-noto-sans text-sm font-normal leading-relaxed text-blue-night">
                                {interaction.content}
                            </p>
                        </div>

                        {#if interaction.admin_response}
                            <div class="flex flex-1 flex-col border-t border-blue-night/10 bg-blue-night/5 px-4 py-2.5">
                                <div class="mb-2 flex items-start gap-2">
                                    <div class="size-8 shrink-0 overflow-hidden rounded-full border-2 border-neutral-gray/35 bg-transparent">
                                        <img
                                            src={resolvePlaceholderImage(interaction.responder?.avatar, "avatar", interaction.responder?.gender)}
                                            alt=""
                                            aria-hidden="true"
                                            class="h-full w-full scale-125 object-cover object-top"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div class="min-w-0 font-noto-sans uppercase italic">
                                        <div class="flex min-w-0 flex-wrap items-center gap-1.5">
                                            <div class="truncate text-xs font-black leading-none text-blue-night">
                                                {interaction.responder?.nickname ?? interaction.responder?.name ?? "Equipe Akiba"}
                                            </div>
                                            <span class="rounded-sm bg-blue-night/10 px-1.5 py-0.5 text-[0.6rem] font-black leading-none text-blue-ocean">
                                                Resposta
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[0.65rem] font-bold leading-none text-neutral-gray">
                                            {interaction.responded_at}
                                        </div>
                                    </div>
                                </div>
                                <p class="whitespace-pre-line font-noto-sans text-sm font-normal leading-snug text-blue-night">
                                    {interaction.admin_response}
                                </p>
                            </div>
                        {/if}
                    </article>
                {/each}
            </div>
        {:else}
            <EmptyState title="Nenhuma interação" description="As perguntas e respostas definitivas aparecerão aqui." />
        {/if}
    </div>
</Modal>

{#if enigmagames}
    <Section {title} {actions}>
        {#if list.length > 0}
            <GridList as="div" preset="content">
                {#each list as item (item.uuid)}
                    <article class="relative flex min-h-44 flex-col rounded-md bg-blue-ocean">
                        <div class="flex-1 overflow-hidden rounded-t-md p-3">
                            <h3 class="line-clamp-2 font-noto-sans text-base font-normal uppercase text-suspense-aurora">
                                {item.title}
                            </h3>
                        </div>

                        <div class={`grid grid-cols-[1fr_auto] items-center gap-2 rounded-b-md px-2 py-1 ${cardStatusBackground(item)}`}>
                            <span class="flex min-w-0 items-center gap-1 font-noto-sans text-xs font-extrabold uppercase italic text-suspense-aurora">
                                <span
                                    class="size-6 shrink-0 bg-suspense-aurora"
                                    style="-webkit-mask: url('/svg/interactions.svg') center / contain no-repeat; mask: url('/svg/interactions.svg') center / contain no-repeat;"
                                    aria-hidden="true"
                                ></span>
                                <span class="truncate">{item.interactions?.length ?? 0}</span>
                            </span>
                            <div class="flex items-center justify-end gap-1">
                                {#if item.status !== "draft"}
                                    <IconButton variant="eye" icon="/svg/interactions.svg" iconClass="size-5" label="Interações" size="sm" surface="dark" on:click={() => showInteractions(item)} />
                                {/if}
                                {#if can.update}
                                    <IconButton variant="edit" label="Atualizar" size="sm" surface="dark" on:click={() => edit(item)} />
                                {/if}
                                {#if can.delete && item.status !== "inactive" && item.status !== "active"}
                                    <IconButton variant="trash" label="Inativar" size="sm" surface="dark" on:click={() => deactivate(item)} />
                                {/if}
                            </div>
                        </div>
                    </article>
                {/each}
            </GridList>
        {:else}
            <EmptyState
                title="Nenhum enigma encontrado"
                description="Os enigmas cadastrados aparecerão aqui."
            />
        {/if}
    </Section>
{/if}
