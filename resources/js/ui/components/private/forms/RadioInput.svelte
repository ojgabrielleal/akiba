<script>
    let className;
    export { className as class };
    export let id;
    export let label;
    export let value;
    export let group = null;
    export let error = null;

    $: classes = [
        "size-4 cursor-pointer text-blue-skywave focus:ring-blue-skywave",
        error ? "border-red-crimson" : "border-gray-300",
        className,
    ];

    const labelClasses = "cursor-pointer font-noto-sans text-md text-gray-700";
</script>

<div class="flex items-center gap-2">
    <input
        {...$$restProps}
        {id}
        {value}
        type="radio"
        class={classes}
        bind:group
    />
    <label for={id} class={labelClasses}>
        {label}
    </label>
</div>
