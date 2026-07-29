<script>
    let className;
    export { className as class };
    export let title = "Nenhum registro encontrado";
    export let description = null;
    export let icon = null;
    
    $: classes = [
        "flex min-h-40 w-full flex-col items-center justify-center rounded-md border border-dashed border-suspense-aurora/20 px-6 py-10 text-center",
        className,
    ];
</script>

<div class={classes} role="status">
    {#if icon || $$slots.icon}
        <div class="mb-3 flex size-10 items-center justify-center" aria-hidden="true">
            <slot name="icon">
                <img
                    src={icon}
                    alt=""
                    class="max-h-full max-w-full filter-neutral-gray"
                    loading="lazy"
                />
            </slot>
        </div>
    {/if}
    <p class="font-noto-sans text-lg font-extrabold uppercase italic text-suspense-aurora">
        {title}
    </p>
    {#if description}
        <p class="mt-1 max-w-xl font-noto-sans text-sm text-suspense-aurora/60">
            {description}
        </p>
    {/if}
    {#if $$slots.action}
        <div class="mt-5">
            <slot name="action" />
        </div>
    {/if}
</div>
