<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { postReactions } from "@/lib/constants";
    import { AdvertisementSlot, AuthGuard, Button, Modal, Tooltip } from "@/lib/components/public";
    import { Layout } from "@/lib/layouts/public";
    import { EventRegistrationForm, PostEngagement, PostLikeButton } from "@/lib/widgets/public";
    import { resolvePlaceholderImage, themeClass } from "@/lib/utils";

    $: ({ flash, oauth, onair, stream, post, comments, relatedPosts } = $page.props);

    $: pageUrl = $page.url;
    $: event = post?.data ?? {};
    $: related = relatedPosts?.data ?? [];
    $: reactionCounts = (event.reactions ?? []).reduce((counts, reaction) => {
        counts[reaction.name] = (counts[reaction.name] ?? 0) + 1;
        return counts;
    }, {});
    let registrationModalRef;

    const submitReaction = (reaction) => {
        router.post(`/materia/${event.slug}/reaction`, {
            name: reaction.name,
        }, {
            only: ["post"],
            preserveScroll: true,
        });
    };
</script>

<Meta meta={{ title: event.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <Modal bind:this={registrationModalRef} title="Informar evento" size="sm">
        <EventRegistrationForm {event} close={() => registrationModalRef.close()} />
    </Modal>

    <section class="public-read-content-background bg-blue-marinho pt-5 pb-2">
        <div class="public-read-content-background bg-blue-marinho">
            <div class={[
                "container-page grid gap-8 py-8",
                "lg:grid-cols-[minmax(0,1fr)_15rem]",
            ]}>
                <article class="min-w-0">
                    <div class="mb-5 rounded-md bg-orange-citric px-3 py-2">
                        <h1 class={["font-noto-sans text-xl font-black leading-tight uppercase italic sm:text-2xl", themeClass("text", "blue-night", { fixed: true })]}>
                            {event.title}
                        </h1>
                    </div>

                    <div class="relative mb-5">
                        <div class="absolute -right-3 top-3 z-10">
                            <PostLikeButton post={event} />
                        </div>
                        <img
                            src={resolvePlaceholderImage(event.cover, "placeholder")}
                            alt=""
                            aria-hidden="true"
                            class="w-full rounded-md bg-neutral-gray"
                        />
                    </div>

                    <dl class="mb-6 grid gap-3 font-noto-sans uppercase md:grid-cols-2">
                        <div class="min-w-0 rounded-md bg-blue-ocean px-4 py-3">
                            <dt class="mb-1 text-xs font-black text-blue-skywave">Data</dt>
                            <dd class="truncate text-lg font-black text-suspense-aurora italic">{event.metadata?.dates ?? "Data em breve"}</dd>
                        </div>
                        <div class="min-w-0 rounded-md bg-blue-ocean px-4 py-3">
                            <dt class="mb-1 text-xs font-black text-blue-skywave">Local</dt>
                            <dd class="truncate text-lg font-black text-suspense-aurora italic">{event.metadata?.address ?? "Local em breve"}</dd>
                        </div>
                    </dl>

                    <div class="mb-8 flex justify-center">
                        <Button type="button" size="lg" on:click={() => registrationModalRef.open()}>
                            <img src="/svg/plus.svg" alt="" aria-hidden="true" class="w-6 filter-blue-marinho" />
                            <span class="text-[#000036]">Cadastrar nesse evento</span>
                        </Button>
                    </div>

                    {#if event.content}
                        <div class="public-read-body font-noto-sans text-[1.1875rem] text-suspense-aurora">
                            {@html event.content}
                        </div>
                    {:else}
                        <div class="space-y-5 font-noto-sans text-[1.1875rem] font-bold leading-tight text-suspense-aurora">
                            <p>Informações do evento em breve.</p>
                        </div>
                    {/if}

                    <section class="mt-8 grid min-h-28 gap-5 py-4 font-noto-sans uppercase">
                        <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-4">
                            <p class="max-w-44 text-center text-lg leading-tight font-normal text-orange-amber">
                                O que você achou desse evento?
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

                    <PostEngagement post={event} {oauth} {comments} />
                </article>

                <aside class="min-w-0">
                    {#if related.length}
                        <h2 class="mb-6 flex flex-col items-center gap-1 font-noto-sans leading-none font-black text-orange-amber uppercase italic">
                            <span class="whitespace-nowrap text-sm text-suspense-aurora">Veja mais:</span>
                            <span class="mt-1 flex items-center justify-center gap-2 text-2xl">
                                <img src="/svg/events.svg" alt="" aria-hidden="true" class="size-8 filter-orange-amber" />
                                <span class="whitespace-nowrap">Eventos</span>
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
