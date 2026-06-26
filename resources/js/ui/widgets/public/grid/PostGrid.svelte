<script>
    export let title;
    export let variant = "default";

    import { page, Link } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";
    import { postTags } from "@/data";
    import { resolvePlaceholderImage } from "@/utils";

    $: ({ posts } = $page.props);
</script>

{#if variant === "home" && posts.data.length > 0}
    <Section {title}>
        <ul class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            {#each posts.data as item}
                <li>
                    <Link
                        aria-label={`Ler materia ${item.title}`}
                        href={`review/${item.slug}`}
                        class="flex flex-col sm:flex-row gap-4"
                    >
                        <article class="contents">
                            <img
                                src={resolvePlaceholderImage(item.cover, "placeholder")}
                                alt={item.title}
                                class="w-full sm:w-50 sm:h-40 aspect-square sm:aspect-auto object-cover rounded-md bg-amber-50 shrink-0"
                            />
                            <div class="flex flex-col justify-between relative w-full">
                                <h2 class="font-noto-sans font-extrabold text-lg sm:text-xl text-suspense-aurora italic uppercase line-clamp-3 sm:line-clamp-4">
                                    {item.title}
                                </h2>
                                <ul class="flex gap-3 mt-2 sm:mt-0 sm:absolute sm:bottom-0" aria-label="Categorias">
                                    {#each item.tags as tag}
                                        <li>
                                            <img
                                                src={postTags[tag.name]?.icon}
                                                alt={tag.name}
                                                class="w-5 h-5 sm:w-6 sm:h-6 filter-suspense-aurora"
                                            />
                                        </li>
                                    {/each}
                                </ul>
                            </div>
                        </article>
                    </Link>
                </li>
            {/each}
        </ul>
    </Section>
{/if}
