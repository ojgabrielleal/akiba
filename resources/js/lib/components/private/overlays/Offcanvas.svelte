<script>
    import { overlay } from "@/lib/utils/overlay";
    export let title;

    import { fly } from "svelte/transition";

    let visible = false;
    let titleId = title ? `offcanvas-title-${Math.random().toString(36).slice(2)}` : undefined;

    export const open = () => {
        visible = true;
    };

    export const close = () => {
        visible = false;
    };

    const block = (event) => {
        event.stopPropagation();
    };
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        use:overlay
        transition:fly={{ x: 400, duration: 300 }}
        class="fixed inset-0 z-[2147483647] h-dvh max-h-dvh w-full overflow-hidden bg-black/1 backdrop-blur-xs"
        role="presentation"
        on:click={close}
    >
        <div
            class="absolute inset-y-0 right-0 flex h-full max-h-full w-[min(24rem,100vw)] flex-col overflow-hidden bg-suspense-aurora"
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            tabindex="-1"
            on:click={block}
        >
        {#if title}
            <h2 id={titleId} class="shrink-0 bg-blue-ocean py-5 px-4 text-suspense-aurora text-center font-bold italic uppercase">
                {title}
            </h2>
        {/if}
            <div class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain px-4 pt-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] sm:pl-5 sm:pr-8 sm:pt-8">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
