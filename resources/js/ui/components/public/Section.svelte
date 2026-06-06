<script>
    export let title = null;
    export let styles = "container-page mb-10";
</script>

<section class={styles}>
    {#if title}
        <div class="flex items-center gap-4 mb-5 after:content-[''] after:flex-1 after:h-px after:bg-orange-amber">
            <h1 class="uppercase font-black italic whitespace-nowrap text-[1.3rem] text-orange-amber font-noto-sans">
                {title}
            </h1>
        </div>
    {/if}
    <slot />
</section>
