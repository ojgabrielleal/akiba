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
    $: aboutParagraphs = about ?? [
        "Foi na tarde do dia 25 de maio de 2016 que os atuais idealizadores da Akiba tiveram a ideia de criar uma emissora de rádio direcionada ao público fã de animes e cultura japonesa. Depois de escolher o foco que seguiriam, no dia seguinte o grupo de amigos se une para a construção do primeiro site da webrádio.",
        "A rádio sempre foi o fator principal do projeto que visava uma maior interação com o público e parceiros através dos programas transmitidos ao vivo durante a programação e redes sociais. Teve seu início e continua no ar em razão de um simples, porém importantíssimo requisito, a ambição por uma rádio que tocasse as músicas orientais que nós gostamos, uma programação diferenciada, ativa, atualizada e moderna!",
        "Esperamos que você curta nosso conteúdo, que é minuciosamente selecionado para levar até seu PC, tablet, smartphone (App Android) ou até mesmo sua Smart TV, o melhor conteúdo otaku desenvolvido exclusivamente para o público brasileiro.",
        "A Rádio Akiba realiza suas transmissões na excelente qualidade AAC Plus ou HE-AAC (High-Efficiency Advanced Audio Coding) 320 kbps em nosso site e em dezenas de outros agregadores que retransmitem a emissora na web, além de ser um serviço de mídia multiplataforma.",
        "A Rede Akiba se expande mais ainda em 2021 e dá espaço para streamers e criadores de conteúdo otaku nas plataformas de vídeo YouTube e Twitch, além das redes sociais Instagram, Facebook e Twitter.",
        "Estamos no ar desde o dia 09 de outubro de 2016, trabalhando muito e reinventando o jeito de fazer rádio otaku no Brasil para levar até você a melhor experiência online possível!",
    ];
</script>

<Meta meta={{ title: "Rádio" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <h1 class="sr-only">Rádio</h1>
    <div class="bg-blue-night pt-10">
        <RadioProgramGrid {programs} />
        <RadioRankingGrid {ranking} />
        <RadioListenerMonthGrid {listenerMonth} />

        <section class="bg-blue-marinho pb-8">
            <EditorialTitle title="Sobre nós" compact />
            <div class="container-page py-8">
                <div class="mx-auto max-w-5xl space-y-5 font-noto-sans text-base font-normal leading-7 text-suspense-aurora sm:text-lg sm:leading-8">
                    {#each aboutParagraphs as paragraph}
                        <p>{paragraph}</p>
                    {/each}
                </div>
            </div>
        </section>
    </div>
</Layout>
