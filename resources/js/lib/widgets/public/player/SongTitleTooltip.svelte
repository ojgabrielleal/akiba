<script>
    export let music = null;
    export let titleClass = "";

    let container;
    let open = false;

    $: title = music || "Estamos offline";

    const toggle = () => {
        open = !open;
    };

    const closeFromOutside = (event) => {
        if (open && !container?.contains(event.target)) {
            open = false;
        }
    };

    const closeFromKeyboard = (event) => {
        if (event.key === "Escape") {
            open = false;
        }
    };
</script>

<svelte:window on:click={closeFromOutside} on:keydown={closeFromKeyboard} />

<div bind:this={container} class="group/song-title relative min-w-0">
    <button
        type="button"
        class="block w-full min-w-0 cursor-pointer text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric"
        aria-label={`Nome completo da música: ${title}`}
        aria-expanded={open}
        on:click|stopPropagation={toggle}
    >
        <span class={titleClass}>
            {title}
        </span>
    </button>
    <div role="tooltip" class={["pointer-events-none absolute bottom-full left-0 z-50 mb-2 w-max max-w-[min(22rem,calc(100vw-2rem))] rounded-md border border-suspense-aurora/10 bg-blue-ocean px-3 py-2 font-noto-sans text-xs font-normal leading-snug text-suspense-aurora normal-case shadow-xl transition-opacity group-hover/song-title:visible group-hover/song-title:opacity-100 group-focus-within/song-title:visible group-focus-within/song-title:opacity-100 not-italic", open ? "visible opacity-100" : "invisible opacity-0"]}>
        {title}
        <span aria-hidden="true" class="absolute top-full left-5 border-6 border-transparent border-t-blue-ocean"></span>
    </div>
</div>
