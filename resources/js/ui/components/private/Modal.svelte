<script>
    export let title = "";

    import { fade } from "svelte/transition";
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

    const titleId = `modal-title-${Math.random().toString(36).slice(2)}`;
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ duration: 200 }}
        class="w-screen h-screen fixed inset-0 flex justify-center items-center bg-black/40 backdrop-blur-xs z-100 p-4"
        role="presentation"
        on:click={close}
    >
        <div
            class="w-full min-w-sm max-w-sm bg-suspense-aurora rounded-md overflow-hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby={title ? titleId : undefined}
            aria-label={title ? undefined : "Janela modal"}
            tabindex="-1"
            on:click={block}
        >
            {#if title}
                <div class="grid grid-cols-[1.5rem_1fr_1.5rem] items-center bg-blue-skywave p-4">
                    <span aria-hidden="true"></span>
                    <h2 id={titleId} class="text-center text-suspense-aurora font-bold italic uppercase">
                        {title}
                    </h2>
                    <button
                        type="button"
                        class="flex cursor-pointer justify-end transition-opacity hover:opacity-80"
                        aria-label="Fechar"
                        on:click={close}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-4 invert brightness-0"
                            loading="lazy"
                        />
                    </button>
                </div>
            {/if}
            <div class="p-6 overflow-y-auto max-h-[80vh]">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
