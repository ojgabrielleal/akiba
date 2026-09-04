<script>
    import { themeClass } from "@/lib/utils";

    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "light";

    const variants = {
        light: `h-11 rounded-md bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora`,
        dark: `h-11 rounded-md bg-blue-ocean ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora`,
        transparent: "h-11 rounded-md bg-transparent text-suspense-aurora",
        pill: `h-11 rounded-full bg-neutral-gray ${themeClass("bg", "neutral-light", { fixed: true, theme: "light" })} text-suspense-aurora`,
    };

    const borders = {
        light: "border border-suspense-aurora/20 focus:border-blue-skywave",
        dark: "border border-blue-skywave/40 focus:border-blue-skywave",
        transparent: "border border-suspense-aurora/25 focus:border-orange-amber",
        pill: "border border-transparent focus:border-blue-skywave",
    };

    $: classes = [
        "w-full appearance-none px-4 pr-10 font-noto-sans text-sm outline-none transition",
        variants[variant] ?? variants.light,
        error ? "border border-red-crimson" : borders[variant] ?? borders.light,
        className,
    ];
</script>

<div class="relative">
    <select
        {...$$restProps}
        {id}
        aria-invalid={error ? "true" : undefined}
        aria-describedby={error ? `${id}-error` : undefined}
        class={classes}
        bind:value
    >
        <slot />
    </select>
    <img
        src="/svg/chevron-down.svg"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute top-1/2 right-3 size-5 -translate-y-1/2 opacity-80 filter-orange-amber"
    />
</div>
