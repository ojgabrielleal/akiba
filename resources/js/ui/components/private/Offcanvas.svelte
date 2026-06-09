<script>
    export let title;

    import { fly } from "svelte/transition";
    import { onDestroy } from "svelte";

    let visible = false;

    $: if (typeof document !== "undefined") {
        document.body.style.overflow = visible ? "hidden" : "auto";
    }

    onDestroy(() => {
        if (typeof document !== "undefined") {
            document.body.style.overflow = "auto";
        }
    });

    export const open = () => {
        visible = true;
    };

    export const close = () => {
        visible = false;
    };

    const block = (event) => {
        event.stopPropagation();
    };

    const titleId = `offcanvas-title-${Math.random().toString(36).slice(2)}`;
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fly={{ x: 400, duration: 300 }}
        class="w-screen h-screen fixed inset-0 bg-black/1 backdrop-blur-xs z-100"
        role="presentation"
        on:click={close}
    >
        <div
            class="max-w-sm min-w-sm h-screen float-right bg-suspense-aurora"
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            tabindex="-1"
            on:click={block}
        >
            <h2 id={titleId} class="bg-blue-ocean py-5 px-4 text-suspense-aurora text-center font-bold italic uppercase">
                {title}
            </h2>
            <div class="pl-5 pr-8 pt-8 h-[calc(100vh-6rem)] overflow-y-auto">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
