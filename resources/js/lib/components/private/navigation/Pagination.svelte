<script>
    export let pages;
    export let loadingLabel = "Carregando...";
    export let only = [];

    import { router } from "@inertiajs/svelte";
    import LoadingSpinner from "../feedback/LoadingSpinner.svelte";

    let isLoading = false;
    let loadingAction = null;

    $: previousUrl = pages?.links?.prev ?? pages?.prev_page_url ?? null;
    $: nextUrl = pages?.links?.next ?? pages?.next_page_url ?? null;
    $: currentPage = pages?.meta?.current_page ?? pages?.current_page ?? 1;
    $: lastPage = pages?.meta?.last_page ?? pages?.last_page ?? 1;
    $: numericLinks = (pages?.meta?.links ?? [])
        .filter((link) => /^\d+$/.test(String(link.label)))
        .map((link) => ({
            ...link,
            page: Number(link.label),
        }));
    $: pageLinks = numericLinks.length > 0
        ? numericLinks
        : Array.from({ length: lastPage }, (_, index) => ({
            page: index + 1,
            label: String(index + 1),
            url: pageUrl(index + 1),
            active: currentPage === index + 1,
        }));
    $: visiblePageLinks = pageLinks.filter((link) => {
        if (lastPage <= 3) return true;

        if (currentPage <= 2) return link.page <= 3;
        if (currentPage >= lastPage - 1) return link.page >= lastPage - 2;

        return link.page >= currentPage - 1 && link.page <= currentPage + 1;
    });

    const pageUrl = (page) => {
        if (typeof window === "undefined") return null;

        const url = new URL(window.location.href);

        if (page <= 1) {
            url.searchParams.delete("page");
        } else {
            url.searchParams.set("page", String(page));
        }

        return `${url.pathname}${url.search}${url.hash}`;
    };

    const visit = (url, action) => {
        if (!url || isLoading) return;

        isLoading = true;
        loadingAction = action;

        router.visit(url, {
            preserveScroll: true,
            preserveState: true,
            only,
            onFinish: () => {
                isLoading = false;
                loadingAction = null;
            },
        });
    };
</script>

{#if previousUrl || nextUrl}
    <div class="mt-2 flex justify-end">
        <nav class="flex min-h-12 items-center justify-center gap-1" aria-label="Paginação">
            {#if previousUrl}
                {#if isLoading && loadingAction === "previous"}
                    <LoadingSpinner label={loadingLabel} />
                {:else}
                    <button
                        type="button"
                        aria-label="Página anterior"
                        class="grid size-8 cursor-pointer place-items-center rounded-md bg-orange-citric disabled:cursor-not-allowed disabled:opacity-50"
                        disabled={isLoading}
                        on:click={() => visit(previousUrl, "previous")}
                    >
                        <img src="/svg/chevron-left.svg" alt="" aria-hidden="true" class="size-5 filter-blue-marinho" />
                    </button>
                {/if}
            {/if}

            {#each visiblePageLinks as link}
                <button
                    type="button"
                    aria-current={link.active ? "page" : undefined}
                    class={[
                        "grid size-8 cursor-pointer place-items-center rounded-md font-noto-sans text-xs font-extrabold italic text-suspense-aurora disabled:cursor-not-allowed disabled:opacity-50",
                        link.active ? "bg-neutral-gray" : "bg-blue-ocean hover:bg-neutral-gray",
                    ]}
                    disabled={isLoading || link.active || !link.url}
                    on:click={() => visit(link.url, `page-${link.page}`)}
                >
                    {link.page}
                </button>
            {/each}

            {#if nextUrl}
                {#if isLoading && loadingAction === "next"}
                    <LoadingSpinner label={loadingLabel} />
                {:else}
                    <button
                        type="button"
                        aria-label="Próxima página"
                        class="grid size-8 cursor-pointer place-items-center rounded-md bg-orange-citric disabled:cursor-not-allowed disabled:opacity-50"
                        disabled={isLoading}
                        on:click={() => visit(nextUrl, "next")}
                    >
                        <img src="/svg/chevron-right.svg" alt="" aria-hidden="true" class="size-5 filter-blue-marinho" />
                    </button>
                {/if}
            {/if}
        </nav>
    </div>
{/if}
