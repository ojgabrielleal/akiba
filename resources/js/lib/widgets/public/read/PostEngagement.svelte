<script>
    import CommentSection from "./CommentSection.svelte";
    import { resolvePlaceholderImage, themeClass } from "@/lib/utils";

    export let post = {};
    export let oauth = null;
    export let comments = null;
    export let showAuthor = true;
    export let showSources = true;
    export let showPublished = true;

    $: authorNickname = post.author?.nickname ?? post.author?.name ?? "Neko Kirame";
    $: authorName = post.author?.name ?? "Ellyson Santos de Castro";
    $: sources = (post.references ?? []).filter((source) => source.name && source.url).slice(0, 2);
</script>

{#if showAuthor || showSources || (showPublished && post.created_at)}
    <footer class="mt-9 grid items-stretch gap-x-4 gap-y-12 font-noto-sans uppercase md:grid-cols-[minmax(0,1fr)_24rem] md:gap-y-4">
        {#if showSources || (showPublished && post.created_at)}
            <div class="grid grid-rows-[auto_auto_1fr] gap-4">
                {#if showSources}
                    <h2 class="text-center text-xl leading-none font-normal text-orange-amber">
                        Fontes
                    </h2>
                    <div class="grid gap-4">
                        {#if sources.length}
                            {#each sources as source}
                                <a href={source.url} class={["flex min-h-12 items-center rounded-md bg-blue-cerulean px-4 py-3 text-sm font-black", themeClass("text", "suspense-aurora", { fixed: true })]} target="_blank" rel="noopener noreferrer" aria-label={source.name}>
                                    {source.name}
                                </a>
                            {/each}
                        {:else}
                            <p class="flex min-h-12 items-center rounded-md border border-blue-skywave/30 px-4 py-3 text-sm font-bold text-suspense-aurora/70">
                                Nenhuma fonte encontrada.
                            </p>
                        {/if}
                    </div>
                {/if}
                {#if showPublished && post.created_at}
                    <p class="flex items-center justify-end self-end text-right text-xl leading-none font-normal text-orange-amber">
                        Publicado
                        <span class={["ml-2 rounded-sm bg-blue-cerulean px-3 py-1 text-xl font-black italic", themeClass("text", "suspense-aurora", { fixed: true })]}>
                            {post.created_at}
                        </span>
                    </p>
                {/if}
            </div>
        {/if}

        {#if showAuthor}
            <div class="grid grid-rows-[auto_1fr] gap-4">
                <h2 class="text-xl leading-none font-normal text-orange-amber">
                    Autor
                </h2>
                <div class="relative h-full min-h-40 overflow-visible rounded-md bg-blue-cerulean p-3">
                    <div class="relative z-10 max-w-[58%] pt-2 leading-none">
                        <p class={["font-noto-sans text-[1.65rem] font-black italic", themeClass("text", "suspense-aurora", { fixed: true })]}>
                            {authorNickname}
                        </p>
                        <p class={["mt-1 text-[0.8rem] leading-none font-black italic", themeClass("text", "suspense-aurora", { fixed: true })]}>
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
        {/if}
    </footer>
{/if}

<CommentSection commentable={post} {oauth} {comments} commentBasePath={`/materia/${post.slug}`} />
