<script>
    let className;
    export { className as class };
    export let items = [];
    export let active = null;
    export let ariaLabel = "Navegação por categorias";
    export let variant = "underline";

    import { Link } from "@inertiajs/svelte";
    import { themeClass } from "@/lib/utils";

    const variants = {
        underline: {
            list: "border-b border-suspense-aurora/10",
            base: `border-b-2 border-transparent ${themeClass("text", "suspense-aurora/45", { fixed: true })}`,
            active: "border-orange-amber text-orange-amber",
        },
        pills: {
            list: "",
            base: `rounded-full bg-blue-ocean ${themeClass("text", "suspense-aurora/60", { fixed: true })}`,
            active: `bg-blue-skywave ${themeClass("text", "suspense-aurora", { fixed: true })}`,
        },
    };

    $: selectedVariant = variants[variant] ?? variants.underline;
    $: classes = [
        "flex max-w-full items-center justify-center overflow-x-auto",
        selectedVariant.list,
        className,
    ];
</script>

<nav aria-label={ariaLabel}>
    <ul class={classes}>
        {#each items as item}
            <li class="shrink-0">
                {#if item.href}
                    <Link
                        href={item.href}
                        aria-current={active === item.value ? "page" : undefined}
                        class={[
                            "flex min-h-10 items-center gap-2 px-4 font-noto-sans text-xs font-extrabold uppercase italic transition hover:text-orange-amber",
                            selectedVariant.base,
                            { [selectedVariant.active]: active === item.value },
                        ]}
                    >
                        {#if item.icon}
                            <img src={item.icon} alt="" aria-hidden="true" class="size-4" />
                        {/if}
                        {item.label}
                    </Link>
                {:else}
                    <button
                        type="button"
                        aria-pressed={active === item.value}
                        class={[
                            "flex min-h-10 cursor-pointer items-center gap-2 px-4 font-noto-sans text-xs font-extrabold uppercase italic transition hover:text-orange-amber",
                            selectedVariant.base,
                            { [selectedVariant.active]: active === item.value },
                        ]}
                        on:click={() => (active = item.value)}
                    >
                        {#if item.icon}
                            <img src={item.icon} alt="" aria-hidden="true" class="size-4" />
                        {/if}
                        {item.label}
                    </button>
                {/if}
            </li>
        {/each}
    </ul>
</nav>
