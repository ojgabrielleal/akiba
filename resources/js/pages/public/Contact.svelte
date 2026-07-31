<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { EditorialTitle } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { RecruitmentForm } from "@/lib/widgets/public";

    $: ({ onair, stream, flash, oauth } = $page.props);
    $: pageUrl = $page.url;
</script>

<Meta meta={{ title: "Contato" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-10">
        <EditorialTitle
            title="Entre para a equipe"
            phrase="Só preencha o formulário aí e espera a resposta (normalmente respondemos aos sábados)"
        />

        <div class="bg-blue-marinho pb-12 pt-14 text-suspense-aurora md:pt-16">
            <div class="container-page grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:gap-12">
                <div class="font-noto-sans">
                    <h2 class="mb-4 text-sm font-black italic uppercase text-neutral-gray">
                        Tenha certeza de:
                    </h2>

                    <ul class="space-y-2 text-sm font-semibold uppercase text-blue-skywave">
                        <li>+ Ter mais de 15 anos de idade</li>
                        <li>+ Ter um bom microfone</li>
                        <li>+ Ter boa oralidade e dissertação</li>
                        <li>+ Conhecer partes técnicas dos anime</li>
                        <li>+ Ter um espaço sem ruídos</li>
                        <li>+ Estar sempre livre nos sábados às 3 da tarde</li>
                        <li>+ Ter responsabilidade</li>
                    </ul>

                    <div class="my-6 h-px bg-blue-skywave"></div>

                    <p class="max-w-xl text-sm leading-7 text-blue-skywave">
                        A Akiba Network trabalha de forma independente, porém seguindo certas diretrizes, portanto a sua participação também terá que segui-las.
                        Logo abaixo tem um PDF com todas as diretrizes, se concordar com elas, pode continuar com a inscrição.
                    </p>

                    <a
                        href="/pdf/diretrizes_rede_akiba.pdf"
                        class="mx-auto mt-5 flex min-h-9 w-fit items-center justify-center rounded-md bg-blue-cerulean px-5 font-noto-sans text-sm font-black italic uppercase text-orange-citric transition hover:brightness-110"
                    >
                        Diretrizes_Rede_Akiba.pdf
                    </a>
                </div>

                <RecruitmentForm />
            </div>
        </div>
    </section>
</Layout>
