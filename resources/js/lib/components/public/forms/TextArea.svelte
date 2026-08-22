<script>
    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "light";
    export let resize = "vertical";

    const variants = {
        light: "bg-neutral-gray text-suspense-aurora placeholder:text-suspense-aurora/45",
        dark: "bg-blue-ocean text-suspense-aurora placeholder:text-suspense-aurora/35",
        transparent: "bg-transparent text-suspense-aurora placeholder:text-suspense-aurora/35",
        profile: "bg-neutral-white text-blue-night placeholder:text-blue-night/35",
    };

    const borders = {
        light: "border border-suspense-aurora/20 focus:border-blue-skywave",
        dark: "border border-blue-skywave/40 focus:border-blue-skywave",
        transparent: "border border-suspense-aurora/25 focus:border-orange-citric",
        profile: "border border-blue-night/15 focus:border-blue-skywave",
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
