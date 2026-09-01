<script>
    export let icon;

    let className = "";
    export { className as class };

    $: maskStyle = `mask-image: url('${icon}'); -webkit-mask-image: url('${icon}');`;
</script>

<span
    aria-hidden="true"
    class={[
        "inline-block shrink-0 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]",
        className,
    ]}
    style={maskStyle}
></span>
