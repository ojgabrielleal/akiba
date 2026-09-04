<script>
    let className;
    export { className as class };
    export let id;
    export let type = "text";
    export let value = null;
    export let error = null;
    export let variant = "default";

    const variants = {
        default: "h-10 rounded-md bg-white text-gray-900",
        offcanvas: "h-10 rounded-md bg-white text-gray-900",
        editorial: "h-12 rounded-md bg-blue-ocean text-suspense-aurora",
        profile: "h-12 rounded-md bg-suspense-aurora text-blue-marinho",
        pillLeft: "h-12 rounded-l-full bg-suspense-aurora text-blue-marinho",
        pillRight: "h-12 rounded-r-full bg-suspense-aurora text-blue-marinho",
    };

    const borders = {
        default: "border border-gray-400",
        offcanvas: "border border-gray-400",
        editorial: "border border-blue-skywave",
        profile: "border-0",
        pillLeft: "border-0 border-r border-blue-marinho",
        pillRight: "border-0",
    };

    $: classes = [
        "w-full pl-4 font-noto-sans text-md outline-none",
        type === "date" ? "akiba-date-input pr-3" : "",
        variants[variant] ?? variants.default,
        error ? "private-field-error" : borders[variant] ?? borders.default,
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
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }

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
