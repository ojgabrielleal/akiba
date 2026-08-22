<script>
    import { Link } from "@inertiajs/svelte";
    import { publicAnimations } from "@/lib/constants";
    import { GridList, Section } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let reviews = [];
    export let title = null;
    export let styles = "container-page mb-8 pt-2 pb-4 lg:mb-10 lg:pt-4 lg:pb-10";
    export let ariaLabel = title ?? "Reviews";

    $: reviewList = Array.isArray(reviews) ? reviews : reviews?.data ?? [];
</script>

{#if reviewList.length > 0}
    <Section {title} {styles}>
        <GridList preset="reviews" aria-label={ariaLabel}>
            {#each reviewList as review (review.uuid)}
                <li class="min-w-0">
                    <Link
                        href={`/review/${review.slug}`}
                        aria-label={`Ler review: ${review.title}`}
                        class="group block rounded-md focus-visible:outline-none"
                    >
                        <article class={["overflow-hidden rounded-md bg-orange-amber group-focus-visible:ring-2 group-focus-visible:ring-orange-amber", publicAnimations.cardInteractive]}>
                            <img
                                src={resolvePlaceholderImage(review.cover, "placeholder")}
                                alt={review.title}
                                class={["aspect-[3/2] w-full bg-neutral-gray object-cover", publicAnimations.imageZoom]}
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
