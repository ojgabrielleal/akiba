<script>
    export let position = "top";

    const positions = {
        top: {
            content: "bottom-full left-1/2 -translate-x-1/2 mb-2",
            arrow: "top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-blue-ocean",
        },
        bottom: {
            content: "top-full left-1/2 -translate-x-1/2 mt-2",
            arrow: "bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-blue-ocean",
        },
        left: {
            content: "right-full top-1/2 -translate-y-1/2 mr-2",
            arrow: "left-full top-1/2 -translate-y-1/2 border-4 border-transparent border-l-blue-ocean",
        },
        right: {
            content: "left-full top-1/2 -translate-y-1/2 ml-2",
            arrow: "right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-blue-ocean",
        },
    };

    $: tooltipPosition = positions[position] ?? positions.top;
</script>

<div class="group/tooltip relative inline-flex">
    <slot />
    <div class={[
        "pointer-events-none absolute z-50 hidden whitespace-nowrap rounded-md border border-suspense-aurora/10 bg-blue-ocean px-2 py-1 font-noto-sans text-[10px] text-suspense-aurora opacity-0 shadow-xl transition-all group-hover/tooltip:visible group-hover/tooltip:opacity-100 group-focus-within/tooltip:visible group-focus-within/tooltip:opacity-100 lg:block",
        tooltipPosition.content,
    ]}>
        <slot name="content" />
        <div class={["absolute", tooltipPosition.arrow]}></div>
    </div>
</div>
