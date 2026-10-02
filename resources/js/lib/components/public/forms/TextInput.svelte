<script>
    import { themeClass } from "@/lib/utils";

    let className;
    export { className as class };
    export let id;
    export let type = "text";
    export let value = null;
    export let error = null;
    export let variant = "light";

    const variants = {
        light: `h-11 rounded-md bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/45`,
        dark: `h-11 rounded-md bg-blue-ocean ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/35 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))]`,
        transparent: "h-11 rounded-md bg-transparent text-suspense-aurora placeholder:text-suspense-aurora/35",
        pill: `h-11 rounded-full bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/45`,
        profile: `h-11 rounded-md bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora placeholder:text-suspense-aurora/45 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:placeholder:text-suspense-aurora/35 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:placeholder:text-suspense-aurora/35`,
    };

    const borders = {
        light: "border border-suspense-aurora/20 focus:border-blue-skywave",
        dark: "border border-blue-skywave/40 focus:border-blue-skywave [[data-public-theme=akiba]_&]:border-0 [[data-public-theme=night]_&]:border-0",
        transparent: "border border-suspense-aurora/25 focus:border-orange-amber",
        pill: "border border-transparent focus:border-blue-skywave",
        profile: "border-0",
    };

    $: classes = [
        "w-full px-4 font-noto-sans text-sm outline-none transition",
        type === "date" ? "akiba-date-input pr-3" : "",
        variants[variant] ?? variants.light,
        error ? "border border-red-crimson" : borders[variant] ?? borders.light,
        className,
    ];
</script>

<input
    {...$$restProps}
    {id}
    {type}
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : undefined}
    class={classes}
    bind:value
/>

<style>
    .akiba-date-input::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.85;
        filter: invert(51%) sepia(94%) saturate(1015%) hue-rotate(2deg) brightness(105%) contrast(104%);
    }

    .akiba-date-input:disabled::-webkit-calendar-picker-indicator {
        cursor: not-allowed;
        opacity: 0.45;
    }
</style>
