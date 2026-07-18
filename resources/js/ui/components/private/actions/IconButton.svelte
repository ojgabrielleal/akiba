<script>
    export let variant = "edit";
    export let label;
    export let href = null;
    export let type = "button";
    export let size = "md";
    export let disabled = false;
    export let tooltipPosition = "top";
    let className = "";
    export { className as class };
    export let icon = null;
    export let tone = null;
    export let surface = null;

    import { Link } from "@inertiajs/svelte";
    import Tooltip from "../Tooltip.svelte";

    const variants = {
        edit: {
            icon: "/svg/edit.svg",
            tone: "accent",
            surface: "default",
        },
        trash: {
            icon: "/svg/trash.svg",
            tone: "danger",
            surface: "default",
        },
        plus: {
            icon: "/svg/plus.svg",
            tone: "light",
            surface: "default",
        },
        close: {
            icon: "/svg/close.svg",
            tone: "light",
            surface: "dark",
        },
        verify: {
            icon: "/svg/verify.svg",
            tone: "light",
            surface: "default",
        },
        eye: {
            icon: "/svg/eye.svg",
            tone: "light",
            surface: "default",
        },
        download: {
            icon: "/svg/download.svg",
            tone: "primary",
            surface: "transparent",
        },
        crown: {
            icon: "/svg/crown.svg",
            tone: "dark",
            surface: "light",
        },
    };

    const tones = {
        accent: "filter-orange-citric",
        danger: "filter-red-crimson",
        light: "filter-suspense-aurora",
        primary: "filter-blue-skywave",
        dark: "filter-blue-marinho",
    };

    const surfaces = {
        default: "bg-blue-marinho",
        dark: "bg-blue-night",
        light: "bg-suspense-aurora",
        transparent: "bg-transparent",
        danger: "bg-red-crimson",
        ocean: "bg-blue-ocean",
    };

    const sizes = {
        sm: {
            button: "size-7",
            icon: "size-4",
        },
        md: {
            button: "size-8",
            icon: "size-4",
        },
        lg: {
            button: "size-10",
            icon: "size-5",
        },
    };

    $: selectedVariant = variants[variant];
    $: selectedIcon = icon ?? selectedVariant.icon;
    $: selectedTone = tone ?? selectedVariant.tone;
    $: selectedSurface = surface ?? selectedVariant.surface;
    $: selectedSize = sizes[size];
    
    $: classes = [
        "inline-flex shrink-0 cursor-pointer items-center justify-center rounded-md transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50",
        selectedSize.button,
        surfaces[selectedSurface],
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
                class={[selectedSize.icon, tones[selectedTone]]}
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
                class={[selectedSize.icon, tones[selectedTone]]}
                loading="lazy"
            />
        </button>
    {/if}
    <span slot="content">{label}</span>
</Tooltip>
