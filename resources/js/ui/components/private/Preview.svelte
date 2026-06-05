<script>
    export let name = null;
    export let size = "default";
    export let tone = "default";
    export let color = "default";

    export let src = null;
    export let oninput = null;
    export let required = false;
    export let disabled = false;

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
    };

    const tones = {
        default: "bg-blue-ocean border border-blue-skywave",
        muted: "bg-suspense-aurora",
    };

    const colors = {
        default: "text-orange-citric",
        muted: "text-blue-ocean",
        light: "text-suspense-aurora",
    };

    let preview = null;

    $: imageToShow = preview ?? (src && src !== "#" ? src : null);
    $: selectedSize = sizes[size] ?? sizes.default;
    $: selectedTone = tones[tone] ?? tones.default;
    $: selectedColor = colors[color] ?? colors.default;
    $: placeholderCSS = `${selectedSize.frame} ${selectedTone} ${selectedColor} w-full flex items-center justify-center overflow-hidden font-noto-sans text-7xl font-extrabold italic uppercase`;
    $: previewCSS = `${selectedSize.image} ${selectedTone} w-full object-top object-cover`;

    const previewImage = (event) => {
        if (disabled) {
            return;
        }

        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview = e.target.result;
            };
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
        {name}
        class="sr-only"
        accept="image/*"
        {required}
        {disabled}
        on:input={oninput}
        on:change={previewImage}
    />
</label>
