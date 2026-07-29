<script>
    import { onDestroy, tick } from "svelte";
    import { fade, fly } from "svelte/transition";

    export let title = null;
    export let label = title || "Janela de diálogo";
    export let closeOnBackdrop = true;
    export let size = "md";

    let visible = false;
    let panel;
    let previousBodyOverflow = "";

    const sizes = {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-xl",
    };

    export const open = async () => {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        visible = true;
        await tick();
        panel?.focus();
    };

    export const close = () => {
        visible = false;
        document.body.style.overflow = previousBodyOverflow;
    };

    const handleBackdrop = (event) => {
        if (closeOnBackdrop && event.target === event.currentTarget) close();
    };

    const handleKeydown = (event) => {
        if (visible && event.key === "Escape") close();
    };

    onDestroy(() => {
        if (typeof document !== "undefined") {
            document.body.style.overflow = previousBodyOverflow;
        }
    });
</script>

<svelte:window on:keydown={handleKeydown} />

{#if visible}
    <div
        class="fixed inset-0 z-150 flex items-center justify-center bg-blue-night/65 p-4 backdrop-blur-sm"
        role="presentation"
        transition:fade={{ duration: 180 }}
        on:click={handleBackdrop}
    >
        <div
            bind:this={panel}
            class={["w-full overflow-hidden rounded-xl bg-suspense-aurora shadow-2xl", sizes[size] ?? sizes.md]}
            role="dialog"
            aria-modal="true"
            aria-label={label}
            tabindex="-1"
            transition:fly={{ y: 12, duration: 220 }}
        >
            <header class="flex min-h-14 items-center justify-between gap-4 border-b border-blue-night/10 px-5 py-3">
                {#if title}
                    <h2 class="font-noto-sans text-base font-extrabold uppercase italic text-blue-night">
                        {title}
                    </h2>
                {:else}
                    <span></span>
                {/if}
                <button
                    type="button"
                    aria-label="Fechar"
                    class="flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-full transition hover:bg-blue-night/5 active:scale-95"
                    on:click={close}
                >
                    <img
                        src="/svg/close.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-4 filter-blue-marinho"
                    />
                </button>
            </header>

            <div class="max-h-[min(75dvh,40rem)] overflow-y-auto p-5">
                <slot {close} />
            </div>
        </div>
    </div>
{/if}
