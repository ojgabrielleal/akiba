<script>
    export let title = "";
    export let size = "sm";

    import { fade } from "svelte/transition";
    import { onDestroy } from "svelte";

    let visible = false;
    let titleId = title ? `modal-title-${Math.random().toString(36).slice(2)}` : undefined;
    let previousBodyOverflow = "";
    let previousDocumentOverflow = "";
    let scrollLocked = false;
    
    $: if (typeof document !== "undefined") {
        if (visible && !scrollLocked) {
            previousBodyOverflow = document.body.style.overflow;
            previousDocumentOverflow = document.documentElement.style.overflow;
            document.body.style.overflow = "hidden";
            document.documentElement.style.overflow = "hidden";
            scrollLocked = true;
        }

        if (!visible && scrollLocked) {
            document.body.style.overflow = previousBodyOverflow;
            document.documentElement.style.overflow = previousDocumentOverflow;
            scrollLocked = false;
        }
    }

    onDestroy(() => {
        if (typeof document !== "undefined" && scrollLocked) {
            document.body.style.overflow = previousBodyOverflow;
            document.documentElement.style.overflow = previousDocumentOverflow;
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

    const sizes = {
        sm: "max-w-sm",
        md: "max-w-xl",
        lg: "max-w-3xl",
        xl: "max-w-6xl",
    };
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ duration: 200 }}
        class="fixed inset-0 z-100 flex h-dvh w-full items-center justify-center overflow-y-auto bg-black/40 p-3 backdrop-blur-xs sm:p-4"
        role="presentation"
        on:click={close}
    >
        <div
            class={[
                "relative my-auto w-full min-w-0 border-0 bg-suspense-aurora shadow-none outline-none ring-0 focus:outline-none focus:ring-0",
                title ? "rounded-t-2xl rounded-b-md" : "rounded-md",
                sizes[size] ?? sizes.sm,
            ]}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-label={title ? undefined : "Janela modal"}
            tabindex="-1"
            on:click={block}
        >
            {#if title}
                <div class="flex items-center justify-center rounded-t-md bg-blue-marinho p-4">
                    <h2 id={titleId} class="text-center text-suspense-aurora font-bold italic uppercase">
                        {title}
                    </h2>
                    <button
                        type="button"
                        class="absolute right-3 top-3 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-md transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-neutral-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-5 sm:-top-8 sm:h-6 sm:w-6 sm:shadow-lg motion-reduce:transform-none motion-reduce:transition-none"
                        aria-label="Fechar"
                        on:click={close}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-3 brightness-0"
                            loading="lazy"
                        />
                    </button>
                </div>
            {/if}
            <div class="max-h-[80vh] overflow-x-hidden overflow-y-auto p-4 sm:p-6">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
