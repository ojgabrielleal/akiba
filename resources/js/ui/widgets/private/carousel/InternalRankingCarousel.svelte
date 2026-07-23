<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Carousel, EmptyState, Section } from "@/ui/components/private";
    import { resolvePlaceholderImage } from "@/utils";

    $: ({ ranking_interno: ranking } = $page.props);
    $: items = resolveItems(ranking);

    const formatNumber = (value) => Number(value ?? 0).toLocaleString("pt-BR");

    function resolveItems(data = {}) {
        data = data ?? {};

        return [
            data.redator_mais_ativo && {
                key: "redator-mais-ativo",
                title: "Redator mais ativo",
                name: data.redator_mais_ativo.usuario.nickname,
                image: resolvePlaceholderImage(
                    data.redator_mais_ativo.usuario.avatar,
                    "avatar",
                    data.redator_mais_ativo.usuario.gender,
                ),
                imageAlt: `Avatar de ${data.redator_mais_ativo.usuario.nickname}`,
                imageClass: "object-cover object-top",
                value: data.redator_mais_ativo.total,
                description: "Matérias criadas",
            },
            data.locutor_mais_ativo && {
                key: "locutor-mais-ativo",
                title: "Locutor mais ativo",
                name: data.locutor_mais_ativo.usuario.nickname,
                image: resolvePlaceholderImage(
                    data.locutor_mais_ativo.usuario.avatar,
                    "avatar",
                    data.locutor_mais_ativo.usuario.gender,
                ),
                imageAlt: `Avatar de ${data.locutor_mais_ativo.usuario.nickname}`,
                imageClass: "object-cover object-top",
                value: data.locutor_mais_ativo.total,
                description: "Programas feitos",
            },
            data.pedidos_atendidos && {
                key: "pedidos-atendidos",
                title: "Pedidos atendidos",
                name: "Pedidos musicais",
                showName: false,
                value: data.pedidos_atendidos.total,
                description: `No dia ${data.pedidos_atendidos.data}`,
            },
            data.pico_audiencia && {
                key: "pico-audiencia",
                title: "Pico de audiência",
                name: data.pico_audiencia.programa.nome,
                image: resolvePlaceholderImage(data.pico_audiencia.programa.imagem, "program"),
                imageAlt: `Imagem do programa ${data.pico_audiencia.programa.nome}`,
                imageClass: "object-contain p-5",
                showName: false,
                value: data.pico_audiencia.total,
                description: "Nesse programa",
            },
            data.maior_interacao && {
                key: "maior-interacao",
                title: "Maior interação",
                name: data.maior_interacao.titulo,
                image: resolvePlaceholderImage(data.maior_interacao.imagem, "placeholder"),
                imageAlt: `Imagem da matéria ${data.maior_interacao.titulo}`,
                imageClass: "object-cover",
                value: data.maior_interacao.total,
                description: "Nessa matéria",
            },
            data.enquete_mais_votada && {
                key: "enquete-mais-votada",
                title: "Enquete mais votada",
                name: data.enquete_mais_votada.pergunta,
                value: data.enquete_mais_votada.total,
                description: "Votos nessa enquete",
            },
        ].filter(Boolean);
    }
</script>

<Section {title}>
    {#if items.length}
        <Carousel class="internal-ranking-carousel" label={title} scrollAmount={0.9}>
            {#each items as item (item.key)}
                <article
                    class="flex h-57 w-42 shrink-0 flex-col overflow-hidden rounded-md bg-blue-ocean font-noto-sans sm:w-45"
                    aria-label={`${item.title}: ${formatNumber(item.value)} — ${item.description}`}
                >
                    <header class="flex h-7 shrink-0 items-center justify-center bg-blue-cerulean px-2 text-center text-[0.7rem] font-semibold uppercase text-suspense-aurora">
                        <span class="truncate">{item.title}</span>
                    </header>

                    <div class="relative min-h-0 flex-1 overflow-hidden">
                        {#if item.image}
                            <img
                                src={item.image}
                                alt={item.imageAlt}
                                aria-hidden={!item.imageAlt}
                                class={["h-full w-full", item.imageClass]}
                                loading="lazy"
                            />
                        {/if}
                        {#if item.showName !== false}
                            <div class="absolute inset-x-0 bottom-0 bg-blue-marinho px-2 py-1.5 text-center text-xs font-bold text-suspense-aurora">
                                <p class="truncate">{item.name}</p>
                            </div>
                        {/if}
                    </div>

                    <footer class="flex h-14 shrink-0 flex-col items-center justify-center bg-blue-cerulean px-2 text-center uppercase text-suspense-aurora">
                        <strong class="text-2xl font-black italic leading-none">
                            {formatNumber(item.value)}
                        </strong>
                        <span class="mt-1 truncate text-[0.65rem] font-semibold leading-none">
                            {item.description}
                        </span>
                    </footer>
                </article>
            {/each}
        </Carousel>
    {:else}
        <EmptyState
            title="Ranking ainda indisponível"
            description="Os destaques aparecerão quando houver dados suficientes."
        />
    {/if}
</Section>

<style>
    :global(.internal-ranking-carousel .carousel-scroll) {
        gap: 0.65rem;
    }
</style>
