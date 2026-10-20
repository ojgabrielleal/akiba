<script>
    import { fade } from "svelte/transition";
    import { onDestroy } from "svelte";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { defaultBrandLogo, resolveCommemorativeLogo, themeClass } from "@/lib/utils";

    let visible = false;
    let previousBodyOverflow = "";
    let previousDocumentOverflow = "";
    let scrollLocked = false;
    let brandLogo = resolveCommemorativeLogo();

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
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ duration: 200 }}
        class="modal-active fixed inset-0 z-[2147483000] flex h-dvh w-full items-center justify-center overflow-y-auto bg-black/40 p-3 backdrop-blur-xs sm:p-4"
        role="presentation"
        on:click={close}
    >
        <div
            class={[
                "relative my-auto flex max-h-[80vh] w-full min-w-0 max-w-sm flex-col rounded-md bg-suspense-aurora shadow-[0_1.25rem_3rem_rgba(0,0,0,0.24)] [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))]",
                themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
            ]}
            role="dialog"
            aria-modal="true"
            aria-label="Pedido musical"
            tabindex="-1"
            on:click={block}
        >
            <header class="w-full h-20 shrink-0 pt-8 px-4 sm:px-6 lg:mb-2 bg-cover bg-center rounded-t-md" style="background-image: url('/img/player/song-requests-bg.webp');">
                <div class="w-60 mt-4">
                    <img
                        src={brandLogo.src}
                        alt={brandLogo.alt}
                        width="240"
                        height="80"
                        on:error={() => brandLogo = defaultBrandLogo}
                    />
                </div>
                <button
                    type="button"
                    aria-label="Fechar modal"
                    class={[
                        "absolute -right-3 -top-3 z-20 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-[0_0.5rem_1.25rem_rgba(0,0,20,0.22)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-4 sm:-top-4 sm:h-7 sm:w-7 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:hover:bg-blue-ocean [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:hover:bg-blue-ocean",
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
                        width="12"
                        height="12"
                    />
                </button>
            </header>
            <div class="min-h-0 w-full flex-1 overflow-x-hidden overflow-y-auto rounded-b-md p-4 pt-8 sm:p-6 sm:pt-8">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
