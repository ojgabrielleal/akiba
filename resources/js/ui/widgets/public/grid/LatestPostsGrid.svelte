<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";
    import { postTags } from "@/data";
    import { resolvePlaceholderImage } from "@/utils";

    $: latestPosts = $page.props.latestPosts?.data ?? [];
</script>

{#if latestPosts.length > 0}
    <Section title="Últimas matérias">
        <ul class="grid grid-cols-1 gap-x-7 gap-y-6 lg:grid-cols-2">
            {#each latestPosts as post (post.uuid)}
                <li class="min-w-0">
                    <Link
                        href={`post/${post.slug}`}
                        aria-label={`Ler matéria: ${post.title}`}
                        class="group grid grid-cols-[7rem_1fr] gap-3 rounded-md transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none sm:grid-cols-[11.5rem_1fr]"
                    >
                        <div class="aspect-[4/3] overflow-hidden rounded-md bg-neutral-gray">
                            <img
                                src={resolvePlaceholderImage(post.cover, "placeholder")}
                                alt=""
                                aria-hidden="true"
                                class="h-full w-full rounded-md object-cover"
                            />
                        </div>
                        <article class="flex min-w-0 flex-col justify-between">
                            <h3 class="line-clamp-3 font-noto-sans text-lg leading-tight font-bold text-suspense-aurora uppercase italic sm:text-xl">
                                {post.title}
                            </h3>
                            <div class="flex items-end gap-2">
                                {#each post.tags as tag (tag.uuid)}
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
    </Section>
{/if}
