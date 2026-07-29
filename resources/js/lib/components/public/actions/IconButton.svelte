<script>
    let className;
    export { className as class };
    export let variant = "menu";
    export let label;
    export let href = null;
    export let type = "button";
    export let size = "md";
    export let disabled = false;
    export let tooltipPosition = "top";
    export let icon = null;
    export let iconClass = "";
    export let tone = null;
    export let surface = null;

    import { Link } from "@inertiajs/svelte";
    import Tooltip from "../overlays/Tooltip.svelte";

    const variants = {
        close: { icon: "/svg/close.svg", tone: "dark", surface: "light" },
        play: { icon: "/svg/play.svg", tone: "dark", surface: "accent" },
        pause: { icon: "/svg/pause.svg", tone: "dark", surface: "primary" },
        menu: { icon: "/svg/menu.svg", tone: "light", surface: "transparent" },
        next: { icon: "/svg/chevron-right.svg", tone: "dark", surface: "accent" },
        previous: { icon: "/svg/chevron-left.svg", tone: "dark", surface: "accent" },
    };

    const tones = {
        accent: "filter-orange-citric",
        primary: "filter-blue-skywave",
        light: "filter-suspense-aurora",
        neutral: "filter-neutral-gray",
        dark: "filter-blue-marinho",
    };

    const surfaces = {
        transparent: "bg-transparent",
        dark: "bg-blue-night",
        ocean: "bg-blue-ocean",
        light: "bg-suspense-aurora",
        accent: "bg-orange-citric",
        primary: "bg-blue-skywave",
    };

    const sizes = {
        sm: { button: "size-8", icon: "size-4" },
        md: { button: "size-10", icon: "size-5" },
        lg: { button: "size-12", icon: "size-6" },
        hero: { button: "size-16", icon: "size-7" },
    };

    $: selectedVariant = variants[variant] ?? variants.menu;
    $: selectedIcon = icon ?? selectedVariant.icon;
    $: selectedTone = tone ?? selectedVariant.tone;
    $: selectedSurface = surface ?? selectedVariant.surface;
    $: selectedSize = sizes[size] ?? sizes.md;
    $: classes = [
        "inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full transition hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50",
        selectedSize.button,
        surfaces[selectedSurface] ?? surfaces.transparent,
        className,
    ];
</script>

<Tooltip position={tooltipPosition}>
    {#if href}
        <Link {...$$restProps} {href} aria-label={label} class={classes} on:click>
            <img
                src={selectedIcon}
                alt=""
                aria-hidden="true"
                class={[selectedSize.icon, tones[selectedTone], iconClass]}
                loading="lazy"
            />
        </Link>
    {:else}
        <button
            {...$$restProps}
            {type}
            aria-label={label}
            class={classes}
            {disabled}
            on:click
        >
            <img
                src={selectedIcon}
                alt=""
                aria-hidden="true"
                class={[selectedSize.icon, tones[selectedTone], iconClass]}
                loading="lazy"
            />
        </button>
    {/if}
    <span slot="content">{label}</span>
</Tooltip>
