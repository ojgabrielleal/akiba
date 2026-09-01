<script>
    import { themeClass } from "@/lib/utils";

    export let title = null;
    export let styles = "container-page mb-10";
</script>

<section class={styles}>
    {#if title}
        <div class="public-section-heading mb-5 flex items-center gap-4">
            <h2 class={["public-section-heading-title uppercase font-black italic whitespace-nowrap text-[1.3rem] text-orange-citric font-noto-sans", themeClass("text", "blue-cerulean", { theme: "light" })]}>
                {title}
            </h2>
            <span class={["public-section-heading-line h-px min-w-10 flex-1 bg-orange-citric", themeClass("bg", "blue-cerulean", { theme: "light" })]} aria-hidden="true"></span>
        </div>
    {/if}
    <slot />
</section>
