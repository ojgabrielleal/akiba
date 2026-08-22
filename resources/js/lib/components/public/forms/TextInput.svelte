<script>
    let className;
    export { className as class };
    export let id;
    export let type = "text";
    export let value = null;
    export let error = null;
    export let variant = "light";

    const variants = {
        light: "h-11 rounded-md bg-neutral-gray text-suspense-aurora placeholder:text-suspense-aurora/45",
        dark: "h-11 rounded-md bg-blue-ocean text-suspense-aurora placeholder:text-suspense-aurora/35",
        transparent: "h-11 rounded-md bg-transparent text-suspense-aurora placeholder:text-suspense-aurora/35",
        pill: "h-11 rounded-full bg-neutral-gray text-suspense-aurora placeholder:text-suspense-aurora/45",
        profile: "h-11 rounded-md bg-neutral-white text-blue-night placeholder:text-blue-night/35",
    };

    const borders = {
        light: "border border-suspense-aurora/20 focus:border-blue-skywave",
        dark: "border border-blue-skywave/40 focus:border-blue-skywave",
        transparent: "border border-suspense-aurora/25 focus:border-orange-amber",
        pill: "border border-transparent focus:border-blue-skywave",
        profile: "border border-blue-night/15 focus:border-blue-skywave",
    };

    $: classes = [
        "w-full px-4 font-noto-sans text-sm outline-none transition",
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
