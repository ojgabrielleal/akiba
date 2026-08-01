<script>
    import { Link } from "@inertiajs/svelte";
    
    import { postTags } from "@/lib/constants";
    import { AdvertisementSlot } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let posts = [];
    export let title = null;
    export let styles = "container-page mb-10";
    export let baseHref = "/materia";
    export let fallbackPosts = [];
    export let advertisementAfter = 10;

    $: resolvedPosts = Array.isArray(posts) ? posts : posts?.data ?? [];
    $: postList = resolvedPosts.length > 0 ? resolvedPosts : fallbackPosts;

    const postHref = (post) => post.placeholder ? "#" : post.href ?? `${baseHref}/${post.slug}`;
</script>

{#if postList.length > 0}
    <section class={title ? styles : ""}>
        {#if title}
            <div class="mb-5 flex items-center gap-4 after:h-px after:flex-1 after:bg-orange-amber after:content-['']">
                <h2 class="whitespace-nowrap font-noto-sans text-[1.3rem] font-black text-orange-amber uppercase italic">
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
                        class="group block overflow-hidden rounded-md bg-blue-ocean transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none sm:grid sm:grid-cols-[14rem_1fr] sm:gap-3 sm:bg-transparent"
                    >
                        <div class="relative aspect-[16/9] overflow-hidden bg-neutral-gray sm:h-34 sm:aspect-auto sm:rounded-md">
                            <img
                                src={resolvePlaceholderImage(post.cover, "placeholder")}
                                alt=""
                                aria-hidden="true"
                                class="h-full w-full object-cover sm:rounded-md"
                            />
                            <span class="absolute right-1.5 top-1.5 z-10 inline-flex h-5 min-w-11 items-center justify-center gap-1 rounded-sm bg-orange-amber px-1.5 font-noto-sans text-xs leading-none font-black text-suspense-aurora uppercase italic shadow-sm shadow-blue-night/20">
                                {post.likes_percentage ?? 0}%
                                <img src="/svg/like.svg" alt="" aria-hidden="true" class="size-3 filter-suspense-aurora" />
                            </span>
                        </div>
                        <article class="flex min-w-0 flex-col justify-between gap-4 p-3 sm:p-0">
                            <h3 class="line-clamp-3 font-noto-sans text-lg leading-tight font-bold text-suspense-aurora uppercase italic sm:text-xl">
                                {post.title}
                            </h3>
                            <div class="flex items-end gap-2">
                                {#each post.tags ?? [] as tag (tag.uuid)}
                                    <img
                                        src={postTags[tag.name]?.icon}
                                        alt={postTags[tag.name]?.label ?? tag.name}
                                        title={postTags[tag.name]?.label ?? tag.name}
                                        class="size-5 object-contain filter-suspense-aurora sm:size-6"
                                    />
                                {/each}
                            </div>
                        </article>
                    </Link>
                </li>
            {/each}
        </ul>
    </section>
{/if}
