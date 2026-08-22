<script>
    import { Link, router, page } from "@inertiajs/svelte";

    import { Meta } from "@/lib/components/shared";
    import { Button, Pagination, TextInput } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { PostListGrid } from "@/lib/widgets/public";

    let query = null;

    $: ({ flash, oauth, onair, stream, search, results } = $page.props);

    $: pageUrl = $page.url;
    $: searchedQuery = search ?? "";
    $: if (query === null) {
        query = searchedQuery;
    }
    $: editorialResults = results?.editorial ?? [];
    $: extraSections = [
        { key: "podcasts", title: "Podcasts", items: results?.podcasts ?? [] },
        { key: "programs", title: "Programas", items: results?.programs ?? [] },
        { key: "calendar", title: "Agenda", items: results?.calendar ?? [] },
        { key: "team", title: "Equipe", items: results?.team ?? [] },
    ];
    $: extraCount = extraSections.reduce((total, section) => total + section.items.length, 0);
    $: resultCount = (editorialResults?.meta?.total ?? editorialResults?.data?.length ?? 0) + extraCount;
    $: hasSearched = searchedQuery.trim().length > 0;
    $: title = hasSearched ? `Buscar por "${searchedQuery}"` : "Buscar";

    const submitSearch = () => {
        const normalizedQuery = (query ?? "").trim();

        router.get(
            "/buscar",
            normalizedQuery ? { q: normalizedQuery } : {},
            {
                preserveScroll: true,
                only: ["search", "results"],
            },
        );
    };
</script>

<Meta meta={{ title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-10">
        <div class="container-page pb-10">
            <div class="max-w-3xl">
                <form class="flex flex-col gap-2 sm:flex-row" on:submit|preventDefault={submitSearch}>
                    <TextInput
                        id="global-search"
                        type="search"
                        name="q"
                        variant="transparent"
                        class="focus:border-suspense-aurora/35"
                        placeholder="Busque por matérias, reviews, podcasts, programas e equipe"
                        bind:value={query}
                    />
                    <Button type="submit" class="sm:w-36">
                        <img src="/svg/search.svg" alt="" aria-hidden="true" class="size-4 filter-blue-marinho" />
                        Buscar
                    </Button>
                </form>
            </div>
        </div>

        <div class="min-h-[28rem] bg-blue-night pt-1 pb-10">
            <div class="container-page">
                {#if hasSearched}
                    <div class="mb-5 font-noto-sans text-sm font-bold uppercase italic text-neutral-gray">
                        {resultCount} {resultCount === 1 ? "resultado encontrado" : "resultados encontrados"}
                    </div>

                    {#if resultCount > 0}
                        {#if editorialResults?.data?.length > 0}
                            <div class="mb-12">
                                <h2 class="mb-5 font-noto-sans text-xl font-black uppercase italic text-orange-amber">
                                    Matérias, reviews e eventos
                                </h2>
                                <PostListGrid posts={editorialResults} styles="" advertisementAfter={0} />
                                <Pagination pages={editorialResults} only={["search", "results"]} loadingLabel="Buscando..." />
                            </div>
                        {/if}

                        {#each extraSections.filter((section) => section.items.length > 0) as section (section.key)}
                            <section class="mt-8">
                                <h2 class="mb-3 font-noto-sans text-xl font-black uppercase italic text-orange-amber">
                                    {section.title}
                                </h2>
                                <ul class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    {#each section.items as item (item.uuid)}
                                        <li>
                                            <Link
                                                href={item.href}
                                                class="group flex min-h-20 gap-3 rounded-md bg-blue-ocean p-3 transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                                            >
                                                <div class={[
                                                    "flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-md",
                                                    section.key === "team" ? "bg-transparent" : "bg-blue-night",
                                                ]}>
                                                    {#if item.image}
                                                        <img src={item.image} alt="" aria-hidden="true" class="h-full w-full object-cover" />
                                                    {:else}
                                                        <img src="/svg/search.svg" alt="" aria-hidden="true" class="size-7 filter-orange-amber" />
                                                    {/if}
                                                </div>
                                                <article class="min-w-0">
                                                    <div class="mb-1 font-noto-sans text-xs font-black uppercase italic text-orange-morning">
                                                        {item.type}
                                                    </div>
                                                    <h3 class="line-clamp-1 font-noto-sans text-base font-black uppercase italic leading-tight text-suspense-aurora group-hover:text-orange-amber">
                                                        {item.title}
                                                    </h3>
                                                    {#if item.description}
                                                        <p class="mt-1 line-clamp-1 font-noto-sans text-sm text-neutral-gray">
                                                            {item.description}
                                                        </p>
                                                    {/if}
                                                </article>
                                            </Link>
                                        </li>
                                    {/each}
                                </ul>
                            </section>
                        {/each}
                    {:else}
                        <div class="rounded-md border border-suspense-aurora/10 bg-blue-ocean p-6 font-noto-sans text-suspense-aurora">
                            Nenhum resultado encontrado para "{searchedQuery}".
                        </div>
                    {/if}
                {:else}
                    <div class="flex max-w-2xl items-start gap-4 font-noto-sans">
                        <div class="flex size-13 shrink-0 items-center justify-center rounded-md border border-orange-morning/35 bg-orange-morning/10">
                            <img src="/svg/search.svg" alt="" aria-hidden="true" class="size-6 filter-orange-morning" />
                        </div>
                        <div>
                            <p class="text-xl font-black uppercase italic leading-tight text-orange-morning">
                                O que vamos garimpar hoje?
                            </p>
                            <p class="mt-2 max-w-xl text-sm font-bold leading-relaxed text-suspense-aurora/70">
                                Procure por matérias, reviews, podcasts, programas ou integrantes da equipe.
                            </p>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </section>
</Layout>
