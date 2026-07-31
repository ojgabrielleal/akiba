<script>
    export let title = "";
    export let size = "sm";

    import { fade } from "svelte/transition";
    import { onMount, onDestroy } from "svelte";

    let visible = false;
    let titleId = title ? `modal-title-${Math.random().toString(36).slice(2)}` : undefined;
    
    onMount(()=>{
        if (typeof document !== "undefined"){
            document.body.style.overflow = visible ? "hidden" : "auto";
        }
    });

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

    const sizes = {
        sm: "max-w-sm",
        md: "max-w-xl",
        lg: "max-w-3xl",
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
            class={["my-auto w-full min-w-0 rounded-t-2xl rounded-b-md border-0 bg-suspense-aurora shadow-none outline-none ring-0 focus:outline-none focus:ring-0", sizes[size] ?? sizes.sm]}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-label={title ? undefined : "Janela modal"}
            tabindex="-1"
            on:click={block}
        >
            {#if title}
                <div class="grid grid-cols-[1.5rem_1fr_1.5rem] items-center rounded-t-md bg-blue-marinho p-4">
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
            <div class="max-h-[calc(100dvh-5rem)] overflow-x-hidden overflow-y-auto p-4 sm:p-6">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
