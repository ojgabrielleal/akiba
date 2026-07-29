<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Pagination } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { ReviewListGrid } from "@/lib/widgets/public";

    const filters = [
        { value: "alphabetical", label: "Alfabética" },
        { value: "likes", label: "Melhor avaliados" },
        { value: "year", label: "Ano de lançamento" },
    ];

    $: ({ flash, oauth, onair, stream, reviews, activeSort } = $page.props);

    $: pageUrl = $page.url;
    $: currentSort = new URL(pageUrl, "http://akiba.local").searchParams.get("sort") ?? activeSort ?? "alphabetical";

    const filterHref = (sort) => `/reviews?sort=${sort}`;
</script>

<Meta meta={{ title: "Reviews" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-10">
        <header class="relative isolate overflow-hidden bg-cover bg-right bg-no-repeat py-5 lg:bg-contain" style="background-image: url('/img/textures/screentone.webp'), var(--gradient-blue-ocean-cerulean);">
            <div class="container-page relative">
                <h1 class="text-center font-noto-sans text-6xl font-black italic uppercase leading-none text-orange-citric">
                    Reviews
                </h1>
            </div>
        </header>

        <nav aria-label="Filtros de reviews">
            <ul class="container-page flex flex-wrap items-center justify-center gap-x-8 gap-y-3 py-8">
                {#each filters as filter}
                    <li>
                        <Link
                            href={filterHref(filter.value)}
                            only={["reviews", "activeSort"]}
                            preserveScroll
                            class={[
                                "rounded-md font-noto-sans text-lg font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                                currentSort === filter.value ? "text-orange-citric" : "text-neutral-gray",
                            ]}
                        >
                            {filter.label}
                        </Link>
                    </li>
                {/each}
            </ul>
        </nav>

        <div class="bg-blue-marinho py-10">
            <div class="container-page">
                <ReviewListGrid reviews={reviews} styles="" />
                <Pagination pages={reviews} only={["reviews", "activeSort"]} loadingLabel="Carregando reviews..." />
            </div>
        </div>
    </section>
</Layout>
