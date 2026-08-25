<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { EditorialTitle } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { RecruitmentForm } from "@/lib/widgets/public";

    const socialLinks = [
        {
            name: "Instagram",
            label: "@redeakiba",
            description: "Bastidores, artes e chamadas da programação.",
            icon: "/svg/instagram.svg",
            href: "https://www.instagram.com/redeakiba/",
        },
        {
            name: "Facebook",
            label: "Rede Akiba",
            description: "Novidades, eventos e conteúdos da comunidade.",
            icon: "/svg/facebook.svg",
            href: "https://www.facebook.com/RedeAkiba",
        },
        {
            name: "X / Twitter",
            label: "@RedeAkiba",
            description: "Avisos rápidos, links e comentários da Akiba.",
            icon: "/svg/x.svg",
            href: "https://x.com/RedeAkiba",
        },
        {
            name: "Discord",
            label: "Comunidade",
            description: "Converse com a galera e acompanhe a rádio ao vivo.",
            icon: "/svg/discord.svg",
            href: "https://discord.akiba.com.br",
        },
        {
            name: "YouTube",
            label: "@RedeAkiba",
            description: "Vídeos, programas especiais e conteúdos da rede.",
            icon: "/svg/youtube.svg",
            href: "https://www.youtube.com/@RedeAkiba",
        },
        {
            name: "Apple Podcasts",
            label: "AkibaCast",
            description: "Episódios do AkibaCast para ouvir no Apple Podcasts.",
            icon: "/svg/apple-podcasts.svg",
            href: "https://podcasts.apple.com/us/podcast/akibacast/id1508240222",
        },
        {
            name: "Spotify",
            label: "AkibaCast",
            description: "Ouça o AkibaCast direto pelo Spotify.",
            icon: "/svg/spotify.svg",
            href: "https://open.spotify.com/show/21MR4fnrOnKW7gNGYiWx37",
        },
        {
            name: "Amazon Music",
            label: "AkibaCast",
            description: "AkibaCast disponível também no Amazon Music.",
            icon: "/svg/amazon-music.svg",
            iconClass: "w-9 filter-suspense-aurora",
            href: "https://music.amazon.com.au/podcasts/fafe997e-b911-4a8e-8e38-dbbc6b285d83/akibacast",
        },
    ];

    $: ({ onair, stream, flash, oauth } = $page.props);
    $: pageUrl = $page.url;
</script>

<Meta meta={{ title: "Contato" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-page-background bg-blue-night">
        <EditorialTitle
            title="Redes sociais da Akiba"
            phrase="Cola com a gente nas redes pra acompanhar a programação, novidades e avisos"
            phraseMinHeight="min-h-0"
        />

        <div class="container-page pb-20 pt-2 font-noto-sans">
            <ul class="mx-auto grid max-w-7xl grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {#each socialLinks as social}
                    <li class="min-w-0">
                        <a
                            href={social.href}
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group/social relative flex min-h-34 overflow-hidden rounded-md bg-blue-ocean p-4 text-left transition duration-300 ease-out hover:-translate-y-0.5 hover:bg-blue-cerulean focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                        >
                            <span class="absolute inset-y-0 left-0 w-1.5 bg-orange-amber" aria-hidden="true"></span>
                            <span class="flex min-w-0 flex-1 flex-col gap-2 pl-1">
                                <span class="flex items-center gap-3">
                                    <span class="flex size-12 shrink-0 items-center justify-center rounded-md bg-blue-night">
                                        <img
                                            src={social.icon}
                                            alt=""
                                            aria-hidden="true"
                                            class={social.iconClass ?? "size-6 filter-suspense-aurora"}
                                        />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-base font-black uppercase italic text-orange-citric group-hover/social:text-orange-amber">
                                            {social.name}
                                        </span>
                                        <span class="mt-0.5 block text-[0.65rem] font-black uppercase italic text-blue-skywave">
                                            {social.label}
                                        </span>
                                    </span>
                                </span>

                                <span class="block text-xs font-bold leading-5 text-suspense-aurora/75">
                                    {social.description}
                                </span>
                            </span>
                        </a>
                    </li>
                {/each}
            </ul>
        </div>

        <EditorialTitle
            title="Entre para a equipe"
            phrase="Só preencha o formulário aí e espera a resposta (normalmente respondemos aos sábados)"
            phraseMinHeight="min-h-0"
            topSpacing=""
        />

        <div class="pb-12 pt-5 text-suspense-aurora">
            <div class="container-page grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:gap-12">
                <div class="public-contact-copy font-noto-sans">
                    <h2 class="public-contact-title mb-4 text-sm font-black italic uppercase text-orange-amber">
                        Tenha certeza de:
                    </h2>

                    <ul class="public-contact-list space-y-2 text-sm font-semibold uppercase text-suspense-aurora/75">
                        <li>+ Ter mais de 16 anos de idade</li>
                        <li>+ Ter um bom microfone</li>
                        <li>+ Ter boa oralidade e dissertação</li>
                        <li>+ Conhecer partes técnicas dos anime</li>
                        <li>+ Ter um espaço sem ruídos</li>
                        <li>+ Estar sempre livre nos sábados às 3 da tarde</li>
                        <li>+ Ter responsabilidade</li>
                    </ul>

                    <div class="my-6 h-px bg-blue-skywave"></div>

                    <p class="public-contact-text max-w-xl text-sm leading-7 text-suspense-aurora/75">
                        A Akiba Network trabalha de forma independente, porém seguindo certas diretrizes, portanto a sua participação também terá que segui-las.
                        Logo abaixo tem um PDF com todas as diretrizes, se concordar com elas, pode continuar com a inscrição.
                    </p>

                </div>

                <RecruitmentForm />
            </div>
        </div>
    </section>
</Layout>
