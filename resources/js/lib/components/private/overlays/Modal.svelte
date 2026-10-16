<script>
    export let title = "";
    export let size = "sm";
    export let flush = false;

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
        class="fixed inset-0 z-[2147483000] flex h-dvh w-full items-center justify-center overflow-y-auto bg-black/45 p-3 backdrop-blur-xs sm:p-4"
        role="presentation"
        on:click={close}
    >
        <div class={["relative my-auto w-full min-w-0", sizes[size] ?? sizes.sm]} on:click={block}>
            <button
                type="button"
                class="absolute -right-3 -top-3 z-20 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-[0_0.5rem_1.25rem_rgba(0,0,20,0.22)] transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-neutral-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-4 sm:-top-4 sm:h-7 sm:w-7 motion-reduce:transform-none motion-reduce:transition-none"
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

            <div
                class={[
                "relative flex max-h-[80vh] w-full min-w-0 flex-col overflow-hidden rounded-md bg-suspense-aurora shadow-[0_1.25rem_3rem_rgba(0,0,20,0.24)] outline-none ring-0 focus:outline-none focus:ring-0",
            ]}
            role="dialog"
            aria-modal="true"
            aria-labelledby={title ? titleId : undefined}
            aria-label={title ? undefined : "Janela modal"}
            tabindex="-1"
        >
            {#if title}
                <header class="flex min-h-15 items-center border-b border-blue-marinho/10 bg-suspense-aurora px-4 py-3 sm:px-6">
                    <h2 id={titleId} class="font-noto-sans text-base font-black uppercase italic text-blue-marinho">
                        {title}
                    </h2>
                </header>
            {/if}

            <div class={["min-h-0 flex-1 overflow-x-hidden overflow-y-auto", flush ? "p-0" : "p-4 sm:p-6"]}>
                <slot name="content" {close} />
            </div>
            </div>
        </div>
    </div>
{/if}
