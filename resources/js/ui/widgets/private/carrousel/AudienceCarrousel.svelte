<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Carrousel, Section } from "@/ui/components/private/";

    $: ({ audience } = $page.props);
</script>

<Section {title}>
    <Carrousel styles="mt-5">
        {#each audience as item}
            <article class="shrink-0 flex flex-col items-center justify-center gap-4 px-5 lg:first:pl-0 lg:border-r-2 lg:border-suspense-aurora/10 lg:last:border-0">
                <div class="">
                    <img
                        src={item.logo}
                        alt=""
                        aria-hidden="true"
                        loading="lazy"
                        class="w-15 h-15 object-contain rounded-md"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <span class="w-full text-center text-[10px] font-medium text-white/50 tracking-widest uppercase mb-1">
                        {item.nome}
                    </span>
                    <div class="flex items-center gap-1 font-black italic text-lg text-suspense-aurora uppercase">
                        <img
                            src="/svg/listeners.svg"
                            alt="Ouvintes"
                            class="w-5 h-5 shrink-0 filter-suspense-aurora"
                        />
                        <span>
                            {#if item.listeners === "offline"}
                                Fora do ar
                            {:else if item.listeners === "NaN"}
                                NaN
                            {:else if item.listeners === 0}
                                Sem Ouvintes
                            {:else}
                                {item.listeners} Ouvindo
                            {/if}
                        </span>
                    </div>
                </div>
            </article>
        {/each}
    </Carrousel>
</Section>
