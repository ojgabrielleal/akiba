<script>
    import { createEventDispatcher } from "svelte";

    export let themes = [];
    export let selectedTheme = "akiba";
    export let size = "sm";

    let className = "";
    export { className as class };

    const dispatch = createEventDispatcher();

    const sizes = {
        sm: {
            wrapper: "h-[1.625rem]",
            button: "size-[1.375rem]",
            icon: "size-[0.8125rem]",
        },
        md: {
            wrapper: "h-7",
            button: "size-6",
            icon: "size-3",
        },
    };

    $: selectedSize = sizes[size] ?? sizes.sm;
</script>

<div class={["flex shrink-0 items-center rounded-full bg-blue-skywave p-0.5", selectedSize.wrapper, className]} aria-label="Selecionar tema">
    {#each themes as item}
        <button
            type="button"
            aria-label={item.label}
            aria-pressed={selectedTheme === item.name}
            class={[
                "flex cursor-pointer items-center justify-center rounded-full transition duration-200 ease-out hover:scale-110 active:scale-95 motion-reduce:transform-none motion-reduce:transition-none",
                selectedSize.button,
                { "bg-blue-night": selectedTheme === item.name },
            ]}
            on:click={() => dispatch("select", item.name)}
        >
            <img
                src={item.icon}
                alt=""
                aria-hidden="true"
                class={[
                    "filter-suspense-aurora",
                    selectedSize.icon,
                    { "filter-orange-morning": selectedTheme === item.name },
                ]}
            />
        </button>
    {/each}
</div>
