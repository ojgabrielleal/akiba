<script>
    import { onDestroy, tick } from "svelte";
    import { fade } from "svelte/transition";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { applyPublicTheme, getStoredPublicTheme, themeClass } from "@/lib/utils";

    export let title = null;
    export let label = title || "Janela de diálogo";
    export let closeOnBackdrop = true;
    export let size = "md";
    export let bordered = true;

    let visible = false;
    let panel;
    let titleId = title ? `modal-title-${Math.random().toString(36).slice(2)}` : undefined;
    let previousBodyOverflow = "";
    let previousDocumentOverflow = "";
    let selectedTheme = getStoredPublicTheme();

    const sizes = {
        sm: "max-w-sm",
        md: "max-w-xl",
        lg: "max-w-3xl",
        xl: "max-w-6xl",
    };

    export const open = async () => {
        selectedTheme = getStoredPublicTheme();
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
        applyPublicTheme(selectedTheme);

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
        data-public-theme-scope
        data-public-theme={selectedTheme}
        class="fixed inset-0 z-[2147483000] flex h-dvh w-full items-center justify-center overflow-y-auto bg-black/40 p-3 backdrop-blur-xs sm:p-4"
        role="presentation"
        transition:fade={{ duration: 200 }}
        on:click={handleBackdrop}
    >
        <div
            bind:this={panel}
            class={[
                "relative my-auto flex max-h-[80vh] w-full min-w-0 flex-col rounded-md bg-blue-ocean shadow-[0_1.25rem_3rem_rgba(0,0,0,0.24)] outline-none focus:outline-none focus-visible:outline-none [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))]",
                themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
                bordered && "[[data-public-theme=light]_&]:border [[data-public-theme=light]_&]:border-blue-night/10 [[data-public-theme=light]_&]:shadow-2xl",
                sizes[size] ?? sizes.md,
            ]}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-label={title ? undefined : label}
            tabindex="-1"
        >
            {#if title}
                <div class={["flex min-h-15 shrink-0 items-center rounded-t-md bg-blue-marinho px-4 py-3 sm:px-6 [[data-public-theme=light]_&]:!bg-suspense-sandstone", themeClass("bg", "blue-marinho", { theme: "light" })]}>
                    <h2 id={titleId} class={["font-noto-sans text-base font-black text-suspense-aurora uppercase italic [[data-public-theme=light]_&]:!text-[#000014]", themeClass("text", "suspense-aurora", { fixed: true })]}>
                        {title}
                    </h2>
                    <button
                        type="button"
                        class={[
                            "absolute -right-3 -top-3 z-20 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-[0_0.5rem_1.25rem_rgba(0,0,20,0.22)] hover:bg-neutral-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-4 sm:-top-4 sm:h-7 sm:w-7 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:hover:bg-blue-ocean [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:hover:bg-blue-ocean",
                            themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
                            publicAnimations.iconButtonInteractive,
                        ]}
                        aria-label="Fechar"
                        on:click={close}
                    >
                        <img
                            src="/svg/close.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-3 brightness-0 [[data-public-theme=akiba]_&]:filter-suspense-aurora [[data-public-theme=night]_&]:filter-suspense-aurora"
                            width="12"
                            height="12"
                        />
                    </button>
                </div>
            {:else}
                <button
                    type="button"
                    aria-label="Fechar"
                    class={[
                        "absolute -right-3 -top-3 z-20 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-[0_0.5rem_1.25rem_rgba(0,0,20,0.22)] hover:bg-neutral-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-4 sm:-top-4 sm:h-7 sm:w-7 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:hover:bg-blue-ocean [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:hover:bg-blue-ocean",
                        themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
                        publicAnimations.iconButtonInteractive,
                    ]}
                    on:click={close}
                >
                    <img
                        src="/svg/close.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-3 brightness-0 [[data-public-theme=akiba]_&]:filter-suspense-aurora [[data-public-theme=night]_&]:filter-suspense-aurora"
                    />
                </button>
            {/if}

            <div class="min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto rounded-b-md p-4 sm:p-6">
                <slot {close} />
            </div>
        </div>
    </div>
{/if}
