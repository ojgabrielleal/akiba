<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { postTags } from "@/lib/constants";
    import { Pagination } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { PostListGrid } from "@/lib/widgets/public";

    $: ({ flash, oauth, onair, stream } = $page.props);
    
    $: title = $page.props.title ?? "News";
    $: posts = $page.props.posts ?? [];
    $: categories = $page.props.categories ?? [];
    $: pageUrl = $page.url;
    $: activeTag = new URL(pageUrl, "http://akiba.local").searchParams.get("tag") ?? $page.props.activeTag;

    const categoryLabel = (name) => postTags[name]?.label ?? name;
    const categoryHref = (category) => category === "reviews"
        ? "/reviews"
        : `${pageUrl.split("?")[0]}?tag=${category}`;

    $: displayTitle = activeTag ? categoryLabel(activeTag) : title;
</script>

<Meta meta={{ title: displayTitle }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-10">
        <header class="relative isolate overflow-hidden bg-cover bg-right bg-no-repeat py-5 lg:bg-contain" style="background-image: url('/img/textures/screentone.webp'), var(--gradient-blue-ocean-cerulean);">
            <div class="container-page relative">
                <h1 class="text-center font-noto-sans text-6xl font-black italic uppercase leading-none text-orange-citric">
                    {displayTitle}
                </h1>
            </div>
        </header>

        <nav aria-label={`Categorias de ${title}`}>
            <ul class="container-page flex flex-wrap items-center justify-center gap-y-3 py-8">
                {#each categories as category}
                    <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                        {#if category === "reviews"}
                            <Link
                                href="/reviews"
                                class="group/category relative flex items-center gap-2 whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic text-neutral-gray transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <img
                                    src={postTags[category]?.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class="size-6 filter-neutral-gray group-hover/category:scale-105 group-hover/category:filter-orange-citric group-focus-visible/category:scale-105 group-focus-visible/category:filter-orange-citric motion-reduce:transform-none"
                                />
                                {categoryLabel(category)}
                            </Link>
                        {:else}
                            <Link
                                href={categoryHref(category)}
                                only={["posts", "activeTag"]}
                                preserveScroll
                                class={[
                                    "group/category relative flex items-center gap-2 whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                                    activeTag === category ? "text-orange-citric" : "text-neutral-gray",
                                ]}
                            >
                                <img
                                    src={postTags[category]?.icon}
                                    alt=""
                                    aria-hidden="true"
                                    class={[
                                        "size-6 group-hover/category:scale-105 group-hover/category:filter-orange-citric group-focus-visible/category:scale-105 group-focus-visible/category:filter-orange-citric motion-reduce:transform-none",
                                        activeTag === category ? "filter-orange-citric" : "filter-neutral-gray",
                                    ]}
                                />
                                {categoryLabel(category)}
                            </Link>
                        {/if}
                    </li>
                {/each}
            </ul>
        </nav>

        <div class="min-h bg-blue-marinho py-16">
            <div class="container-page">
                <PostListGrid {posts} />
                <Pagination pages={posts} only={["posts"]} />
            </div>
        </div>
    </section>
</Layout>
