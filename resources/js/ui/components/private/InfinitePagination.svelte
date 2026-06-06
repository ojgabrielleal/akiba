<script>
    export let pages;
    export let loadingLabel = "Carregando...";
    export let rootMargin = "240px";
    export let only = [];

    import { afterUpdate, onDestroy } from "svelte";
    import { router } from "@inertiajs/svelte";

    let sentinel;
    let observer;
    let isLoading = false;

    $: nextUrl = pages?.links?.next ?? pages?.next_page_url ?? null;
    $: hasNextPage = Boolean(nextUrl) || (
        pages?.meta?.current_page &&
        pages?.meta?.last_page &&
        pages.meta.current_page < pages.meta.last_page
    );

    const visit = (url, data = {}) => {
        if (isLoading) return;

        const previousData = pages?.data ?? [];

        isLoading = true;

        router.visit(url, {
            data,
            preserveScroll: true,
            preserveState: true,
            only,
            onSuccess: (page) => {
                if (only.length === 1) {
                    const appendProp = only[0];
                    const nextPage = page.props[appendProp];

                    router.replaceProp(appendProp, {
                        ...nextPage,
                        data: [...previousData, ...(nextPage?.data ?? [])],
                    });
                }
            },
            onFinish: () => {
                isLoading = false;
            },
        });
    };

    const loadNextPage = () => {
        if (pages?.meta?.current_page && pages?.meta?.current_page < pages?.meta?.last_page) {
            visit("", { page: pages.meta.current_page + 1 });
        }
    };

    const observeSentinel = () => {
        observer?.disconnect();

        if (!hasNextPage || !sentinel) return;

        observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadNextPage();
            }
        }, { rootMargin });

        observer.observe(sentinel);
    };

    afterUpdate(observeSentinel);

    onDestroy(() => {
        observer?.disconnect();
    });
</script>

{#if hasNextPage}
    <div bind:this={sentinel} class="flex justify-center mt-10 min-h-12">
        {#if isLoading}
            <span class="infinite-pagination-spinner" aria-label={loadingLabel}></span>
        {/if}
    </div>
{/if}

<style>
    .infinite-pagination-spinner {
        width: 1.5rem;
        height: 1.5rem;
        border: 0.2rem solid rgb(255 143 0 / 0.25);
        border-top-color: rgb(255 143 0);
        border-radius: 9999px;
        animation: infinite-pagination-spinner-spin 0.75s linear infinite;
    }

    @keyframes infinite-pagination-spinner-spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
