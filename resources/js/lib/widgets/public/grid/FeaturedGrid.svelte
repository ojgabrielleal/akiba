<script>
    import { Link } from "@inertiajs/svelte";
    import { Section } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let featuredPosts = [];

    $: posts = Array.isArray(featuredPosts) ? featuredPosts : featuredPosts?.data ?? [];
</script>

{#if posts.length > 0}
    <Section title="Destaques da Akiba" styles="container-page mb-8 pb-2 lg:mb-10 lg:pb-4">
        <ul class="mt-8 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 sm:gap-x-5 sm:gap-y-8 lg:grid-cols-3 lg:gap-x-6">
            {#each posts as item (item.uuid)}
                <li class="min-w-0">
                    <Link href={item.href} aria-label={`Ler destaque: ${item.title}`} class="group block focus-visible:outline-none">
                        <article class="public-featured-card relative mt-8 h-40 rounded-md bg-gradient-blue-cerulean-glow transition duration-300 ease-out group-hover:-translate-y-1 group-focus-visible:-translate-y-1 group-focus-visible:ring-2 group-focus-visible:ring-orange-citric motion-reduce:transform-none motion-reduce:transition-none">
                            <div class="relative z-10 w-[58%] p-2 pr-3">
                                <h3 class="public-featured-card-title line-clamp-5 font-noto-sans text-lg leading-[1.2] font-extrabold text-suspense-aurora uppercase italic sm:text-xl">
                                    {item.title}
                                </h3>
                            </div>
                            <img
                                src={resolvePlaceholderImage(item.image, "placeholder")}
                                alt=""
                                aria-hidden="true"
                                class="absolute right-0 bottom-0 z-20 h-[12.75rem] w-[55%] max-w-none origin-bottom-right object-contain object-right-bottom transition duration-300 ease-out group-hover:scale-[1.03] group-focus-visible:scale-[1.03] motion-reduce:transform-none motion-reduce:transition-none"
                            />
                            <span class="like-metric-badge absolute right-3 bottom-3 z-30 inline-flex h-5 min-w-11 items-center justify-center gap-1 rounded-sm bg-orange-amber px-1.5 font-noto-sans text-xs leading-none font-black text-suspense-aurora uppercase italic shadow-sm shadow-blue-night/20">
                                {item.likes_count ?? 0}
                                <img src="/svg/like.svg" alt="" aria-hidden="true" class="size-3 filter-suspense-aurora" />
                            </span>
                        </article>
                    </Link>
                </li>
            {/each}
        </ul>
    </Section>
{/if}
