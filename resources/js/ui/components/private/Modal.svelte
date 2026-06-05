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
</script>

{#if visible}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        transition:fade={{ duration: 200 }}
        class="w-screen h-screen fixed inset-0 flex justify-center items-center bg-black/40 backdrop-blur-xs z-100 p-4"
        on:click={close}
    >
        <div class="w-full min-w-sm max-w-sm bg-suspense-aurora rounded-md overflow-hidden" on:click={block}>
            {#if title}
                <div class="bg-blue-skywave p-4 text-suspense-aurora font-extrabold italic uppercase flex justify-between items-center">
                    <span>
                        {title}
                    </span>
                    <button
                        type="button"
                        class="cursor-pointer hover:opacity-80 transition-opacity"
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
