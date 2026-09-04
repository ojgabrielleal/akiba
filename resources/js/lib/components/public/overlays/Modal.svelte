<script>
    import { onDestroy, tick } from "svelte";
    import { fade, fly } from "svelte/transition";

    export let title = null;
    export let label = title || "Janela de diálogo";
    export let closeOnBackdrop = true;
    export let size = "md";

    let visible = false;
    let panel;
    let titleId = title ? `modal-title-${Math.random().toString(36).slice(2)}` : undefined;
    let previousBodyOverflow = "";
    let previousDocumentOverflow = "";

    const sizes = {
        sm: "max-w-sm",
        md: "lg:w-120",
        lg: "lg:w-160",
    };

    export const open = async () => {
        previousBodyOverflow = document.body.style.overflow;
        previousDocumentOverflow = document.documentElement.style.overflow;
        document.body.style.overflow = "hidden";
        document.documentElement.style.overflow = "hidden";
        visible = true;
        await tick();
        panel?.focus();
    };

    export const close = () => {
        visible = false;
        document.body.style.overflow = previousBodyOverflow;
        document.documentElement.style.overflow = previousDocumentOverflow;
    };

    const handleBackdrop = (event) => {
        if (closeOnBackdrop && event.target === event.currentTarget) close();
    };

    const handleKeydown = (event) => {
        if (visible && event.key === "Escape") close();
    };

    const portal = (node) => {
        document.body.appendChild(node);

        return {
            destroy() {
                node.remove();
            },
        };
    };

    onDestroy(() => {
        if (typeof document !== "undefined") {
            document.body.style.overflow = previousBodyOverflow;
            document.documentElement.style.overflow = previousDocumentOverflow;
        }
    });
</script>

<svelte:window on:keydown={handleKeydown} />

{#if visible}
    <div
        use:portal
        class="fixed inset-0 z-[200] flex h-screen w-screen items-center justify-center bg-black/40 p-9 backdrop-blur-xs"
        role="presentation"
        transition:fade={{ duration: 180 }}
        on:click={handleBackdrop}
    >
        <div
            bind:this={panel}
            class={["relative w-full rounded-t-xl rounded-b-xl bg-suspense-aurora shadow-2xl", sizes[size] ?? sizes.md]}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-label={title ? undefined : label}
            tabindex="-1"
            transition:fly={{ y: 12, duration: 220 }}
        >
            {#if title}
                <div class="grid grid-cols-[1.5rem_1fr_1.5rem] items-center rounded-t-md bg-blue-marinho p-4">
                    <span aria-hidden="true"></span>
                    <h2 id={titleId} class="text-center font-noto-sans font-bold text-suspense-aurora uppercase italic">
                        {title}
                    </h2>
                    <button
                        type="button"
                        class="flex cursor-pointer justify-end transition-opacity hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
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
            {:else}
                <button
                    type="button"
                    aria-label="Fechar"
                    class="absolute -top-8 -right-5 z-10 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-lg transition hover:-translate-y-0.5 hover:bg-neutral-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                    on:click={close}
                >
                    <img
                        src="/svg/close.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-3 filter-blue-marinho"
                    />
                </button>
            {/if}

            <div class="max-h-[70vh] w-full overflow-y-auto p-5 lg:max-h-[90vh]">
                <slot {close} />
            </div>
        </div>
    </div>
{/if}
