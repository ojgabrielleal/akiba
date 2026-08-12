<script>
    export let image = null;
    export let href = null;
    export let alt = "Anúncio";
    export let mirrored = false;
    let className = "";
    export { className as class };
</script>

<svelte:element
    this={href ? "a" : "div"}
    {href}
    target={href ? "_blank" : undefined}
    rel={href ? "noopener noreferrer sponsored" : undefined}
    class={[
        "group relative block h-32 cursor-pointer overflow-hidden border border-blue-skywave/25 bg-blue-ocean/20 transition duration-300 ease-out hover:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none [clip-path:polygon(0_0,96%_0,100%_18%,100%_100%,4%_100%,0_82%)]",
        className,
    ]}
    aria-label={href ? alt : undefined}
>
    {#if image}
        <img
            src={image}
            {alt}
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
        />
        <span class="absolute top-2 right-3 rounded-full bg-blue-night/75 px-2 py-0.5 font-noto-sans text-[0.5rem] font-extrabold tracking-widest text-suspense-aurora/70 uppercase backdrop-blur-sm">
            Anúncio
        </span>
    {:else}
        <div
            class="absolute inset-0 opacity-25"
            style="background-image: radial-gradient(circle, var(--color-blue-skywave) 1px, transparent 1px); background-size: 9px 9px;"
        ></div>
        <div class={[
            "absolute top-0 h-full w-24 skew-x-[-18deg] bg-orange-morning/10",
            mirrored ? "-left-8" : "-right-8",
        ]}></div>
        <div class={[
            "absolute top-1/2 -translate-y-1/2 font-noto-sans text-8xl font-black italic text-blue-skywave/10",
            mirrored ? "left-7" : "right-7",
        ]}>
            広告
        </div>

        <div class="absolute top-3 left-4 flex items-center gap-1.5">
            <span class="h-px w-8 bg-orange-morning"></span>
            <span class="font-noto-sans text-[0.55rem] font-extrabold tracking-[0.2em] text-orange-morning uppercase">
                Akiba Anúncios
            </span>
        </div>

        <div class="relative flex h-full items-center justify-center gap-3 pt-3">
            <div class="relative flex size-10 rotate-[-8deg] items-center justify-center">
                <img
                    src="/svg/star.svg"
                    alt=""
                    aria-hidden="true"
                    class="absolute size-10 filter-blue-skywave opacity-80"
                />
                <span class="relative font-noto-sans text-sm font-black text-blue-night">
                    AD
                </span>
            </div>
            <div class="font-noto-sans uppercase italic">
                <p class="text-[0.6rem] font-extrabold tracking-[0.15em] text-blue-skywave">
                    アキバで宣伝しよう！
                </p>
                <p class="text-xl font-black text-suspense-aurora">
                    Anuncie <span class="text-orange-morning">aqui!</span>
                </p>
            </div>
        </div>
    {/if}
</svelte:element>
