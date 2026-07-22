<script>
    export let title;

    import { fly } from "svelte/transition";
    import { onMount, onDestroy } from "svelte";

    let visible = false;
    let titleId = title ? `offcanvas-title-${Math.random().toString(36).slice(2)}` : undefined;

    const setPageOverflowY = (overflow) => {
        if (typeof document !== "undefined") document.body.style.overflowY = overflow;
    };

    onMount(() => setPageOverflowY("auto"));

    onDestroy(() => {
        if (typeof document !== "undefined") {
            setPageOverflowY("auto");
        }
    });

    export const open = () => {
        visible = true;
        setPageOverflowY("hidden");
    };

    export const close = () => {
        visible = false;
        setPageOverflowY("auto");
    };

    const block = (event) => {
        event.stopPropagation();
    };
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fly={{ x: 400, duration: 300 }}
        class="fixed inset-0 z-100 h-dvh max-h-dvh w-screen bg-black/1 backdrop-blur-xs"
        role="presentation"
        on:click={close}
    >
        <div
            class="float-right flex h-dvh max-h-dvh w-[min(24rem,100vw)] flex-col bg-suspense-aurora"
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
            <div class="min-h-0 flex-1 overflow-y-auto px-4 pt-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] sm:pl-5 sm:pr-8 sm:pt-8">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
