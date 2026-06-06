<script>
    export let pages;
    export let loadingLabel = "Carregando...";
    export let only = [];

    import { router } from "@inertiajs/svelte";
    import Tooltip from "./Tooltip.svelte";

    let isLoading = false;
    let loadingAction = null;
    let pageHistory = [];
    const resetDelay = 900;

    $: currentPages = pageHistory.length > 0 ? pageHistory.at(-1) : pages;
    $: nextUrl = currentPages?.links?.next ?? currentPages?.next_page_url ?? null;
    $: hasNextPage = Boolean(nextUrl) || (
        currentPages?.meta?.current_page &&
        currentPages?.meta?.last_page &&
        currentPages.meta.current_page < currentPages.meta.last_page
    );

    $: shouldResetHistory = pages?.meta?.current_page === 1
        && (pages?.data?.length ?? 0) <= (pages?.meta?.per_page ?? pages?.data?.length ?? 0);

    $: if (shouldResetHistory) {
        pageHistory = [pages];
    }

    const updatePageUrl = (page) => {
        if (typeof window === "undefined") return;

        const url = new URL(window.location.href);

        if (page <= 1) {
            url.searchParams.delete("page");
        } else {
            url.searchParams.set("page", String(page));
        }

        window.history.replaceState(window.history.state, "", url);
    };

    const visit = (url, data = {}, action = "next") => {
        if (isLoading) return;

        const previousData = pages?.data ?? [];

        isLoading = true;
        loadingAction = action;

        router.visit(url, {
            data,
            preserveScroll: true,
            preserveState: true,
            only,
            onSuccess: (page) => {
                if (only.length === 1) {
                    const appendProp = only[0];
                    const nextPage = page.props[appendProp];
                    const nextHistory = [...pageHistory, nextPage];

                    router.replaceProp(appendProp, {
                        ...nextPage,
                        data: [...previousData, ...(nextPage?.data ?? [])],
                    });

                    pageHistory = nextHistory;
                }
            },
            onFinish: () => {
                isLoading = false;
                loadingAction = null;
            },
        });
    };

    const loadNextPage = () => {
        if (currentPages?.meta?.current_page && currentPages?.meta?.current_page < currentPages?.meta?.last_page) {
            visit("", { page: currentPages.meta.current_page + 1 }, "next");
        }
    };

    const resetPages = () => {
        if (isLoading || only.length !== 1 || pageHistory.length <= 1) return;

        isLoading = true;
        loadingAction = "previous";

        setTimeout(() => {
            pageHistory = pageHistory.slice(0, 1);
            updatePageUrl(1);

            router.replaceProp(only[0], {
                ...pageHistory.at(-1),
                data: pageHistory.flatMap((page) => page?.data ?? []),
            });

            isLoading = false;
            loadingAction = null;
        }, resetDelay);
    };
</script>

{#if hasNextPage || pageHistory.length > 1}
    <div class="flex justify-center mt-2">
        <div class="flex items-center justify-center gap-4 min-h-12">
            {#if pageHistory.length > 1 && !hasNextPage}
                {#if isLoading && loadingAction === "previous"}
                    <span class="pagination-spinner" aria-label={loadingLabel}></span>
                {:else}
                    <Tooltip>
                        <button
                            aria-label="Mostrar menos"
                            type="button"
                            class="cursor-pointer flex items-center justify-center disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={isLoading}
                            on:click={resetPages}
                        >
                            <img
                                src="/svg/chevron-up.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-10 filter-orange-citric pagination-less-arrow"
                                loading="lazy"
                            />
                        </button>
                        <span slot="content">
                            Mostrar menos
                        </span>
                    </Tooltip>
                {/if}
            {/if}
            {#if hasNextPage}
                {#if isLoading && loadingAction === "next"}
                    <span class="pagination-spinner" aria-label={loadingLabel}></span>
                {:else}
                    <Tooltip>
                        <button
                            aria-label="Mostrar mais"
                            type="button"
                            class="cursor-pointer flex items-center justify-center disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={isLoading}
                            on:click={loadNextPage}
                        >
                            <img
                                src="/svg/chevron-down.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-10 filter-orange-citric pagination-more-arrow"
                                loading="lazy"
                            />
                        </button>
                        <span slot="content">
                            Mostrar mais
                        </span>
                    </Tooltip>
                {/if}
            {/if}
        </div>
    </div>
{/if}

<style>
    .pagination-more-arrow {
        animation: pagination-more-arrow-bounce 1.8s ease-in-out infinite;
    }

    .pagination-less-arrow {
        animation: pagination-less-arrow-bounce 1.8s ease-in-out infinite;
    }

    .pagination-spinner {
        width: 1.5rem;
        height: 1.5rem;
        border: 0.2rem solid rgb(255 143 0 / 0.25);
        border-top-color: rgb(255 143 0);
        border-radius: 9999px;
        animation: pagination-spinner-spin 0.75s linear infinite;
    }

    @keyframes pagination-more-arrow-bounce {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(0.2rem);
        }
    }

    @keyframes pagination-less-arrow-bounce {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-0.15rem);
        }
    }

    @keyframes pagination-spinner-spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
