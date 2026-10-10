<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { fade } from "svelte/transition";
    import { Meta } from "@/lib/components/shared";
    import { postReactions } from "@/lib/constants";
    import { AdvertisementSlot, AuthGuard, Tooltip } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { PostEngagement, PostLikeButton } from "@/lib/widgets/public";
    import { resolveDate, resolvePlaceholderImage, themeClass } from "@/lib/utils";

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


    const reviewerName = (item) => item.author?.nickname ?? item.author?.name ?? "Review";

    const reviewerInitials = (item) => reviewerName(item)
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
</script>

<Meta meta={{ title: review.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-read-content-background bg-blue-marinho pt-5 pb-2">
        <div class="public-read-content-background bg-blue-marinho">
            <div class={[
                "container-page grid gap-8 py-8",
                "lg:grid-cols-[minmax(0,1fr)_15rem]",
            ]}>
                <article class="min-w-0">
                    <div class="mb-5 rounded-md bg-orange-citric px-3 py-2">
                        <h1 class={["font-noto-sans text-xl font-black leading-tight uppercase italic sm:text-2xl", themeClass("text", "blue-night", { fixed: true })]}>
                            {review.title}
                        </h1>
                    </div>

                    <div class="relative mb-5">
                        <div class="absolute -right-3 top-3 z-10">
                            <PostLikeButton post={review} />
                        </div>
                        <img
                            src={resolvePlaceholderImage(review.cover, "placeholder")}
                            alt=""
                            aria-hidden="true"
                            class="w-full rounded-md bg-neutral-gray"
                        />
                    </div>

                    {#if review.metadata?.date_of_release || review.metadata?.year_of_release || review.metadata?.studio}
                        <dl class="mb-6 grid gap-3 font-noto-sans uppercase md:grid-cols-2">
                            {#if review.metadata?.date_of_release || review.metadata?.year_of_release}
                                <div class="public-review-date-band rounded-md bg-blue-ocean px-4 py-3">
                                    <dt class={["mb-1 text-xs font-black text-blue-skywave", themeClass("text", "neutral-white/80", { theme: "light" })]}>Data de lançamento</dt>
                                    <dd class={["text-lg font-black text-suspense-aurora italic", themeClass("text", "neutral-white", { theme: "light" })]}>
                                        {resolveDate(review.metadata.date_of_release ?? review.metadata.year_of_release)}
                                    </dd>
                                </div>
                            {/if}
                            {#if review.metadata?.studio}
                                <div class="min-w-0 rounded-md bg-blue-ocean px-4 py-3">
                                    <dt class={["mb-1 text-xs font-black text-blue-skywave", themeClass("text", "neutral-white/80", { theme: "light" })]}>Estúdio</dt>
                                    <dd class={["truncate text-lg font-black text-suspense-aurora italic", themeClass("text", "neutral-white", { theme: "light" })]}>{review.metadata.studio}</dd>
                                </div>
                            {/if}
                        </dl>
                    {/if}

                    <section class="font-noto-sans text-suspense-aurora">
                        <h2 class="mb-3 text-xl leading-none font-normal text-orange-amber uppercase">
                            Sinopse
                        </h2>
                        {#if review.metadata?.sinopse}
                            <div class="public-read-body text-[1.1875rem]">{@html review.metadata.sinopse}</div>
                        {:else}
                            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                                Sinopse em breve.
                            </p>
                        {/if}
                    </section>

                    <section class="mt-6 font-noto-sans">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <h2 class="text-xl leading-none font-normal text-orange-amber uppercase">
                                Reviews da Akiba
                            </h2>
                        </div>

                        {#if reviews.length}
                            <div>
                                <nav class="flex flex-wrap gap-2.5" aria-label="Reviews">
                                    {#each reviews as item}
                                        {@const reviewKey = item.uuid ?? item.author?.uuid}
                                        {@const active = reviewKey === activeReview}
                                        <button
                                            type="button"
                                            class={[
                                                "group/review-tab flex min-h-10 cursor-pointer items-center gap-2 rounded-full border px-3.5 py-2 font-noto-sans text-xs font-black uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                                active
                                                    ? `border-orange-citric bg-orange-citric shadow-[0_0.75rem_1.5rem_rgba(255,128,0,0.18)] ${themeClass("text", "blue-night", { fixed: true })}`
                                                    : "border-blue-skywave/25 bg-blue-ocean/60 text-suspense-aurora hover:text-orange-citric",
                                                themeClass("border", "blue-night", { theme: "light" }),
                                                themeClass("text", "neutral-white", { theme: "light" }),
                                            ]}
                                            on:click={() => activeReview = reviewKey}
                                        >
                                            <span
                                                class={[
                                                    "flex size-6 shrink-0 items-center justify-center rounded-full text-[0.62rem] font-black not-italic transition duration-300",
                                                    active
                                                        ? ["bg-blue-night text-orange-citric", themeClass("text", "blue-night", { theme: "light" })]
                                                        : ["bg-blue-night/45 text-orange-morning", themeClass("text", "neutral-white", { theme: "light" })],
                                                ]}
                                            >
                                                {reviewerInitials(item)}
                                            </span>
                                            <span class="min-w-0 max-w-40 truncate">{reviewerName(item)}</span>
                                        </button>
                                    {/each}
                                </nav>
                            </div>

                            <div class="grid">
                                {#key activeReview}
                                    <div class="col-start-1 row-start-1" in:fade={{ duration: 160 }} out:fade={{ duration: 90 }}>
                                        {#if selectedReview}
                                        <aside class="mt-4 flex items-center gap-3.5 rounded-md border border-blue-skywave/25 bg-blue-ocean/45 px-4 py-3 font-noto-sans [[data-public-theme=light]_&]:border-blue-night/10 [[data-public-theme=light]_&]:bg-neutral-light">
                                            <div class="size-14 shrink-0 overflow-hidden rounded-full bg-blue-night">
                                                <img
                                                    src={resolvePlaceholderImage(selectedReview.author?.avatar, "avatar", selectedReview.author?.gender)}
                                                    alt=""
                                                    aria-hidden="true"
                                                    class="h-full w-full scale-125 object-cover object-top"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div class="min-w-0 text-left">
                                                <p class={["text-[0.68rem] font-black leading-tight uppercase italic tracking-[0.14em] text-orange-citric", themeClass("text", "neutral-white/80", { theme: "light" })]}>
                                                    Review por
                                                </p>
                                                <p class={[
                                                    "mt-[0.2rem] truncate text-[0.95rem] font-black leading-tight uppercase italic text-suspense-aurora",
                                                    themeClass("text", "neutral-white", { theme: "light" }),
                                                ]}>
                                                    {reviewerName(selectedReview)}
                                                </p>
                                            </div>
                                        </aside>
                                    {/if}

                                    <article class="pt-5 text-suspense-aurora">
                                        {#if selectedReview.content}
                                            <div class="public-read-body text-[1.1875rem]">{@html selectedReview.content}</div>
                                        {:else}
                                            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora">
                                                Review ainda não publicada.
                                            </p>
                                        {/if}
                                        </article>
                                    </div>
                                {/key}
                            </div>
                        {:else}
                            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                                Ainda não há reviews por aqui.
                            </p>
                        {/if}
                    </section>

                    <section class="mt-8 grid min-h-28 gap-5 py-4 font-noto-sans uppercase">
                        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-4">
                            <p class="max-w-44 text-center text-lg leading-tight font-normal text-orange-amber">
                                O que você achou dessas reviews?
                            </p>
                            <AuthGuard
                                {oauth}
                                buttonLabel="Entrar"
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
                                                <span class={["absolute -right-1 -bottom-1 min-w-6 rounded-full bg-blue-skywave px-1.5 py-0.5 text-center font-noto-sans text-xs font-black", themeClass("text", "suspense-aurora", { fixed: true })]}>
                                                    {reactionCounts[reaction.name] ?? 0}
                                                </span>
                                            </button>
                                            <span slot="content">{reaction.label}</span>
                                        </Tooltip>
                                    {/each}
                                </div>
                            </AuthGuard>
                        </div>
                    </section>

                    <PostEngagement post={review} {oauth} {comments} showAuthor={false} showSources={false} showPublished={false} />
                </article>

                <aside class="min-w-0">
                    {#if related.length}
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
                    {/if}
                    <AdvertisementSlot class={related.length ? "mt-8 h-96 lg:h-[34rem]" : "h-96 lg:h-[34rem]"} />
                </aside>
            </div>
        </div>
    </section>
</Layout>
