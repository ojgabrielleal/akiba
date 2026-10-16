<script>
    import Cookies from "js-cookie";
    import { router } from "@inertiajs/svelte";

    import { EmptyState, GridList, IconButton, Pagination, Section, Tooltip } from "@/lib/components/private";
    import { formatCompactNumber, postPermissions, resolveStatusBackground } from "@/lib/utils";

    export let title;
    export let posts = null;
    export let actions = [];
    export let showFilters = false;
    export let filters = {};

    const filterOptions = [
        { name: "post_type", label: "Tipo", options: [["all", "Todos"], ["post", "Matérias"], ["review", "Reviews"], ["event", "Eventos"]] },
        { name: "post_date", label: "Data", type: "date" },
        { name: "post_sort", label: "Ordenar por", options: [["all", "Mais recentes"], ["oldest", "Mais antigos"], ["views", "Mais visualizados"]] },
    ];
    let search = "";
    $: search = filters.search ?? "";

    function applyFilters(name, value) {
        const params = new URLSearchParams(window.location.search);
        const normalized = value.trim();
        if (!normalized || (name !== "search" && normalized === "all")) {
            params.delete(name);
        } else {
            params.set(name, normalized);
        }
        params.delete("page");
        params.delete("post_period");
        router.get(`${window.location.pathname}?${params.toString()}`, {}, {
            preserveScroll: true,
            preserveState: true,
            only: ["posts"],
        });
    }

    const can = postPermissions();
    $: canReviewOpinions = can.approve || can.publish;
    const reviewStatusBadges = [
        { status: "draft", label: "Rascunho", class: "bg-green-mint text-blue-marinho" },
        { status: "revision", label: "Avaliação", class: "bg-orange-amber text-blue-marinho" },
        { status: "published", label: "Publicada", class: "bg-blue-cerulean text-suspense-aurora" },
    ];

    function operation(module) {
        Cookies.set("akiba_post_module", module);
    }

    function requestDeactivate(post) {
        router.patch(`/panel/post/${post.uuid}/deactivate`, {}, {
            preserveScroll: true,
        });
    }

    function requestDelete(post) {
        if (!window.confirm(`Remover "${post.title}" da lista?`)) return;

        router.delete(`/panel/post/${post.uuid}`, {
            preserveScroll: true,
        });
    }

    function statusItem(item) {
        if (!canReviewOpinions && item.module === "review" && item.review_status) {
            return { ...item, status: item.review_status };
        }

        return item;
    }

    function canOpenOnSite(item) {
        return item.status === "published";
    }
</script>

{#if posts}
    <Section {title} {actions}>
        {#if showFilters}
            <div class="mb-5 grid gap-3 font-noto-sans sm:grid-cols-2 xl:grid-cols-[minmax(16rem,1fr)_auto_auto_auto]">
                <form on:submit|preventDefault={() => applyFilters("search", search)} class="min-w-0">
                    <label for="post-grid-search" class="mb-2 block text-xs font-extrabold uppercase italic text-suspense-aurora">Buscar</label>
                    <div class="flex gap-2">
                        <input id="post-grid-search" type="search" bind:value={search} maxlength="200" placeholder="Digite o título" class="h-10 min-w-0 flex-1 rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm text-suspense-aurora placeholder:text-suspense-aurora/60 focus:outline-none focus:ring-1 focus:ring-blue-skywave" />
                        <button type="submit" class="inline-flex h-10 cursor-pointer items-center justify-center rounded-md bg-orange-citric px-4 text-sm font-extrabold uppercase italic text-blue-night transition hover:bg-orange-citric/85 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-citric">Buscar</button>
                    </div>
                </form>
                {#each filterOptions as filter (filter.name)}
                    <label class="text-xs font-extrabold uppercase italic text-suspense-aurora">
                        {filter.label}
                        {#if filter.type === "date"}
                            <input type="date" name={filter.name} value={filters[filter.name] ?? ""} on:change={(event) => applyFilters(filter.name, event.currentTarget.value)} class="mt-2 block h-10 w-full min-w-0 cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora [color-scheme:dark] focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric" />
                        {:else}
                        <select name={filter.name} value={filters[filter.name] ?? "all"} on:change={(event) => applyFilters(filter.name, event.currentTarget.value)} class="mt-2 block h-10 w-full cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric">
                            {#each filter.options as [value, label]}
                                <option {value}>{label}</option>
                            {/each}
                        </select>
                        {/if}
                    </label>
                {/each}
            </div>
        {/if}
        {#if posts.data.length > 0}
        <GridList preset="content">
            {#each posts.data as item (item.uuid)}
                <li
                    class="relative h-53 w-full overflow-hidden rounded-md bg-blue-ocean transition hover:-translate-y-0.5"
                >
                    <article class="h-full">
                        <svelte:element
                            this={canOpenOnSite(item) ? "a" : "div"}
                            href={canOpenOnSite(item) ? item.href : undefined}
                            class={["block h-full focus:outline-none", canOpenOnSite(item) ? "cursor-pointer" : "cursor-default"]}
                            aria-label={canOpenOnSite(item) ? `Abrir ${item.title} no site` : undefined}
                        >
                            <div class="p-4">
                                <h3 class="font-noto-sans text-lg text-suspense-aurora line-clamp-4 uppercase">
                                    {item.title}
                                </h3>
                            </div>
                            {#if canReviewOpinions && item.module === "review" && item.review_status_counts}
                                <div class="absolute bottom-11 left-3 right-3 flex justify-center">
                                    <Tooltip position="top">
                                        <div class="flex min-h-6 items-center justify-center gap-1 rounded-sm px-1">
                                            {#each reviewStatusBadges as badge}
                                                {@const count = item.review_status_counts[badge.status] ?? 0}
                                                {#if count > 0}
                                                    <span
                                                        class={`inline-flex items-center justify-center rounded px-1.5 py-0.5 font-noto-sans text-[0.55rem] font-extrabold italic uppercase leading-none ${badge.class}`}
                                                        aria-label={`${count} ${badge.label}`}
                                                    >
                                                        {count} {badge.label}
                                                    </span>
                                                {/if}
                                            {/each}
                                        </div>
                                        <div slot="content" class="space-y-0.5 text-left">
                                            {#each reviewStatusBadges as badge}
                                                {@const count = item.review_status_counts[badge.status] ?? 0}
                                                {#if count > 0}
                                                    <div>{count} {badge.label}</div>
                                                {/if}
                                            {/each}
                                        </div>
                                    </Tooltip>
                                </div>
                            {/if}
                        </svelte:element>
                        <div class={`grid grid-cols-[minmax(3.75rem,auto)_minmax(0,1fr)_auto] items-center gap-2 absolute bottom-0 w-full py-1 px-4 ${resolveStatusBackground(statusItem(item), { useValidity: false })}`}>
                            <div class="flex min-w-0 items-center gap-2 whitespace-nowrap font-noto-sans font-extrabold italic uppercase text-md text-suspense-aurora"
                                title={String(item.views ?? 0) + " visualizações"}>
                                <img
                                    src="/svg/eye.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-4 filter-suspense-aurora"
                                    loading="lazy"
                                />
                                {formatCompactNumber(item.views)}
                            </div>
                            <div class="mt-[0.1rem] w-full truncate text-center font-noto-sans text-sm font-extrabold italic uppercase text-suspense-aurora">
                                {item.module === "review" ? "Review" : item.author.nickname}
                            </div>
                            <div class="relative z-10 flex gap-1 justify-end mt-1">
                                {#if item.can?.delete}
                                    <IconButton
                                        variant="trash"
                                        label="Remover"
                                        size="sm"
                                        surface="dark"
                                        on:click={() => requestDelete(item)}
                                    />
                                {:else if can.deactivate}
                                    <IconButton
                                        variant="trash"
                                        label="Desativar"
                                        size="sm"
                                        surface="dark"
                                        on:click={() => requestDeactivate(item)}
                                    />
                                {/if}
                                {#if can.update}
                                    <IconButton
                                        variant="edit"
                                        label="Atualizar"
                                        href={`/panel/post/${item.uuid}`}
                                        size="sm"
                                        surface="dark"
                                        on:click={() => operation(item.module)}
                                    />
                                {/if}
                            </div>
                        </div>
                    </article>
                </li>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                title="Nenhuma matéria encontrada"
                description="As matérias cadastradas aparecerão aqui."
            />
        {/if}
        <Pagination pages={posts} only={["posts"]} />
    </Section>
{/if}
