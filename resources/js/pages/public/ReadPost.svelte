<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { postReactions, postTags } from "@/data";
    import { Tooltip } from "@/ui/components/public";
    import { Layout } from "@/ui/layouts/public";
    import { resolvePlaceholderImage } from "@/utils";

    $: ({ flash, oauth, onair, stream, post, relatedPosts } = $page.props);

    $: pageUrl = $page.url;
    $: article = post?.data ?? {};
    $: related = relatedPosts?.data ?? [];
    $: primaryTag = article.tags?.[0]?.name;
    $: relatedLabel = postTags[primaryTag]?.label ?? "Novidades";
    $: relatedIcon = postTags[primaryTag]?.icon ?? "/svg/news.svg";
    $: authorNickname = article.author?.nickname ?? article.author?.name ?? "Neko Kirame";
    $: authorName = article.author?.name ?? "Ellyson Santos de Castro";
    $: reactionCounts = (article.reactions ?? []).reduce((counts, reaction) => {
        counts[reaction.name] = (counts[reaction.name] ?? 0) + 1;
        return counts;
    }, {});

    const submitReaction = (reaction) => {
        router.post(`/materia/${article.slug}/reaction`, {
            name: reaction.name,
        }, {
            only: ["post"],
            preserveScroll: true,
        });
    };
</script>

<Meta meta={{ title: article.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <section class="bg-blue-night pt-5 pb-10">
        <div class="bg-blue-marinho">
            <div class="container-page grid gap-8 py-8 lg:grid-cols-[minmax(0,1fr)_15rem]">
            <article class="min-w-0">
                <h1 class="mb-5 rounded-md bg-orange-amber px-3 py-2 font-noto-sans text-xl font-black leading-tight text-blue-night uppercase italic sm:text-2xl">
                    {article.title ?? "Quem fez esse bagulho se parecer com outro bagulho que parece com esse bagulho? Que doido"}
                </h1>

                <img
                    src={resolvePlaceholderImage(article.cover, "placeholder")}
                    alt=""
                    aria-hidden="true"
                    class="mb-5 w-full rounded-md bg-neutral-gray"
                />

                {#if article.content}
                    <div class="font-noto-sans text-suspense-aurora">
                        {@html article.content}
                    </div>
                {:else}
                    <div class="space-y-5 font-noto-sans text-base font-bold leading-tight text-suspense-aurora">
                        <p>Conteúdo da matéria em breve.</p>
                    </div>
                {/if}

                <section class="mt-10 flex flex-wrap items-center justify-center gap-x-5 gap-y-4 py-8 font-noto-sans uppercase">
                    <p class="max-w-44 text-center text-lg leading-tight font-normal text-orange-amber">
                        O que você achou dessa matéria?
                    </p>
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
                </section>

                <footer class="mt-9 grid items-stretch gap-x-4 gap-y-4 font-noto-sans uppercase md:grid-cols-[minmax(0,1fr)_24rem]">
                    <div class="grid grid-rows-[auto_auto_1fr] gap-4">
                        <h2 class="text-center text-xl leading-none font-normal text-orange-amber">
                            Fontes de pesquisa
                        </h2>
                        <div class="grid gap-4">
                            {#each (article.references ?? [{ uuid: "source-a", name: "", url: "#" }, { uuid: "source-b", name: "", url: "#" }]) as source}
                                <a href={source.url} class="flex min-h-12 items-center rounded-md bg-blue-cerulean px-4 py-3 text-sm font-black text-suspense-aurora" target="_blank" rel="noopener noreferrer" aria-label={source.name || "Fonte de pesquisa"}>
                                    {source.name}
                                </a>
                            {/each}
                        </div>
                        <p class="flex items-center justify-end self-end text-right text-xl leading-none font-normal text-orange-amber">
                            Postado:
                            <span class="ml-2 rounded-sm bg-blue-cerulean px-3 py-1 text-xl font-black text-suspense-aurora italic">
                                25/12/24
                            </span>
                        </p>
                    </div>

                    <div class="grid grid-rows-[auto_1fr] gap-4">
                        <h2 class="text-xl leading-none font-normal text-orange-amber">
                            Autor
                        </h2>
                        <div class="relative h-full min-h-40 overflow-visible rounded-md bg-blue-cerulean p-3">
                            <div class="relative z-10 max-w-[58%] pt-2 leading-none">
                                <p class="font-noto-sans text-[1.65rem] font-black text-suspense-aurora italic">
                                    {authorNickname}
                                </p>
                                <p class="mt-1 text-[0.8rem] leading-none font-black text-suspense-aurora italic">
                                    ({authorName})
                                </p>
                            </div>
                            <img
                                src={resolvePlaceholderImage(article.author?.avatar, "avatar", article.author?.gender)}
                                alt=""
                                aria-hidden="true"
                                class="absolute -right-2 bottom-0 h-52 max-w-none object-contain"
                            />
                        </div>
                    </div>

                </footer>
            </article>

            <aside class="min-w-0">
                <h2 class="mb-6 flex flex-col items-center gap-1 font-noto-sans leading-none font-black text-orange-amber uppercase italic">
                    <span class="whitespace-nowrap text-sm text-suspense-aurora">Veja mais:</span>
                    <span class="mt-1 flex items-center justify-center gap-2 text-2xl">
                        <img src={relatedIcon} alt="" aria-hidden="true" class="size-8 filter-orange-amber" />
                        <span class="whitespace-nowrap">{relatedLabel}</span>
                    </span>
                </h2>
                <ul class="grid gap-5">
                    {#each related as item}
                        <li class="border-t border-suspense-aurora/45 pt-5 first:border-t-0 first:pt-0">
                            <Link
                                href={item.href}
                                class="group block rounded-md transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <div class="aspect-[3/2] rounded-md bg-neutral-gray">
                                    <img
                                        src={resolvePlaceholderImage(item.cover, "placeholder")}
                                        alt=""
                                        aria-hidden="true"
                                        class="h-full w-full rounded-md object-cover transition duration-300 ease-out group-hover:scale-[1.02] group-focus-visible:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none"
                                    />
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
