<script>
    export let label = "Carrossel";
    export let scrollAmount = 0.75;
    let className = "";
    export { className as class };

    import { onMount, tick } from "svelte";

    let container;
    let canScrollLeft = false;
    let canScrollRight = false;
    let resizeObserver;
    let mutationObserver;

    const updateNavigation = () => {
        if (!container) return;

        canScrollLeft = container.scrollLeft > 1;
        canScrollRight = container.scrollLeft + container.clientWidth < container.scrollWidth - 1;
    };

    const scrollBehavior = () => {
        if (typeof window === "undefined") return "auto";

        return window.matchMedia("(prefers-reduced-motion: reduce)").matches
            ? "auto"
            : "smooth";
    };

    const scroll = (direction) => {
        if (!container) return;

        container.scrollBy({
            left: direction === "left"
                ? -container.clientWidth * scrollAmount
                : container.clientWidth * scrollAmount,
            behavior: scrollBehavior(),
        });
    };

    const scrollToEdge = (edge) => {
        if (!container) return;

        container.scrollTo({
            left: edge === "start" ? 0 : container.scrollWidth,
            behavior: scrollBehavior(),
        });
    };

    const handleKeydown = (event) => {
        const actions = {
            ArrowLeft: () => scroll("left"),
            ArrowRight: () => scroll("right"),
            Home: () => scrollToEdge("start"),
            End: () => scrollToEdge("end"),
        };

        if (!actions[event.key]) return;

        event.preventDefault();
        actions[event.key]();
    };

    const observeSizes = () => {
        if (!container || !resizeObserver) return;

        resizeObserver.disconnect();
        resizeObserver.observe(container);

        Array.from(container.children).forEach((item) => {
            resizeObserver.observe(item);
        });
    };

    onMount(() => {
        tick().then(() => {
            resizeObserver = new ResizeObserver(updateNavigation);
            mutationObserver = new MutationObserver(() => {
                observeSizes();
                updateNavigation();
            });

            observeSizes();
            mutationObserver.observe(container, { childList: true });
            updateNavigation();
        });

        return () => {
            resizeObserver?.disconnect();
            mutationObserver?.disconnect();
        };
    });
</script>

<div {...$$restProps} class={["relative min-w-0 w-full max-w-full overflow-hidden xl:overflow-visible", className]}>
    {#if canScrollLeft}
        <div class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 flex w-14 items-center justify-center xl:-left-9 xl:w-22 2xl:-left-12">
            <button
                type="button"
                aria-label="Voltar no carrossel"
                class="pointer-events-auto flex h-14 w-14 cursor-pointer items-center justify-center rounded-full border-8 border-blue-marinho bg-orange-citric xl:h-18 xl:w-18 xl:border-10"
                on:keydown={handleKeydown}
                on:click={() => scroll("left")}
            >
                <img
                    src="/svg/chevron-left.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 pr-[0.1rem] filter-blue-marinho xl:w-auto"
                    loading="lazy"
                />
            </button>
        </div>
    {/if}

    <div
        bind:this={container}
        class="carousel-scroll flex min-w-0 w-full max-w-full flex-nowrap gap-5 overflow-x-auto overscroll-x-contain scroll-smooth"
        role="region"
        aria-roledescription="carrossel"
        aria-label={label}
        on:scroll={updateNavigation}
    >
        <slot />
    </div>

    {#if canScrollRight}
        <div class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 flex w-14 items-center justify-center xl:-right-9 xl:w-22 2xl:-right-12">
            <button
                type="button"
                aria-label="Avançar no carrossel"
                class="pointer-events-auto flex h-14 w-14 cursor-pointer items-center justify-center rounded-full border-8 border-blue-marinho bg-orange-citric xl:h-18 xl:w-18 xl:border-10"
                on:keydown={handleKeydown}
                on:click={() => scroll("right")}
            >
                <img
                    src="/svg/chevron-right.svg"
                    alt=""
                    aria-hidden="true"
                    class="w-8 pl-[0.1rem] filter-blue-marinho xl:w-auto"
                    loading="lazy"
                />
            </button>
        </div>
    {/if}
</div>

<style>
    .carousel-scroll {
        scroll-snap-type: x proximity;
    }

    :global(.carousel-scroll > *) {
        scroll-snap-align: start;
    }

    @media (prefers-reduced-motion: reduce) {
        .carousel-scroll {
            scroll-behavior: auto;
        }
    }
</style>
