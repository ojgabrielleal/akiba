<script>
    export let pages;
    export let only = [];
    export let loadingLabel = "Carregando...";

    import { router } from "@inertiajs/svelte";
    import LoadingSpinner from "../feedback/LoadingSpinner.svelte";

    let isLoading = false;

    $: previousUrl = pages?.links?.prev ?? pages?.prev_page_url ?? null;
    $: nextUrl = pages?.links?.next ?? pages?.next_page_url ?? null;

    const visit = (url) => {
        if (!url || isLoading) return;

        isLoading = true;
        router.visit(url, {
            preserveScroll: true,
            preserveState: true,
            only,
            onFinish: () => {
                isLoading = false;
            },
        });
    };
</script>

{#if previousUrl || nextUrl}
    <nav class="mt-8 flex min-h-11 items-center justify-center gap-3" aria-label="Paginação">
        {#if isLoading}
            <LoadingSpinner label={loadingLabel} />
        {:else}
            <button
                type="button"
                aria-label="Página anterior"
                class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-blue-ocean disabled:cursor-not-allowed disabled:opacity-30"
                disabled={!previousUrl}
                on:click={() => visit(previousUrl)}
            >
                <img src="/svg/chevron-left.svg" alt="" aria-hidden="true" class="size-5 filter-suspense-aurora" />
            </button>
            <span class="min-w-20 text-center font-noto-sans text-xs font-extrabold uppercase italic text-suspense-aurora/60">
                {pages?.meta?.current_page ?? pages?.current_page ?? 1}
                /
                {pages?.meta?.last_page ?? pages?.last_page ?? 1}
            </span>
            <button
                type="button"
                aria-label="Próxima página"
                class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-blue-ocean disabled:cursor-not-allowed disabled:opacity-30"
                disabled={!nextUrl}
                on:click={() => visit(nextUrl)}
            >
                <img src="/svg/chevron-right.svg" alt="" aria-hidden="true" class="size-5 filter-suspense-aurora" />
            </button>
        {/if}
    </nav>
{/if}
