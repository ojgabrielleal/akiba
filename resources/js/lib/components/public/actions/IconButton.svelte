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
    export let interactive = true;

    import { Link } from "@inertiajs/svelte";
    import { publicAnimations } from "@/lib/constants";
    import MaskIcon from "../media/MaskIcon.svelte";
    import Tooltip from "../overlays/Tooltip.svelte";

    const variants = {
        close: { icon: "/svg/close.svg", tone: "dark", surface: "light" },
        edit: { icon: "/svg/edit.svg", tone: "accent", surface: "ocean" },
        play: { icon: "/svg/play.svg", tone: "dark", surface: "accent" },
        pause: { icon: "/svg/pause.svg", tone: "dark", surface: "primary" },
        menu: { icon: "/svg/menu.svg", tone: "light", surface: "transparent" },
        next: { icon: "/svg/chevron-right.svg", tone: "dark", surface: "accent" },
        previous: { icon: "/svg/chevron-left.svg", tone: "dark", surface: "accent" },
        reply: { icon: "/svg/reply.svg", tone: "accent", surface: "ocean" },
        trash: { icon: "/svg/trash.svg", tone: "accent", surface: "ocean" },
    };

    const tones = {
        accent: "text-orange-amber",
        primary: "text-blue-skywave",
        light: "text-suspense-aurora",
        neutral: "text-neutral-gray",
        dark: "text-blue-marinho",
        fixed: "",
    };

    const surfaces = {
        transparent: "bg-transparent",
        dark: "bg-blue-night",
        ocean: "bg-blue-ocean",
        light: "bg-suspense-aurora",
        accent: "bg-orange-amber",
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
        "inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full disabled:cursor-not-allowed disabled:opacity-50",
        interactive ? "hover:brightness-110" : "",
        interactive ? publicAnimations.iconButtonInteractive : "",
        selectedSize.button,
        surfaces[selectedSurface] ?? surfaces.transparent,
        className,
    ];
</script>

<Tooltip position={tooltipPosition}>
    {#if href}
        <Link {...$$restProps} {href} aria-label={label} class={classes} on:click>
            <MaskIcon icon={selectedIcon} class={[selectedSize.icon, tones[selectedTone], iconClass]} />
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
            <MaskIcon icon={selectedIcon} class={[selectedSize.icon, tones[selectedTone], iconClass]} />
        </button>
    {/if}
    <span slot="content">{label}</span>
</Tooltip>
