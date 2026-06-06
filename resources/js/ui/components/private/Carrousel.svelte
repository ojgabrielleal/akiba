<script>
    import { afterUpdate, onMount, tick } from "svelte";

    let container;
    let canCarrouselLeft = false;
    let canCarrouselRight = false;

    const updateCarrouselState = () => {
        if (!container) return;

        canCarrouselLeft = container.scrollLeft > 0;
        canCarrouselRight =
            container.scrollLeft + container.clientWidth <
            container.scrollWidth - 1;
    };

    const moveCarrousel = (direction) => {
        if (!container) return;

        container.scrollBy({
            left:
                direction === "left"
                    ? -container.clientWidth * 0.75
                    : container.clientWidth * 0.75,
            behavior: "smooth",
        });
    };

    onMount(() => {
        tick().then(updateCarrouselState);

        window.addEventListener("resize", updateCarrouselState);

        return () => {
            window.removeEventListener("resize", updateCarrouselState);
        };
    });

    afterUpdate(() => {
        tick().then(updateCarrouselState);
    });
</script>

<div class="relative">
    {#if canCarrouselLeft}
        <div class="flex absolute -left-6 xl:-left-9 2xl:-left-12 top-0 bottom-0 z-10 w-14 xl:w-22 items-center justify-center pointer-events-none">
            <button
                type="button"
                aria-label="Rolar para esquerda"
                class="pointer-events-auto cursor-pointer w-14 h-14 xl:w-18 xl:h-18 rounded-full border-8 xl:border-10 border-blue-marinho bg-orange-citric flex justify-center items-center"
                on:click={() => moveCarrousel("left")}
            >
                <img
                    src="/svg/chevron-left.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 xl:w-auto pr-[0.1rem] filter-blue-marinho"
                    loading="lazy"
                />
            </button>
        </div>
    {/if}

    <div
        bind:this={container}
        class="carrousel-scroll overflow-x-auto flex gap-5 flex-nowrap scroll-smooth"
        role="group"
        on:scroll={updateCarrouselState}
    >
        <slot />
    </div>

    {#if canCarrouselRight}
        <div class="flex absolute -right-6 xl:-right-9 2xl:-right-12 top-0 bottom-0 z-10 w-14 xl:w-22 items-center justify-center pointer-events-none">
            <button
                type="button"
                aria-label="Rolar para direita"
                class="pointer-events-auto cursor-pointer w-14 h-14 xl:w-18 xl:h-18 rounded-full border-8 xl:border-10 border-blue-marinho bg-orange-citric flex justify-center items-center"
                on:click={() => moveCarrousel("right")}
            >
                <img
                    src="/svg/chevron-right.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 xl:w-auto pl-[0.1rem] filter-blue-marinho"
                    loading="lazy"
                />
            </button>
        </div>
    {/if}
</div>
