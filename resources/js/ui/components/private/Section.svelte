<script>
    import { Link } from "@inertiajs/svelte";

    export let title = null;
    export let styles = "container-page mb-10";
    export let actions = [];

    const actionButtonClass = (action) => [
        "cursor-pointer flex h-7 min-w-23 items-center justify-center gap-1.5 rounded-full px-3 text-sm font-black uppercase italic leading-none text-blue-marinho font-noto-sans",
        action.background ?? "bg-orange-citric",
        action.textColor ?? "text-blue-marinho"
    ];

    const actionIconClass = (action) => [
        "h-4 w-4 shrink-0 brightness-0",
        action.filter ?? "filter-blue-marinho",
    ];
</script>

<section class={styles}>
    {#if title}
        <div class="flex flex-wrap items-center gap-4 mb-5">
            <h2 class={["uppercase font-black italic text-[1.3rem] font-noto-sans",
                {"text-orange-citric": actions.length > 0}, 
                {"text-orange-amber": actions.length === 0}
            ]}>
                {title}
            </h2>
            <div class={["h-px flex-1",
                {"bg-orange-citric": actions.length > 0},
                {"bg-orange-amber": actions.length === 0}
            ]}>
            </div>
            {#if actions.length > 0}
                <div class="flex w-full justify-start gap-2 sm:w-auto sm:justify-end">
                    {#each actions as action}
                        {#if action.permission}
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
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    {/if}
    <slot />
</section>
