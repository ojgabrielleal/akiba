<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { EditorialTitle, Pagination } from "@/lib/components/public";
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
        <EditorialTitle title="Reviews" listLabel="Filtros de reviews">
            {#each filters as filter}
                <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                    <Link
                        href={filterHref(filter.value)}
                        only={["reviews", "activeSort"]}
                        preserveScroll
                        class={[
                            "rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none motion-reduce:transition-none",
                            currentSort === filter.value ? "text-orange-citric" : "text-neutral-gray",
                        ]}
                    >
                        {filter.label}
                    </Link>
                </li>
            {/each}
        </EditorialTitle>

        <div class="bg-blue-marinho py-10">
            <div class="container-page">
                <ReviewListGrid reviews={reviews} styles="" />
                <Pagination pages={reviews} only={["reviews", "activeSort"]} loadingLabel="Carregando reviews..." />
            </div>
        </div>
    </section>
</Layout>
