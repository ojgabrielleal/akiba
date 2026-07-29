<script>
    let className;
    export { className as class };
    export let label = "Carrossel";
    export let scrollAmount = 0.8;

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

    const handleKeydown = (event) => {
        if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") return;

        event.preventDefault();
        scroll(event.key === "ArrowLeft" ? "left" : "right");
    };

    const observeSizes = () => {
        if (!container || !resizeObserver) return;

        resizeObserver.disconnect();
        resizeObserver.observe(container);
        Array.from(container.children).forEach((item) => resizeObserver.observe(item));
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

<div {...$$restProps} class={["relative min-w-0 w-full max-w-full overflow-hidden", className]}>
    {#if canScrollLeft}
        <button
            type="button"
            aria-label="Voltar no carrossel"
            class="absolute left-2 top-1/2 z-10 hidden size-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-orange-citric shadow-xl sm:flex"
            on:keydown={handleKeydown}
            on:click={() => scroll("left")}
        >
            <img src="/svg/chevron-left.svg" alt="" aria-hidden="true" class="size-6 filter-blue-marinho" />
        </button>
    {/if}

    <div
        bind:this={container}
        class="carousel-scroll flex min-w-0 w-full max-w-full flex-nowrap gap-4 overflow-x-auto overscroll-x-contain scroll-smooth"
        role="region"
        aria-roledescription="carrossel"
        aria-label={label}
        on:scroll={updateNavigation}
    >
        <slot />
    </div>

    {#if canScrollRight}
        <button
            type="button"
            aria-label="Avançar no carrossel"
            class="absolute right-2 top-1/2 z-10 hidden size-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-orange-citric shadow-xl sm:flex"
            on:keydown={handleKeydown}
            on:click={() => scroll("right")}
        >
            <img src="/svg/chevron-right.svg" alt="" aria-hidden="true" class="size-6 filter-blue-marinho" />
        </button>
    {/if}
</div>

<style>
    .carousel-scroll {
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .carousel-scroll::-webkit-scrollbar {
        display: none;
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
