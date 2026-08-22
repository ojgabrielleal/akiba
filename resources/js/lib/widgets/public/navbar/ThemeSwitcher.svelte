<script>
    export let themes = [];
    export let selectedTheme = "akiba";
    export let size = "sm";
    export let onSelect = () => {};

    let className = "";
    export { className as class };

    const sizes = {
        sm: {
            wrapper: "h-7",
            button: "size-6",
            icon: "size-3.5",
        },
        md: {
            wrapper: "h-8",
            button: "size-7",
            icon: "size-4",
        },
    };

    const selectTheme = (theme) => {
        onSelect(theme.name);
    };

    $: selectedSize = sizes[size] ?? sizes.sm;
</script>

<div class={["theme-switcher flex shrink-0 items-center -space-x-0.5 rounded-full p-px", selectedSize.wrapper, className]} aria-label="Selecionar tema">
    {#each themes as item}
        <button
            type="button"
            aria-label={item.label}
            aria-pressed={selectedTheme === item.name}
            class={[
                "theme-switcher-button flex cursor-pointer items-center justify-center rounded-full transition duration-200 ease-out hover:scale-110 active:scale-95 motion-reduce:transform-none motion-reduce:transition-none",
                selectedSize.button,
            ]}
            class:theme-switcher-button-selected={selectedTheme === item.name && item.name !== "akiba"}
            class:theme-switcher-button-night-selected={selectedTheme === item.name && item.name === "night"}
            on:click={() => selectTheme(item)}
        >
            <img
                src={item.icon}
                alt=""
                aria-hidden="true"
                class={[
                    "theme-switcher-icon",
                    selectedSize.icon,
                ]}
                class:theme-switcher-icon-selected={selectedTheme === item.name && item.name !== "akiba"}
                class:theme-switcher-icon-night-selected={selectedTheme === item.name && item.name === "night"}
                class:theme-switcher-icon-akiba={item.name === "akiba"}
            />
        </button>
    {/each}
</div>

<style>
    .theme-switcher {
        background-color: #0091ff;
    }

    .theme-switcher-button {
        background-color: transparent;
    }

    .theme-switcher-button-selected {
        background-color: #fffaf3;
    }

    .theme-switcher-button-night-selected {
        background-color: #000014;
    }

    .theme-switcher-icon {
        filter: invert(93%) sepia(32%) saturate(268%) hue-rotate(331deg) brightness(115%) contrast(104%);
    }

    .theme-switcher-icon-selected {
        filter: invert(8%) sepia(27%) saturate(5191%) hue-rotate(227deg) brightness(96%) contrast(124%);
    }

    .theme-switcher-icon-night-selected {
        filter: invert(93%) sepia(32%) saturate(268%) hue-rotate(331deg) brightness(115%) contrast(104%);
    }

    .theme-switcher-icon-akiba {
        filter: invert(93%) sepia(32%) saturate(268%) hue-rotate(331deg) brightness(115%) contrast(104%);
    }
</style>
