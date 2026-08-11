<script>
    import { router } from "@inertiajs/svelte";

    import {
        Badge,
        Button,
        EmptyState,
        GridList,
        IconButton,
        Modal,
        Section,
    } from "@/lib/components/private";
    import { hasPermission, resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let items = [];

    const types = [
        { value: "all", label: "Todos" },
        { value: "user", label: "Usuários" },
        { value: "program", label: "Programas" },
        { value: "post", label: "Conteúdos" },
        { value: "podcast", label: "Podcasts" },
        { value: "poll", label: "Enquetes" },
        { value: "task", label: "Tarefas" },
        { value: "repository", label: "Marketing" },
    ];

    const canRestore = hasPermission("trash.restore");
    const canDelete = hasPermission("trash.delete");

    let selectedType = "all";
    let restoring = null;
    let deleting = false;
    let selectedForDeletion = null;
    let deleteModal;

    $: visibleItems = items.filter((item) => (
        selectedType === "all" || item.type === selectedType
    ));

    function placeholder(item) {
        if (item.type === "user") {
            return resolvePlaceholderImage(item.image, "avatar", item.gender);
        }

        if (item.type === "program") {
            return resolvePlaceholderImage(item.image, "program");
        }

        return resolvePlaceholderImage(item.image, "placeholder");
    }

    const reactivate = (item) => {
        restoring = item.uuid;

        router.patch(`/panel/trash/${item.type}/${item.uuid}/reactivate`, {}, {
            preserveScroll: true,
            onFinish: () => {
                restoring = null;
            },
        });
    };

    const confirmDeletion = (item) => {
        selectedForDeletion = item;
        deleteModal.open();
    };

    const cascadeWarning = (type) => ({
        user: "Também serão excluídos os vínculos de cargos e os programas, conteúdos, podcasts, enquetes, tarefas, atividades e calendários pertencentes a este usuário.",
        program: "Também serão excluídos os horários, agendamentos, transmissões, picos de audiência e pedidos musicais vinculados a este programa.",
        post: "Também serão excluídas as reações, referências, categorias e opiniões vinculadas a este conteúdo.",
        poll: "Também serão excluídas as opções e todos os votos vinculados a esta enquete.",
    })[type] ?? null;

    const destroy = (close) => {
        if (!selectedForDeletion || deleting) return;

        deleting = true;

        router.delete(`/panel/trash/${selectedForDeletion.type}/${selectedForDeletion.uuid}`, {
            preserveScroll: true,
            onSuccess: () => {
                close();
                selectedForDeletion = null;
            },
            onFinish: () => {
                deleting = false;
            },
        });
    };
</script>

<Modal bind:this={deleteModal} title="Excluir definitivamente">
    <div slot="content" let:close class="font-noto-sans">
        <div class="mb-5 rounded-md border border-red-crimson/30 bg-red-crimson/10 p-4 text-sm text-blue-marinho">
            <p class="font-extrabold uppercase">Esta ação não pode ser desfeita.</p>
            {#if selectedForDeletion && cascadeWarning(selectedForDeletion.type)}
                <p class="mt-2">
                    {cascadeWarning(selectedForDeletion.type)}
                </p>
            {/if}
        </div>

        {#if selectedForDeletion}
            <p class="mb-5 text-center text-sm text-blue-marinho">
                Deseja excluir permanentemente
                <strong>{selectedForDeletion.title}</strong>?
            </p>
        {/if}

        <div class="flex justify-end gap-2">
            <Button variant="secondary" class="!text-sm" on:click={close} disabled={deleting}>
                Cancelar
            </Button>
            <Button variant="danger" class="!text-sm" loading={deleting} on:click={() => destroy(close)}>
                Excluir definitivamente
            </Button>
        </div>
    </div>
</Modal>

<Section {title}>
    <div class="mb-6 flex flex-col gap-4">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar por tipo">
            {#each types as type}
                <Button
                    size="sm"
                    shape="pill"
                    variant={selectedType === type.value ? "accent" : "secondary"}
                    aria-pressed={selectedType === type.value}
                    on:click={() => (selectedType = type.value)}
                >
                    {type.label}
                </Button>
            {/each}
        </div>

    </div>

    {#if visibleItems.length}
        <GridList preset="wide">
            {#each visibleItems as item (`${item.type}-${item.uuid}`)}
                <li class="flex min-w-0 items-center gap-4 rounded-md bg-blue-ocean p-3">
                    <img
                        src={placeholder(item)}
                        alt=""
                        aria-hidden="true"
                        class="size-16 shrink-0 rounded-md bg-blue-marinho object-cover object-top"
                        loading="lazy"
                    />

                    <div class="min-w-0 flex-1">
                        <Badge variant="dark" class="mb-1 px-2">
                            {item.type_label}
                        </Badge>
                        <h2 class="truncate font-noto-sans text-sm font-extrabold uppercase text-suspense-aurora">
                            {item.title}
                        </h2>
                        {#if item.subtitle}
                            <p class="mt-0.5 truncate font-noto-sans text-xs text-suspense-aurora/60">
                                {item.subtitle}
                            </p>
                        {/if}
                    </div>

                    <div class="flex shrink-0 gap-2">
                        {#if canRestore}
                            <IconButton
                                icon="/svg/return.svg"
                                label={`Reativar ${item.title}`}
                                tone="accent"
                                surface="dark"
                                disabled={restoring === item.uuid}
                                on:click={() => reactivate(item)}
                            />
                        {/if}
                        {#if canDelete}
                            <IconButton
                                variant="trash"
                                label={`Excluir definitivamente ${item.title}`}
                                surface="dark"
                                on:click={() => confirmDeletion(item)}
                            />
                        {/if}
                    </div>
                </li>
            {/each}
        </GridList>
    {:else}
        <EmptyState
            title={items.length ? "Nenhum item encontrado" : "Nenhum item da lixeira"}
            description={items.length
                ? "Tente outro filtro ou termo de busca."
                : "Quando algo for desativado, aparecerá aqui para ser reativado."}
            icon="/svg/return.svg"
        />
    {/if}
</Section>
