<script>
    export let name = null;
    export let size = "default";
    export let tone = "default";
    export let color = "default";

    export let src = null;
    export let oninput = null;
    export let onchange = null;
    export let required = false;
    export let disabled = false;
    export let error = null;

    const sizes = {
        default: {
            frame: "h-[18rem] rounded-md",
            image: "max-h-[18rem] rounded-md",
        },
        featured: {
            frame: "h-[18rem] rounded-md",
            image: "h-[18rem] rounded-md",
        },
        compact: {
            frame: "h-[10rem] rounded-md",
            image: "h-[10rem] rounded-md",
        },
        profile: {
            frame: "h-[15rem] rounded-md",
            image: "h-[15rem] rounded-md",
        },
        thumb: {
            frame: "w-24 h-24 rounded-md",
            image: "w-24 h-24 rounded-md",
        },
        icon: {
            frame: "h-20 w-20 rounded-md",
            image: "h-20 w-20 rounded-md",
        },
    };

    const tones = {
        default: "bg-blue-ocean border border-blue-skywave",
        muted: "bg-suspense-aurora",
    };

    const colors = {
        default: "text-orange-amber",
        muted: "text-blue-ocean",
        light: "text-suspense-aurora",
    };

    let preview = null;

    $: imageToShow = preview ?? (src && src !== "#" ? src : null);
    $: selectedSize = sizes[size] ?? sizes.default;
    $: selectedTone = tones[tone] ?? tones.default;
    $: selectedColor = colors[color] ?? colors.default;

    $: errorClass = error ? "private-field-error" : "";
    $: placeholderCSS = `${selectedSize.frame} ${selectedTone} ${errorClass} ${selectedColor} w-full flex items-center justify-center overflow-hidden font-noto-sans text-7xl font-extrabold italic uppercase`;
    $: previewCSS = `${selectedSize.image} ${selectedTone} ${errorClass} w-full object-top object-contain`;

    const previewImage = (event) => {
        if (disabled) return;
        const file = event.target.files[0];

        onchange?.(event);

        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => preview = e.target.result;
            reader.readAsDataURL(file);
        } else {
            preview = null;
        }
    };
</script>

<label class={["block",
    { "cursor-pointer": !disabled },
    { "cursor-not-allowed opacity-60": disabled },
]}>
    {#if imageToShow}
        <img
            src={imageToShow}
            alt=""
            aria-hidden="true"
            class={previewCSS}
            loading="lazy"
        />
    {:else}
        <div class={placeholderCSS}>
            +
        </div>
    {/if}
    <input
        id={name}
        type="file"
        class="sr-only"
        accept="image/*"
        {name}
        {required}
        {disabled}
        aria-invalid={error ? "true" : undefined}
        aria-describedby={error ? `${name}-error` : undefined}
        on:input={oninput}
        on:change={previewImage}
    />
</label>

<style>
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }
</style>
