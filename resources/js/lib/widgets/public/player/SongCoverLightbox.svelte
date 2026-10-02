<script>
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let open = false;
    export let cover = null;
    export let music = null;
    export let close = () => {};

    $: image = resolvePlaceholderImage(cover, "placeholder");
    $: title = music || "Estamos offline";

    const handleKeydown = (event) => {
        if (open && event.key === "Escape") {
            close();
        }
    };
</script>

<svelte:window on:keydown={handleKeydown} />

{#if open}
    <div class="fixed inset-0 z-[220] flex items-center justify-center bg-blue-night/72 p-3 backdrop-blur-xs sm:p-5 lg:p-8">
        <button
            type="button"
            class="absolute inset-0 cursor-zoom-out focus-visible:outline-none"
            aria-label="Fechar capa da música"
            on:click={close}
        ></button>
        <button
            type="button"
            class={["absolute right-4 top-4 z-20 flex size-11 cursor-pointer items-center justify-center rounded-full bg-orange-citric text-blue-night shadow-lg shadow-blue-night/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora sm:right-6 sm:top-6", publicAnimations.iconButtonInteractive]}
            aria-label="Fechar capa da música"
            on:click={close}
        >
            <img src="/svg/close.svg" alt="" aria-hidden="true" class="size-4 brightness-0" />
        </button>
        <div
            class="relative flex max-h-[94dvh] w-full max-w-7xl cursor-default items-center justify-center"
            role="dialog"
            aria-modal="true"
            aria-label={`Capa da música ${title}`}
        >
            <figure class="relative max-h-[94dvh] max-w-full overflow-hidden rounded-md shadow-2xl shadow-blue-night/50">
                <img
                    src={image}
                    alt={music ? `Capa da música ${music}` : "Capa da música atual"}
                    class="max-h-[94dvh] max-w-full object-contain"
                />

                <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-blue-night via-blue-night/90 to-blue-night/0 px-4 pb-4 pt-16 font-noto-sans text-suspense-aurora sm:px-6 sm:pb-6">
                    <div class="mx-auto max-w-3xl">
                        <p class="text-xs font-black uppercase italic tracking-[0.18em] text-orange-morning">
                            Tocando agora:
                        </p>
                        <h3 class="break-words text-xl font-black uppercase italic leading-tight text-orange-citric sm:text-2xl">
                            {title}
                        </h3>
                    </div>
                </figcaption>
            </figure>
        </div>
    </div>
{/if}
