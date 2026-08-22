<script>
    let className;
    export { className as class };
    export let id;
    export let label;
    export let value;
    export let group = null;
    export let error = null;

    $: classes = [
        "size-4 cursor-pointer accent-orange-amber focus:ring-orange-amber",
        error ? "outline outline-red-crimson" : "",
        className,
    ];
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
    <label for={id} class="cursor-pointer font-noto-sans text-sm text-suspense-aurora">
        {label}
    </label>
</div>
