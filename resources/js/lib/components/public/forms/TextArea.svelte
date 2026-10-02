<script>
    import { themeClass } from "@/lib/utils";

    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "light";
    export let resize = "vertical";

    const variants = {
        light: `bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/45`,
        dark: `bg-blue-ocean ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/35 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))]`,
        transparent: "bg-transparent text-suspense-aurora placeholder:text-suspense-aurora/35",
        profile: `bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/45 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:placeholder:text-suspense-aurora/35 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:placeholder:text-suspense-aurora/35`,
    };

    const borders = {
        light: "border border-suspense-aurora/20 focus:border-blue-skywave",
        dark: "border border-blue-skywave/40 focus:border-blue-skywave [[data-public-theme=akiba]_&]:border-0 [[data-public-theme=night]_&]:border-0",
        transparent: "border border-suspense-aurora/25 focus:border-orange-amber",
        profile: "border-0",
    };

    const resizeModes = {
        none: "resize-none",
        vertical: "resize-y",
    };

    $: classes = [
        "min-h-28 w-full rounded-md px-4 py-3 font-noto-sans text-sm outline-none transition",
        variants[variant] ?? variants.light,
        error ? "border border-red-crimson" : borders[variant] ?? borders.light,
        resizeModes[resize] ?? resizeModes.vertical,
        className,
    ];
</script>

<textarea
    {...$$restProps}
    {id}
    bind:value
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : undefined}
    class={classes}
></textarea>
