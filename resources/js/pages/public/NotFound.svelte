<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { publicAnimations } from "@/lib/constants";
    import { Layout } from "@/lib/layouts/public";

    $: ({ onair, stream, flash, oauth } = $page.props);
    $: pageUrl = $page.url;
</script>

<Meta meta={{
    title: "404 - Página não encontrada",
    description: "A página que você tentou acessar não foi encontrada na Akiba.",
    robots: "noindex, follow",
}} />

<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-page-background relative isolate overflow-hidden bg-blue-night text-suspense-aurora">
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: url('/img/textures/stars.webp');" aria-hidden="true"></div>

        <div class="container-page relative flex min-h-[calc(100vh-9rem)] items-center justify-center py-16 text-center">
            <div class="mx-auto flex max-w-xl flex-col items-center">
                <div class="flex items-center gap-3 font-noto-sans text-[0.7rem] font-black uppercase tracking-[0.18em] text-orange-amber/90">
                    <span class="h-px w-8 bg-orange-amber/60"></span>
                    fora do ar
                    <span class="h-px w-8 bg-orange-amber/60"></span>
                </div>

                <div class="mt-5 flex items-end justify-center gap-3">
                    <span class="mb-3 h-9 w-2 rounded-full bg-blue-skywave/35"></span>
                    <span class="mb-5 h-12 w-2 rounded-full bg-orange-amber"></span>
                    <p class="font-noto-sans text-7xl font-black italic leading-none text-orange-amber drop-shadow-[0_0.75rem_1.75rem_rgba(255,128,0,0.2)] sm:text-8xl">
                        404
                    </p>
                    <span class="mb-5 h-12 w-2 rounded-full bg-orange-amber"></span>
                    <span class="mb-3 h-9 w-2 rounded-full bg-blue-skywave/35"></span>
                </div>

                <h1 class="mt-6 font-noto-sans text-3xl font-black leading-tight text-suspense-aurora sm:text-4xl">
                    Essa página saiu da grade
                </h1>

                <p class="mt-4 max-w-md font-noto-sans text-base font-semibold leading-7 text-neutral-gray">
                    A transmissão passou por aqui, mas o endereço ficou sem sinal. Pode ser um link antigo, uma rota movida ou um episódio que nunca estreou.
                </p>

                <div class="mt-8 rounded-full border border-suspense-aurora/10 bg-blue-night/55 px-4 py-2 font-noto-sans text-xs font-extrabold uppercase tracking-[0.12em] text-blue-skywave">
                    sintonize de novo pela página inicial
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <button
                        type="button"
                        class={[
                            "inline-flex min-h-11 min-w-28 items-center justify-center rounded-md border border-orange-amber/55 bg-transparent px-6 py-2 font-noto-sans text-sm font-extrabold uppercase text-orange-amber hover:bg-orange-amber/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                            publicAnimations.buttonInteractive,
                        ]}
                        on:click={() => window.history.back()}
                    >
                        Voltar
                    </button>
                    <Link
                        href="/site"
                        class={[
                            "inline-flex min-h-11 min-w-40 items-center justify-center rounded-md bg-orange-amber px-6 py-2 font-noto-sans text-sm font-extrabold uppercase text-blue-night shadow-[0_0.75rem_1.5rem_rgba(255,163,26,0.22)] hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                            publicAnimations.buttonInteractive,
                        ]}
                    >
                        Página inicial
                    </Link>
                </div>
            </div>
        </div>
    </section>
</Layout>
