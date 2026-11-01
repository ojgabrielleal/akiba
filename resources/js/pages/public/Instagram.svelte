<script>
    import { Link, page } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import { Layout } from "@/lib/layouts/public";

    $: ({ flash, oauth, onair, stream, stories = [] } = $page.props);
    $: pageUrl = $page.url;
    $: featured = stories[0] ?? null;
    $: previous = stories.slice(1);
</script>

<Meta meta={{ title: "Stories" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="min-h-[70vh] bg-blue-night py-10 sm:py-14">
        <div class="container-page">
            <header class="mb-8 max-w-3xl">
                <p class="mb-1 font-noto-sans text-sm font-black uppercase italic tracking-wider text-orange-morning">
                    Rede Akiba nas redes
                </p>
                <h1 class="font-noto-sans text-3xl font-black uppercase italic leading-tight text-suspense-aurora sm:text-5xl">
                    Continue lendo
                </h1>
                <p class="mt-2 max-w-2xl font-noto-sans text-base leading-relaxed text-neutral-gray">
                    Encontre aqui as matérias divulgadas recentemente nos nossos stories.
                </p>
            </header>

            {#if featured}
                <Link
                    href={featured.href}
                    class={["group grid overflow-hidden rounded-md bg-blue-ocean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric md:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.65fr)]", publicAnimations.cardInteractive]}
                >
                    <div class="aspect-[16/10] overflow-hidden bg-blue-marinho md:aspect-auto md:min-h-[26rem]">
                        <img src={featured.image} alt="" class={["h-full w-full object-cover", publicAnimations.imageZoom]} />
                    </div>
                    <article class="flex flex-col justify-center p-6 sm:p-8">
                        <span class="mb-3 font-noto-sans text-xs font-black uppercase italic tracking-wider text-orange-morning">
                            Destaque · {featured.category}
                        </span>
                        <h2 class="font-noto-sans text-2xl font-black uppercase italic leading-tight text-suspense-aurora group-hover:text-orange-citric group-focus-visible:text-orange-citric sm:text-3xl">
                            {featured.title}
                        </h2>
                        <span class="mt-5 font-noto-sans text-sm font-extrabold uppercase italic text-orange-citric">
                            Leia a matéria →
                        </span>
                    </article>
                </Link>

                {#if previous.length > 0}
                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {#each previous as story (story.uuid)}
                            <Link
                                href={story.href}
                                class={["group overflow-hidden rounded-md bg-blue-ocean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric", publicAnimations.cardInteractive]}
                            >
                                <div class="aspect-video overflow-hidden bg-blue-marinho">
                                    <img src={story.image} alt="" class={["h-full w-full object-cover", publicAnimations.imageZoom]} />
                                </div>
                                <article class="p-5">
                                    <span class="font-noto-sans text-xs font-black uppercase italic text-orange-morning">
                                        {story.category} · {story.published_at}
                                    </span>
                                    <h2 class="mt-2 line-clamp-3 font-noto-sans text-lg font-black uppercase italic leading-tight text-suspense-aurora group-hover:text-orange-citric group-focus-visible:text-orange-citric">
                                        {story.title}
                                    </h2>
                                </article>
                            </Link>
                        {/each}
                    </div>
                {/if}
            {:else}
                <div class="rounded-md border border-suspense-aurora/10 bg-blue-ocean p-8 text-center sm:p-12">
                    <h2 class="font-noto-sans text-xl font-black uppercase italic text-suspense-aurora">
                        Nenhuma matéria divulgada por enquanto
                    </h2>
                    <p class="mt-1 font-noto-sans text-suspense-aurora">
                        As próximas matérias publicadas nos stories aparecerão aqui.
                    </p>
                </div>
            {/if}
        </div>
    </section>
</Layout>
