<script>
    import { fade } from "svelte/transition";
    import { quintOut } from "svelte/easing";
    import { onDestroy } from "svelte";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { themeClass } from "@/lib/utils";

    let visible = false;
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
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ x: "100%", duration: 500, easing: quintOut }}
        class="modal-active fixed inset-0 z-[200] flex h-[100dvh] w-screen items-center justify-center bg-black/40 p-4 backdrop-blur-xs sm:p-6 lg:p-9"
        role="presentation"
        on:click={close}
    >
        <div
            class={[
                "relative flex max-h-[calc(100dvh-2rem)] w-full flex-col rounded-t-xl rounded-b-xl bg-suspense-aurora sm:max-h-[calc(100dvh-3rem)] lg:w-104 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_90%,var(--color-blue-night))]",
                themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
            ]}
            role="dialog"
            aria-modal="true"
            aria-label="Pedido musical"
            tabindex="-1"
            on:click={block}
        >
            <header class="w-full h-20 pt-8 px-5 lg:mb-2 bg-cover bg-center rounded-t-xl" style="background-image: url('/img/player/song-requests-bg.webp');">
                <div class="w-60 mt-4">
                    <img
                        src="/img/brand/logo.webp"
                        alt="Akiba Station"
                        loading="lazy"
                    />
                </div>
                <button
                    type="button"
                    aria-label="Fechar modal"
                    class={[
                        "absolute right-3 top-3 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-suspense-aurora shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric sm:-right-5 sm:-top-8 sm:h-6 sm:w-6 sm:shadow-lg [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:hover:bg-blue-ocean [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:hover:bg-blue-ocean",
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
                        loading="lazy"
                    />
                </button>
            </header>
            <div class="w-full flex-1 overflow-y-auto p-5 pt-8 lg:pt-5">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
