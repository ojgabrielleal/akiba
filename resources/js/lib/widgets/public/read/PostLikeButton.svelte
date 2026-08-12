<script>
    import { router } from "@inertiajs/svelte";
    import { Tooltip } from "@/lib/components/public";

    export let post = {};

    $: count = post.likes_count ?? 0;
    $: liked = Boolean(post.liked_by_me);

    const toggleLike = () => {
        router.post(`/materia/${post.slug}/like`, {}, {
            only: ["post"],
            preserveScroll: true,
        });
    };
</script>

<Tooltip position="right">
    <button
        type="button"
        aria-pressed={liked}
        aria-label={liked ? "Remover curtida" : "Curtir"}
        class={[
            "group/like inline-flex h-6 min-w-12 cursor-pointer items-center justify-center gap-1 rounded-md bg-orange-citric px-2 font-noto-sans text-base leading-none font-black text-suspense-aurora uppercase italic shadow-md shadow-blue-night/20 transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora motion-reduce:transform-none motion-reduce:transition-none",
            liked
                ? "brightness-110"
                : "brightness-100",
        ]}
        on:click={toggleLike}
    >
        <span>{count}</span>
        <img
            src="/svg/like.svg"
            alt=""
            aria-hidden="true"
            class="size-3.5 filter-suspense-aurora transition duration-300"
        />
    </button>
    <span slot="content">{liked ? "Remover curtida" : "Curtir matéria"}</span>
</Tooltip>
