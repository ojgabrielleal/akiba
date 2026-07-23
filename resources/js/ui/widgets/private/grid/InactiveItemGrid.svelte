<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import {
        Badge,
        Button,
        EmptyState,
        GridList,
        IconButton,
        Section,
    } from "@/ui/components/private";
    import { hasPermission, resolvePlaceholderImage } from "@/utils";

    $: ({ inactive_items: items = [] } = $page.props);

    let selectedType = "all";
    let restoring = null;
    const canRestore = hasPermission("inactive.restore");

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

    $: visibleItems = items.filter((item) => (
        selectedType === "all" || item.type === selectedType
    ));

    const placeholder = (item) => {
        if (item.type === "user") {
            return resolvePlaceholderImage(item.image, "avatar", item.gender);
        }

        if (item.type === "program") {
            return resolvePlaceholderImage(item.image, "program");
        }

        return resolvePlaceholderImage(item.image, "placeholder");
    };

    const reactivate = (item) => {
        restoring = item.uuid;

        router.patch(`/panel/inactive/${item.type}/${item.uuid}/reactivate`, {}, {
            preserveScroll: true,
            onFinish: () => {
                restoring = null;
            },
        });
    };
</script>

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
                </li>
            {/each}
        </GridList>
    {:else}
        <EmptyState
            title={items.length ? "Nenhum item encontrado" : "Nenhum item desativado"}
            description={items.length
                ? "Tente outro filtro ou termo de busca."
                : "Quando algo for desativado, aparecerá aqui para ser reativado."}
            icon="/svg/return.svg"
        />
    {/if}
</Section>
