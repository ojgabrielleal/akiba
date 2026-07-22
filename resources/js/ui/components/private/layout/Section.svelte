<script>
    let className;
    export { className as class };
    export let title = null;
    export let actions = [];

    import { Link } from "@inertiajs/svelte";

    const actionButtonClass = (action) => [
        "cursor-pointer flex h-7 min-w-23 items-center justify-center gap-1.5 rounded-full px-3 text-sm font-black uppercase italic leading-none text-blue-marinho font-noto-sans",
        action.background ?? "bg-orange-citric",
        action.textColor ?? "text-blue-marinho",
    ];

    const actionIconClass = (action) => [
        "h-4 w-4 shrink-0 brightness-0",
        action.filter ?? "filter-blue-marinho",
    ];

    $: classes = ["container-page mb-10", className];
    $: visibleActions = actions.filter((action) => action.permission);
    $: hasActions = visibleActions.length > 0;
    $: titleColor = hasActions ? "text-orange-citric" : "text-orange-amber";
    $: lineColor = hasActions ? "bg-orange-citric" : "bg-orange-amber";
</script>

<section {...$$restProps} class={classes}>
    {#if title}
        <header class="mb-5 flex flex-wrap items-center gap-4">
            <h2 class={["font-noto-sans text-[1.3rem] font-black uppercase italic", titleColor]}>
                {title}
            </h2>
            <div class={["h-px flex-1", lineColor]}></div>

            {#if hasActions}
                <div class="flex w-full flex-wrap justify-start gap-2 sm:w-auto sm:justify-end">
                    {#each visibleActions as action}
                        {#if action.onClick}
                            <button
                                type="button"
                                title={action.title}
                                aria-label={action.title}
                                class={actionButtonClass(action)}
                                on:click={action.onClick}
                            >
                                {#if action.icon}
                                    <img
                                        src={action.icon}
                                        alt=""
                                        class={actionIconClass(action)}
                                    />
                                {/if}
                                <span>{action.title}</span>
                            </button>
                        {:else}
                            <Link
                                preserveState={true}
                                href={action.href}
                                title={action.title}
                                aria-label={action.title}
                                class={actionButtonClass(action)}
                            >
                                {#if action.icon}
                                    <img
                                        src={action.icon}
                                        alt=""
                                        class={actionIconClass(action)}
                                    />
                                {/if}
                                <span>{action.title}</span>
                            </Link>
                        {/if}
                    {/each}
                </div>
            {/if}
        </header>
    {/if}

    <slot />
</section>
