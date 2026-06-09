<script>
    import { fade } from "svelte/transition";
    import { quintOut } from "svelte/easing";
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
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ x: "100%", duration: 500, easing: quintOut }}
        class="modal-active w-screen h-screen fixed inset-0 flex justify-center items-center p-9 bg-[#00000086] z-50"
        role="presentation"
        on:click={close}
    >
        <div
            class="w-full lg:w-104 bg-suspense-aurora rounded-t-xl rounded-b-xl relative"
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
                    class="w-6 h-6 cursor-pointer absolute -top-8 -right-5 flex justify-center items-center bg-suspense-aurora rounded-full"
                    on:click={close}
                >
                    <img
                        src="/svg/close.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-3"
                        loading="lazy"
                    />
                </button>
            </header>
            <div class="w-full max-h-[70vh] lg:max-h-[90vh] mt-5 p-5 overflow-y-auto">
                <slot name="content" {close} />
            </div>
        </div>
    </div>
{/if}
