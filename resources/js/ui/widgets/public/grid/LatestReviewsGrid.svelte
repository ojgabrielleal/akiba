<script>
    import { Link, page } from "@inertiajs/svelte";
    import { GridList, Section } from "@/ui/components/public";
    import { resolvePlaceholderImage } from "@/utils";

    $: latestReviews = $page.props.latestReviews?.data ?? [];
</script>

{#if latestReviews.length > 0}
    <Section title="Últimas reviews">
        <GridList preset="reviews" aria-label="Últimas reviews">
            {#each latestReviews as review (review.uuid)}
                <li class="min-w-0">
                    <Link
                        href={`review/${review.slug}`}
                        aria-label={`Ler review: ${review.title}`}
                        class="group block rounded-md focus-visible:outline-none"
                    >
                        <article class="overflow-hidden rounded-md bg-orange-amber transition duration-300 ease-out group-hover:-translate-y-1 group-hover:shadow-lg group-hover:shadow-blue-skywave/10 group-focus-visible:-translate-y-1 group-focus-visible:ring-2 group-focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none">
                            <img
                                src={resolvePlaceholderImage(review.cover, "placeholder")}
                                alt={review.title}
                                class="aspect-[3/2] w-full bg-neutral-gray object-cover transition duration-300 ease-out group-hover:scale-[1.03] group-focus-visible:scale-[1.03] motion-reduce:transform-none motion-reduce:transition-none"
                            />
                            <div class="px-2 py-2">
                                <h3 class="truncate text-center font-noto-sans text-base leading-tight font-black text-blue-night uppercase italic sm:text-lg">
                                    {review.title}
                                </h3>
                            </div>
                        </article>
                    </Link>
                </li>
            {/each}
        </GridList>
    </Section>
{/if}
