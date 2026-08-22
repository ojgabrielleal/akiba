<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { postTags } from "@/lib/constants";
    import { EditorialTitle, Pagination } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { PostListGrid } from "@/lib/widgets/public";

    $: ({ flash, oauth, onair, stream } = $page.props);
    
    $: title = $page.props.title ?? "News";
    $: posts = $page.props.posts ?? [];
    $: categories = $page.props.categories ?? [];
    $: pageUrl = $page.url;
    $: activeTag = new URL(pageUrl, "http://akiba.local").searchParams.get("tag") ?? $page.props.activeTag;

    const categoryLabel = (name) => postTags[name]?.label ?? name;
    const categoryHref = (category) => `${pageUrl.split("?")[0]}?tag=${category}`;

    $: displayTitle = activeTag ? categoryLabel(activeTag) : title;
    $: emptyTitle = `Nenhuma matéria em ${displayTitle}`;
    $: emptyMessage = "Novas publicações aparecem aqui assim que entrarem no ar.";
</script>

<Meta meta={{ title: displayTitle }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-page-background bg-blue-night pt-10">
        <EditorialTitle title={displayTitle} listLabel={`Categorias de ${title}`}>
            {#each categories as category}
                <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                    <Link
                        href={categoryHref(category)}
                        only={["posts", "activeTag"]}
                        preserveScroll
                        class={[
                            "group/category relative flex items-center gap-2 whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-amber focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                            activeTag === category ? "text-orange-amber" : "text-neutral-gray",
                        ]}
                    >
                        <img
                            src={postTags[category]?.icon}
                            alt=""
                            aria-hidden="true"
                            class={[
                                "size-6 group-hover/category:scale-105 group-hover/category:filter-orange-amber group-focus-visible/category:scale-105 group-focus-visible/category:filter-orange-amber motion-reduce:transform-none",
                                activeTag === category ? "filter-orange-amber" : "filter-neutral-gray",
                            ]}
                        />
                        {categoryLabel(category)}
                    </Link>
                </li>
            {/each}
        </EditorialTitle>

        <div class="min-h-[18rem] bg-blue-marinho py-16">
            <div class="container-page">
                <PostListGrid {posts} {emptyTitle} {emptyMessage} />
                <Pagination pages={posts} only={["posts"]} />
            </div>
        </div>
    </section>
</Layout>
