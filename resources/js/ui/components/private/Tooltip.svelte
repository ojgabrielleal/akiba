<script>
    export let position = "top";

    const positions = {
        top: {
            content: "bottom-full left-1/2 -translate-x-1/2 mb-2",
            arrow: "top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-neutral-900/90",
        },
        bottom: {
            content: "top-full left-1/2 -translate-x-1/2 mt-2",
            arrow: "bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-neutral-900/90",
        },
        left: {
            content: "right-full top-1/2 -translate-y-1/2 mr-2",
            arrow: "left-full top-1/2 -translate-y-1/2 border-4 border-transparent border-l-neutral-900/90",
        },
        right: {
            content: "left-full top-1/2 -translate-y-1/2 ml-2",
            arrow: "right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-neutral-900/90",
        },
    };

    $: tooltipPosition = positions[position] ?? positions.top;
</script>

<div class="relative group/tooltip inline-flex">
    <slot />
    <div class={["absolute px-2 py-1 bg-neutral-900/90 backdrop-blur-sm text-white text-[10px] font-medium rounded-md invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 transition-all duration-200 whitespace-nowrap z-50 pointer-events-none border border-white/10 shadow-xl",
        tooltipPosition.content,
    ]}>
        <slot name="content" />
        <div class={["absolute",
            tooltipPosition.arrow,
        ]}></div>
    </div>
</div>
