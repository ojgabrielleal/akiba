<script>
    import { page } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { EditorialTitle } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { RadioListenerMonthGrid, RadioProgramGrid, RadioRankingGrid } from "@/lib/widgets/public";

    $: ({
        flash,
        oauth,
        onair,
        stream,
        programs,
        ranking,
        listenerMonth,
        about,
    } = $page.props);
    $: pageUrl = $page.url;
    const aboutIntro =
        "A Akiba é uma emissora e rede de mídia otaku feita de fã para fã, voltada inteiramente aos apaixonados por animes e pela cultura japonesa no Brasil. Nascida do desejo de oferecer uma rádio ativa, moderna e com foco real em músicas orientais, o projeto combina transmissões ao vivo, interatividade constante e uma curadoria cuidadosa de conteúdo.";
    const aboutTimeline = [
        {
            date: "Maio de 2016",
            title: "O primeiro traço",
            description:
                "No dia 25 de maio, os idealizadores desenharam o conceito da rádio; no dia 26, o primeiro site do projeto foi construído.",
        },
        {
            date: "09 de Outubro de 2016",
            title: "A estreia no ar",
            description: "Estreia oficial das transmissões, dando início a uma jornada contínua no ar.",
        },
        {
            date: "Rede Akiba (2021)",
            title: "A marca vira rede",
            description:
                "Expansão da marca para fortalecer a presença nas redes sociais (com foco no Instagram e Facebook), além do lançamento de conteúdos em vídeo no YouTube, consolidando o projeto como mídia multiplataforma.",
        },
        {
            date: "Comemoração de 10 Anos (2026)",
            title: "Uma nova fase",
            description:
                "Anúncio da parceria com o CANAL START (emissora FAST Channel Gamer e Geek), viabilizando o compartilhamento de conteúdos audiovisuais e a cobertura de eventos em conjunto. Além disso, a nova fase da Akiba traz mais programação ao vivo, atualizações periódicas no portal e redes sociais.",
        },
    ];
    const aboutHighlights = [
        {
            label: "128 kbps (MP3)",
            description: "Transmissão estável em áudio digital",
        },
        {
            label: "Site oficial",
            description: "Ao vivo direto pela Akiba",
        },
        {
            label: "Agregadores web",
            description: "Disponível em dezenas de plataformas",
        },
    ];
    $: customAboutParagraphs = Array.isArray(about) ? about : null;
</script>

<Meta meta={{ title: "Rádio" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <h1 class="sr-only">Rádio</h1>
    <div class="public-page-background bg-blue-night pt-10">
        <RadioProgramGrid {programs} />
        <RadioRankingGrid {ranking} />
        <RadioListenerMonthGrid {listenerMonth} />

        <section class="bg-blue-marinho pb-8">
            <EditorialTitle title="Sobre nós" compact />
            <div class="container-page py-8">
                {#if customAboutParagraphs}
                    <div class="mx-auto max-w-5xl space-y-5 font-noto-sans text-base font-normal leading-7 text-suspense-aurora sm:text-lg sm:leading-8">
                        {#each customAboutParagraphs as paragraph}
                            <p>{paragraph}</p>
                        {/each}
                    </div>
                {:else}
                    <div class="mx-auto grid max-w-6xl gap-8 font-noto-sans text-suspense-aurora">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-end">
                            <p class="max-w-3xl text-base font-semibold leading-6 sm:text-lg sm:leading-7">
                                {aboutIntro}
                            </p>
                            <div class="relative overflow-hidden rounded-md border border-orange-citric/45 bg-blue-ocean/20 px-5 py-5">
                                <div class="absolute inset-y-0 left-0 w-1.5 bg-orange-citric" aria-hidden="true"></div>
                                <p class="pl-3 text-xs font-black uppercase italic text-orange-citric">Feita de fã para fã</p>
                                <p class="mt-2 pl-3 text-2xl font-black uppercase italic leading-none text-suspense-aurora">
                                    Rádio, vídeo e comunidade
                                </p>
                                <div class="mt-4 ml-3 grid grid-cols-3 gap-2" aria-hidden="true">
                                    <span class="h-1.5 rounded-full bg-orange-citric"></span>
                                    <span class="h-1.5 rounded-full bg-orange-citric"></span>
                                    <span class="h-1.5 rounded-full bg-blue-skywave"></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <h2 class="font-noto-sans text-2xl font-black uppercase italic text-orange-citric">
                                Nossa História e Evolução
                            </h2>
                            <ol class="grid gap-4 md:grid-cols-2">
                                {#each aboutTimeline as item}
                                    <li class="rounded-md border border-suspense-aurora/20 bg-blue-ocean/20 p-5">
                                        <p class="text-sm font-black uppercase italic text-orange-citric">{item.date}</p>
                                        <h3 class="mt-2 text-xl font-black uppercase italic leading-tight text-suspense-aurora">
                                            {item.title}
                                        </h3>
                                        <p class="mt-3 text-base font-semibold leading-6 text-suspense-aurora/75">
                                            {item.description}
                                        </p>
                                    </li>
                                {/each}
                            </ol>
                        </div>

                        <div class="grid gap-5 rounded-md border border-orange-citric/35 bg-blue-night/15 p-5 md:grid-cols-[12rem_minmax(0,1fr)] md:items-center">
                            <div>
                                <p class="text-xs font-black uppercase italic text-orange-citric">Sintonize</p>
                                <h2 class="mt-1 text-2xl font-black uppercase italic leading-none text-suspense-aurora">
                                    Onde a Akiba toca
                                </h2>
                            </div>
                            <ul class="grid gap-3 sm:grid-cols-3">
                                {#each aboutHighlights as highlight, index}
                                    <li class="grid min-h-22 grid-cols-[2.5rem_minmax(0,1fr)] items-center gap-3 rounded-md border border-suspense-aurora/15 bg-blue-ocean/20 px-4 py-3">
                                        <span class="flex size-10 items-center justify-center rounded-md bg-orange-citric font-noto-sans text-sm font-black italic text-blue-night">
                                            0{index + 1}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-base font-black uppercase italic leading-tight text-suspense-aurora">
                                                {highlight.label}
                                            </span>
                                            <span class="mt-1 block text-xs font-bold uppercase italic leading-snug text-suspense-aurora/65">
                                                {highlight.description}
                                            </span>
                                        </span>
                                    </li>
                                {/each}
                            </ul>
                        </div>

                        <p class="mx-auto max-w-4xl text-center text-xl font-black uppercase italic leading-7 text-orange-citric">
                            Seja no rádio ou no vídeo, a missão da Rede Akiba permanece a mesma: reinventar o jeito de fazer rádio otaku no Brasil para conectar a comunidade e entregar a melhor experiência online.
                        </p>
                    </div>
                {/if}
            </div>
        </section>
    </div>
</Layout>
