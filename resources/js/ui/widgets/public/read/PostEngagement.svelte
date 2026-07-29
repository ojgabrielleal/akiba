<script>
    import { router } from "@inertiajs/svelte";
    import { AuthGuard, Pagination } from "@/ui/components/public";
    import { resolvePlaceholderImage } from "@/utils";

    export let post = {};
    export let oauth = null;
    export let comments = null;

    $: commentList = comments?.data ?? [];
    $: totalComments = comments?.meta?.total ?? comments?.total ?? commentList.length;
    $: authorNickname = post.author?.nickname ?? post.author?.name ?? "Neko Kirame";
    $: authorName = post.author?.name ?? "Ellyson Santos de Castro";
    $: sources = (post.references ?? []).filter((source) => source.name && source.url);

    let comment = "";

    const submitComment = () => {
        router.post(`/materia/${post.slug}/comment`, {
            comment,
        }, {
            only: ["comments"],
            preserveScroll: true,
            onSuccess: () => {
                comment = "";
            },
        });
    };
</script>

<footer class="mt-9 grid items-stretch gap-x-4 gap-y-12 font-noto-sans uppercase md:grid-cols-[minmax(0,1fr)_24rem] md:gap-y-4">
    <div class="grid grid-rows-[auto_auto_1fr] gap-4">
        <h2 class="text-center text-xl leading-none font-normal text-orange-amber">
            Fontes de pesquisa
        </h2>
        <div class="grid gap-4">
            {#if sources.length}
                {#each sources as source}
                    <a href={source.url} class="flex min-h-12 items-center rounded-md bg-blue-cerulean px-4 py-3 text-sm font-black text-suspense-aurora" target="_blank" rel="noopener noreferrer" aria-label={source.name}>
                        {source.name}
                    </a>
                {/each}
            {:else}
                <p class="flex min-h-12 items-center rounded-md border border-blue-skywave/30 px-4 py-3 text-sm font-bold text-suspense-aurora/70">
                    Nenhuma fonte cadastrada.
                </p>
            {/if}
        </div>
        {#if post.created_at}
            <p class="flex items-center justify-end self-end text-right text-xl leading-none font-normal text-orange-amber">
                Postado:
                <span class="ml-2 rounded-sm bg-blue-cerulean px-3 py-1 text-xl font-black text-suspense-aurora italic">
                    {post.created_at}
                </span>
            </p>
        {/if}
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
                src={resolvePlaceholderImage(post.author?.avatar, "avatar", post.author?.gender)}
                alt=""
                aria-hidden="true"
                class="absolute -right-2 bottom-0 h-52 max-w-none object-contain"
            />
        </div>
    </div>
</footer>

<section class="mt-12 font-noto-sans">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl leading-none font-normal text-orange-amber uppercase">
            Comentários
        </h2>
        <span class="rounded-full bg-blue-cerulean px-3 py-1 text-xs font-black text-suspense-aurora uppercase italic">
            {totalComments}
        </span>
    </div>

    <AuthGuard
        {oauth}
        title="Entre para comentar"
        description="Use sua conta do Discord para participar da conversa."
        buttonLabel="Entrar com Discord"
        filters="filter-suspense-aurora"
        titleClass="text-suspense-aurora"
        descriptionClass="text-suspense-aurora/70"
        buttonClass="text-suspense-aurora"
    >
        <form class="grid gap-3" on:submit|preventDefault={submitComment}>
            <textarea
                bind:value={comment}
                name="comment"
                rows="4"
                maxlength="1000"
                placeholder="Escreva seu comentário..."
                class="min-h-28 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-ocean px-4 py-3 text-sm font-bold text-suspense-aurora placeholder:text-suspense-aurora/45 focus:outline-none"
            ></textarea>
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-full bg-orange-amber px-5 py-2.5 text-sm font-black text-blue-night uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transform-none motion-reduce:transition-none"
                    disabled={!comment.trim()}
                >
                    Comentar
                </button>
            </div>
        </form>
    </AuthGuard>

    <div class="mt-6 grid gap-4">
        {#if commentList.length}
            {#each commentList as item}
                <article class="rounded-md bg-blue-ocean p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <img
                            src={resolvePlaceholderImage(item.author?.avatar, "avatar")}
                            alt=""
                            aria-hidden="true"
                            class="size-10 rounded-full bg-blue-night object-cover"
                        />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-suspense-aurora uppercase italic">
                                {item.author?.name}
                            </p>
                            <p class="text-xs font-bold text-suspense-aurora/60">
                                {item.created_at}
                            </p>
                        </div>
                    </div>
                    <p class="whitespace-pre-line text-sm font-medium leading-relaxed text-suspense-aurora">
                        {item.comment}
                    </p>
                </article>
            {/each}
        {:else}
            <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                Ainda não há comentários por aqui.
            </p>
        {/if}
    </div>
    <Pagination pages={comments} only={["comments"]} loadingLabel="Carregando comentários..." />
</section>
