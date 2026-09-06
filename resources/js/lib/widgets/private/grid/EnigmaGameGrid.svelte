<script>
    import { router } from "@inertiajs/svelte";

    import { Button, EmptyState, GridList, IconButton, Modal, Offcanvas, Section, TextArea } from "@/lib/components/private";
    import EnigmaGameForm from "../form/EnigmaGameForm.svelte";
    import { enigmagamePermissions, resolvePlaceholderImage, resolveStatusBackground } from "@/lib/utils";

    export let title = "Enigma Game";
    export let enigmagames = null;

    const can = enigmagamePermissions();

    let offcanvasRef;
    let interactionsModalRef;
    let enigmagameSelected = null;
    let interactionsEnigmaGameUuid = null;
    let responseContent = {};

    $: list = (enigmagames?.data ?? []).filter((item) => item.status !== "inactive");
    $: interactionsEnigmaGame = list.find((item) => item.uuid === interactionsEnigmaGameUuid);
    $: offcanvasTitle = enigmagameSelected ? "Atualizar enigma" : "Cadastrar enigma";
    $: interactionsModalTitle = interactionsEnigmaGame?.title ?? "Interações";
    $: hasUnsolvedEnigmaGame = list.some((item) => !["ended", "inactive"].includes(item.status) && !isSolved(item));

    let actions = [
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

    function finish(item) {
        if (!confirm("Encerrar este enigma? Ele vai sair do site.")) return;

        router.patch(`/panel/media/enigmagame/${item.uuid}/finish`, {}, {
            preserveScroll: true,
            only: ["enigmagames", "flash"],
        });
    }

    function respond(interaction) {
        const adminResponse = responseContent[interaction.uuid] ?? "";

        router.patch(`/panel/media/enigmagame/interaction/${interaction.uuid}/respond`, {
            admin_response: adminResponse,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                responseContent[interaction.uuid] = "";

                if (!interactionsEnigmaGame) return;

                interactionsEnigmaGame.interactions = interactionsEnigmaGame.interactions.map((item) => item.uuid === interaction.uuid
                    ? { ...item, admin_response: adminResponse }
                    : item
                );
            },
        });
    }

    function judge(interaction, result) {
        const adminResponse = responseContent[interaction.uuid] ?? "";

        router.patch(`/panel/media/enigmagame/interaction/${interaction.uuid}/respond`, {
            admin_response: adminResponse,
            result,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                responseContent[interaction.uuid] = "";

                if (!interactionsEnigmaGame) return;

                interactionsEnigmaGame.interactions = interactionsEnigmaGame.interactions.map((item) => item.uuid === interaction.uuid
                    ? { ...item, admin_response: adminResponse, result }
                    : item
                );
            },
        });
    }

    function cardStatusBackground(item) {
        if (isSolved(item)) {
            return "bg-purple-mystic";
        }

        if (item.status === "draft") {
            return "bg-green-mint";
        }

        return resolveStatusBackground({ ...item, status: "published" }, { useValidity: false });
    }

    function typeLabel(type) {
        return type === "final_answer" ? "Resposta definitiva" : "Pergunta";
    }

    function isSolved(item) {
        return item.interactions?.some((interaction) => interaction.type === "final_answer" && interaction.result === "correct");
    }
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <EnigmaGameForm {enigmagameSelected} {close} hidePublish={!enigmagameSelected && hasUnsolvedEnigmaGame} />
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
                                        <span class={[
                                            "rounded-sm px-2 py-0.5 text-[0.65rem] font-black uppercase italic leading-none",
                                            interaction.result === "correct" ? "bg-green-forest text-blue-marinho" : "bg-red-crimson text-suspense-aurora",
                                        ]}>
                                            {interaction.result === "correct" ? "Acertou" : "Errou"}
                                        </span>
                                    {/if}
                                </div>
                                <p class="mt-1 font-noto-sans text-xs font-bold uppercase italic text-neutral-gray">
                                    {interaction.created_at}
                                </p>
                            </div>
                        </div>

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
                        {:else if can.respond && !interaction.result}
                            <div class="flex flex-1 flex-col border-t border-blue-night/10 bg-blue-night/5 px-4 py-3">
                                <div class="grid flex-1 content-start gap-2">
                                    <TextArea
                                        variant="profile"
                                        class="min-h-18 text-sm"
                                        bind:value={responseContent[interaction.uuid]}
                                        placeholder={interaction.type === "final_answer" ? "Comentário opcional" : "Sua resposta"}
                                    />
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
                                        <Button
                                            type="button"
                                            variant="accent"
                                            size="sm"
                                            shape="pill"
                                            class="w-fit px-4 py-1 text-xs"
                                            disabled={!responseContent[interaction.uuid]}
                                            on:click={() => respond(interaction)}
                                        >
                                            Responder
                                        </Button>
                                    {/if}
                                </div>
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
                    <article class={["relative flex min-h-44 flex-col rounded-md bg-blue-ocean", item.status === "ended" && "opacity-55"]}>
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
                                {#if can.delete && item.status === "active"}
                                    <IconButton
                                        variant="close"
                                        icon="/svg/finish.svg"
                                        label="Encerrar enigma"
                                        size="sm"
                                        surface="dark"
                                        tone="accent"
                                        on:click={() => finish(item)}
                                    />
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
