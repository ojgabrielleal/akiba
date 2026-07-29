<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { postReactions } from "@/lib/constants";
    import { AuthGuard, Tooltip } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import PostEngagement from "@/lib/widgets/public/read/PostEngagement.svelte";
    import { resolvePlaceholderImage } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, post, comments, relatedPosts } = $page.props);

    $: pageUrl = $page.url;
    $: review = post?.data ?? {};
    $: related = relatedPosts?.data ?? [];
    $: reviews = review.reviews ?? [];
    $: activeReview = reviews[0]?.uuid ?? reviews[0]?.author?.uuid ?? null;
    $: selectedReview = reviews.find((item) => (item.uuid ?? item.author?.uuid) === activeReview) ?? reviews[0];
    $: reactionCounts = (review.reactions ?? []).reduce((counts, reaction) => {
        counts[reaction.name] = (counts[reaction.name] ?? 0) + 1;
        return counts;
    }, {});

    const submitReaction = (reaction) => {
        router.post(`/materia/${review.slug}/reaction`, {
            name: reaction.name,
        }, {
            only: ["post"],
            preserveScroll: true,
        });
    };
</script>

<Meta meta={{ title: review.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-5 pb-10">
        <div class="bg-blue-marinho">
            <div class="container-page grid gap-8 py-8 lg:grid-cols-[minmax(0,1fr)_15rem]">
                <article class="min-w-0">
                    <h1 class="mb-5 rounded-md bg-orange-amber px-3 py-2 font-noto-sans text-xl font-black leading-tight text-blue-night uppercase italic sm:text-2xl">
                        {review.title}
                    </h1>

                    <img
                        src={resolvePlaceholderImage(review.cover, "placeholder")}
                        alt=""
                        aria-hidden="true"
                        class="mb-5 h-56 w-full rounded-md bg-neutral-gray object-cover sm:h-72 lg:h-[26rem]"
                    />

                    {#if review.metadata?.year_of_release}
                        <dl class="mb-6 grid gap-3 font-noto-sans uppercase">
                            <div class="rounded-md bg-blue-ocean px-4 py-3">
                                <dt class="mb-1 text-xs font-black text-blue-skywave">Ano de lançamento</dt>
                                <dd class="text-lg font-black text-suspense-aurora italic">{review.metadata.year_of_release}</dd>
                            </div>
                        </dl>
                    {/if}

                    <section class="font-noto-sans text-suspense-aurora">
                        <h2 class="mb-3 text-xl leading-none font-normal text-orange-amber uppercase">
                            Sinopse
                        </h2>
                        {#if review.metadata?.sinopse}
                            <div>{@html review.metadata.sinopse}</div>
                        {:else}
                            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                                Sinopse em breve.
                            </p>
                        {/if}
                    </section>

                    <section class="mt-6 font-noto-sans">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <h2 class="text-xl leading-none font-normal text-orange-amber uppercase">
                                Reviews da tripulação Akiba
                            </h2>
                        </div>

                        {#if reviews.length}
                            <div>
                                <nav class="flex flex-wrap gap-2" aria-label="Reviews">
                                    {#each reviews as item}
                                        <button
                                            type="button"
                                            class={[
                                                "cursor-pointer rounded-md px-4 py-2 font-noto-sans text-sm font-black uppercase italic transition duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                                (item.uuid ?? item.author?.uuid) === activeReview
                                                    ? "bg-orange-amber text-blue-night"
                                                    : "bg-blue-ocean text-suspense-aurora hover:bg-blue-cerulean",
                                            ]}
                                            on:click={() => activeReview = item.uuid ?? item.author?.uuid}
                                        >
                                            {item.author?.nickname ?? item.author?.name ?? "Review"}
                                        </button>
                                    {/each}
                                </nav>
                            </div>

                            <article class="pt-5 text-suspense-aurora">
                                <div class="mb-4 flex items-center gap-3">
                                    <img
                                        src={resolvePlaceholderImage(selectedReview.author?.avatar, "avatar", selectedReview.author?.gender)}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-12 rounded-full bg-blue-night object-cover"
                                    />
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-black uppercase italic">
                                            {selectedReview.author?.nickname ?? selectedReview.author?.name}
                                        </p>
                                    </div>
                                </div>
                                {#if selectedReview.content}
                                    <div>{@html selectedReview.content}</div>
                                {:else}
                                    <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora">
                                        Review ainda não publicada.
                                    </p>
                                {/if}
                            </article>
                        {:else}
                            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                                Ainda não há reviews por aqui.
                            </p>
                        {/if}
                    </section>

                    <section class="mt-8 flex min-h-28 flex-wrap items-center justify-center gap-x-5 gap-y-4 py-4 font-noto-sans uppercase">
                        <p class="max-w-44 text-center text-lg leading-tight font-normal text-orange-amber">
                            O que você achou dessas reviews?
                        </p>
                        <AuthGuard
                            {oauth}
                            buttonLabel="Entre com o Discord para reagir"
                            compact
                        >
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                {#each postReactions as reaction}
                                    <Tooltip position="bottom">
                                        <button
                                            type="button"
                                            aria-label={reaction.label}
                                            class="group/reaction relative flex size-21 cursor-pointer items-center justify-center rounded-full transition duration-300 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                                            on:click={() => submitReaction(reaction)}
                                        >
                                            <img src={reaction.image} alt="" aria-hidden="true" class="size-18" />
                                            <span class="absolute -right-1 -bottom-1 min-w-6 rounded-full bg-blue-skywave px-1.5 py-0.5 text-center font-noto-sans text-xs font-black text-suspense-aurora">
                                                {reactionCounts[reaction.name] ?? 0}
                                            </span>
                                        </button>
                                        <span slot="content">{reaction.label}</span>
                                    </Tooltip>
                                {/each}
                            </div>
                        </AuthGuard>
                    </section>

                    <PostEngagement post={review} {oauth} {comments} />
                </article>

                <aside class="min-w-0">
                    <h2 class="mb-6 flex flex-col items-center gap-1 font-noto-sans leading-none font-black text-orange-amber uppercase italic">
                        <span class="whitespace-nowrap text-sm text-suspense-aurora">Veja mais:</span>
                        <span class="mt-1 flex items-center justify-center gap-2 text-2xl">
                            <img src="/svg/reviews.svg" alt="" aria-hidden="true" class="size-8 filter-orange-amber" />
                            <span class="whitespace-nowrap">Reviews</span>
                        </span>
                    </h2>
                    <ul class="grid gap-5">
                        {#each related as item}
                            <li class="border-t border-suspense-aurora/45 pt-5 first:border-t-0 first:pt-0">
                                <Link href={item.href} class="group block rounded-md transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none">
                                    <div class="aspect-[3/2] rounded-md bg-neutral-gray">
                                        <img src={resolvePlaceholderImage(item.cover, "placeholder")} alt="" aria-hidden="true" class="h-full w-full rounded-md object-cover transition duration-300 ease-out group-hover:scale-[1.02] group-focus-visible:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none" />
                                    </div>
                                    <h3 class="mt-2 line-clamp-4 font-noto-sans text-base font-black leading-tight text-orange-amber uppercase italic">
                                        {item.title}
                                    </h3>
                                </Link>
                            </li>
                        {/each}
                    </ul>
                </aside>
            </div>
        </div>
    </section>
</Layout>
