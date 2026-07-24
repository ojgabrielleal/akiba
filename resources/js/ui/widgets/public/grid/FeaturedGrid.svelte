<script>
    import { Link } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";

    const placeholders = [
        {
            image: "/img/locution/characters/mya-nee.webp",
            title: "Quem fez esse bagulho se parecer com outro bagulho que parece com esse bagulho? Que doido",
        },
        {
            image: "/img/locution/characters/asuza.webp",
            title: "Quem fez esse bagulho se parecer com outro bagulho que parece com esse bagulho? Que doido",
        },
        {
            image: "/img/locution/characters/rin.webp",
            title: "Quem fez esse bagulho se parecer com outro bagulho que parece com esse bagulho? Que doido",
        },
    ];
</script>

<Section title="Destaques da Akiba">
    <ul class="carousel-scroll mt-10 -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-3">
        {#each placeholders as item}
            <li class="w-[82vw] max-w-95 shrink-0 snap-start sm:w-auto sm:max-w-none">
                <Link href="#" aria-label={`Ler destaque: ${item.title}`} class="group block focus-visible:outline-none">
                    <article class="relative mt-8 h-40 rounded-md bg-gradient-blue-cerulean-glow transition duration-300 ease-out group-hover:-translate-y-1 group-hover:shadow-lg group-hover:shadow-blue-skywave/10 group-focus-visible:-translate-y-1 group-focus-visible:ring-2 group-focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none">
                        <h3 class="relative z-10 line-clamp-6 w-[58%] p-2 pr-3 font-noto-sans text-lg leading-[1.2] font-extrabold text-suspense-aurora uppercase italic sm:text-xl">
                            {item.title}
                        </h3>
                        <img
                            src={item.image}
                            alt=""
                            aria-hidden="true"
                            class="absolute right-0 bottom-0 z-20 h-[12.75rem] w-[55%] max-w-none object-contain object-right-bottom"
                        />
                    </article>
                </Link>
            </li>
        {/each}
    </ul>
</Section>
