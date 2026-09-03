<script>
    import { router } from "@inertiajs/svelte";
    import { AuthGuard, Pagination } from "@/lib/components/public";
    import { themeClass } from "@/lib/utils";
    import PostCommentItem from "./PostCommentItem.svelte";

    export let commentable = {};
    export let oauth = null;
    export let comments = null;
    export let commentBasePath;

    $: commentList = comments?.data ?? [];
    $: totalComments = comments?.meta?.total ?? comments?.total ?? commentList.length;

    let comment = "";

    const submitComment = () => {
        router.post(`${commentBasePath}/comment`, {
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

<section class="mt-12 font-noto-sans">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl leading-none font-normal text-orange-amber uppercase">
            Comentários
        </h2>
        <span class={["rounded-full bg-blue-cerulean px-3 py-1 text-xs font-black uppercase italic", themeClass("bg", "neutral-light", { fixed: true, theme: "light" }), themeClass("text", "suspense-aurora", { fixed: true }), themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
            {totalComments}
        </span>
    </div>

    <AuthGuard
        {oauth}
        title="Entre para comentar"
        description="Use sua conta para participar da conversa."
        buttonLabel="Entrar"
        filters="filter-suspense-aurora"
        titleClass="text-suspense-aurora"
        descriptionClass="text-suspense-aurora/70"
        buttonClass="text-suspense-aurora"
        providersLayout="inline"
    >
        <form class="grid gap-3" on:submit|preventDefault={submitComment}>
            <textarea
                bind:value={comment}
                name="comment"
                rows="4"
                maxlength="1000"
                placeholder="Escreva seu comentário..."
                class={["public-comment-input min-h-28 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-ocean px-4 py-3 text-sm font-bold text-suspense-aurora focus:outline-none", themeClass("bg", "neutral-light", { fixed: true, theme: "light" }), themeClass("text", "blue-night", { fixed: true, theme: "light" }), themeClass("placeholder", "suspense-aurora", { fixed: true }), themeClass("placeholder", "blue-night/45", { theme: "light" }), "[[data-public-theme=light]_&]:border-blue-night/10"]}
            ></textarea>
            <div class="flex justify-end">
                <button
                    type="submit"
                    class={["rounded-full bg-orange-amber px-5 py-2.5 text-sm font-black uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transform-none motion-reduce:transition-none", themeClass("text", "blue-night", { fixed: true })]}
                    disabled={!comment.trim()}
                >
                    Comentar
                </button>
            </div>
        </form>
    </AuthGuard>

    <div class="mt-6">
        {#if commentList.length}
            <div class="grid gap-[14px]">
                {#each commentList as item}
                    <PostCommentItem post={commentable} {item} {oauth} {commentBasePath} />
                {/each}
            </div>
        {:else}
            <p class="public-comment-empty rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
                Ainda não há comentários por aqui.
            </p>
        {/if}
    </div>
    <Pagination pages={comments} only={["comments"]} loadingLabel="Carregando comentários..." />
</section>
