<script>
    import { Link } from "@inertiajs/svelte";
    
    import { postTags, publicAnimations } from "@/lib/constants";
    import { AdvertisementSlot, MinimalEmptyState } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let posts = [];
    export let title = null;
    export let styles = "container-page mb-10";
    export let baseHref = "/materia";
    export let fallbackPosts = [];
    export let advertisementAfter = 10;
    export let emptyTitle = "Nada por aqui ainda";
    export let emptyMessage = "Assim que uma matéria entrar no ar, ela aparece nesta seção.";

    $: resolvedPosts = Array.isArray(posts) ? posts : posts?.data ?? [];
    $: postList = resolvedPosts.length > 0 ? resolvedPosts : fallbackPosts;

    const postHref = (post) => post.placeholder ? "#" : post.href ?? `${baseHref}/${post.slug}`;
</script>

{#if postList.length > 0}
    <section class={["public-post-list-grid", title ? styles : ""]}>
        {#if title}
            <div class="public-section-heading mb-5 flex items-center gap-4 after:h-px after:flex-1 after:bg-orange-citric after:content-['']">
                <h2 class="public-section-heading-title whitespace-nowrap font-noto-sans text-[1.3rem] font-black text-orange-citric uppercase italic">
                    {title}
                </h2>
            </div>
        {/if}

        <ul class="grid grid-cols-1 gap-x-10 gap-y-8 lg:grid-cols-2">
            {#each postList as post, index (post.uuid)}
                {#if advertisementAfter > 0 && index === advertisementAfter}
                    <li class="lg:col-span-2">
                        <AdvertisementSlot class="mx-auto h-20 max-w-3xl" />
                    </li>
                {/if}

                <li class="min-w-0">
                    <Link
                        href={postHref(post)}
                        aria-label={`Ler matéria: ${post.title}`}
                        class={["public-post-list-card group block overflow-hidden rounded-md bg-blue-ocean focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber sm:grid sm:grid-cols-[14rem_1fr] sm:gap-3 sm:bg-transparent", publicAnimations.cardInteractive]}
                    >
                        <div class="relative aspect-[16/9] overflow-hidden bg-neutral-gray sm:h-34 sm:aspect-auto sm:rounded-md">
                            <img
                                src={resolvePlaceholderImage(post.cover, "placeholder")}
                                alt=""
                                aria-hidden="true"
                                class={["h-full w-full object-cover sm:rounded-md", publicAnimations.imageZoom]}
                            />
                            <span class="like-metric-badge absolute right-1.5 top-1.5 z-10 inline-flex h-5 min-w-11 items-center justify-center gap-1 rounded-sm bg-orange-amber px-1.5 font-noto-sans text-xs leading-none font-black text-suspense-aurora uppercase italic shadow-sm shadow-blue-night/20">
                                {post.likes_count ?? 0}
                                <img src="/svg/like.svg" alt="" aria-hidden="true" class="size-3 filter-suspense-aurora" />
                            </span>
                        </div>
                        <article class="flex min-w-0 flex-col justify-between gap-4 p-3 sm:p-0">
                            <h3 class="public-post-list-title line-clamp-3 font-noto-sans text-xl leading-tight font-bold text-orange-citric uppercase italic sm:text-[1.375rem]">
                                {post.title}
                            </h3>
                            <div class="flex items-end gap-2">
                                {#each post.tags ?? [] as tag (tag.uuid)}
                                    <img
                                        src={postTags[tag.name]?.icon}
                                        alt={postTags[tag.name]?.label ?? tag.name}
                                        title={postTags[tag.name]?.label ?? tag.name}
                                        class="public-post-list-icon size-5 object-contain filter-suspense-aurora sm:size-6"
                                    />
                                {/each}
                            </div>
                        </article>
                    </Link>
                </li>
            {/each}
        </ul>
    </section>
{:else}
    <MinimalEmptyState title={emptyTitle} message={emptyMessage} />
{/if}
