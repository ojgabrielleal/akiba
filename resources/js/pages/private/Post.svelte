<script>
    import Cookies from "js-cookie";
    import { onMount } from "svelte";
    import { Deferred, router, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Modal, Section } from "@/lib/components/private";
    import { AnimeNewsFeedGrid, PostCommentPanel, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts, canViewNewsFeed, newsFeedSources, selectedNewsFeedSource, newsFeedPosts } = $page.props);
    
    const pendingEditorKey = "akiba_post_pending_editor";

    function consumePendingEditor() {
        if (typeof window === "undefined") return false;

        const pending = window.sessionStorage.getItem(pendingEditorKey) === "true";
        window.sessionStorage.removeItem(pendingEditorKey);

        return pending;
    }

    function openEditor(module) {
        form = module;
        show = true;
        Cookies.set("akiba_post_module", module);
    }

    function closeEditor() {
        show = false;
        FormComponent = null;
        loadedForm = null;
        formLoadToken += 1;
    }

    async function loadFormComponent(module) {
        const token = ++formLoadToken;

        const component = await {
            post: () => import("@/lib/widgets/private/form/PostForm.svelte"),
            review: () => import("@/lib/widgets/private/form/ReviewForm.svelte"),
            event: () => import("@/lib/widgets/private/form/EventForm.svelte"),
        }[module]?.();

        if (token !== formLoadToken || !component) return;

        FormComponent = component.default;
        loadedForm = module;
    }

    let draft = null;
    let contentView = "posts";
    let show = Boolean(post) || consumePendingEditor();
    let FormComponent = null;
    let loadedForm = null;
    let formLoadToken = 0;
    let commentModalRef;
    $: form = post?.data.module ?? Cookies.get("akiba_post_module") ?? "post";
    $: if (post && !show) {
        show = true;
    }
    $: if (show && form && loadedForm !== form) {
        FormComponent = null;
        loadFormComponent(form);
    }
    $: if (!canViewNewsFeed && contentView === "feed") {
        contentView = "posts";
    }

    $: gridActions = canViewNewsFeed
        ? [
            {
                title: "Matérias, reviews e eventos da akiba",
                icon: "/svg/materials.svg",
                permission: true,
                background: contentView === "posts" ? "bg-blue-skywave" : "bg-neutral-gray/50",
                textColor: "text-suspense-aurora",
                filter: "filter-suspense-aurora",
                onClick: () => contentView = "posts",
            },
            {
                title: "Feed externo de notícias",
                icon: "/svg/news.svg",
                permission: true,
                background: contentView === "feed" ? "bg-blue-skywave" : "bg-neutral-gray/50",
                textColor: "text-suspense-aurora",
                filter: "filter-suspense-aurora",
                onClick: () => contentView = "feed",
            },
        ]
        : [];

    function createPostFromFeed(item) {
        draft = {
            title: item.title,
            content: item.content ?? item.excerpt,
            references: [
                {
                    uuid: null,
                    name: item.source.name,
                    url: item.url,
                },
            ],
        };

        openEditor("post");

        requestAnimationFrame(() => {
            document.getElementById("post-editor")?.scrollIntoView({ behavior: "smooth" });
        });
    }

    let operation = (module) => {
        draft = null;

        if (post) {
            Cookies.set("akiba_post_module", module);
            window.sessionStorage.setItem(pendingEditorKey, "true");
            router.visit("/panel/post");
            return;
        }

        openEditor(module);
    }

    let actions = [
        {
            title: "Matéria",
            icon: "/svg/materials.svg",
            permission: true,
            onClick: () => operation('post')
        },
        {
            title: "Review",
            icon: "/svg/reviews.svg",
            permission: true,
            onClick: () => operation('review')
        },
        {
            title: "Evento",
            icon: "/svg/events.svg",
            permission: true,
            onClick: () => operation('event')
        }
    ];


    let pageName = {
        'post': 'Matéria',
        'review': 'Review',
        'event': 'Evento'
    };

    $: metaTitle = show && pageName[form] ? pageName[form] : "Matérias";
    $: selectedPost = post?.data ?? null;
    $: selectedPostComments = selectedPost?.comments_count ?? 0;
    $: selectedPostCommentsLabel = selectedPostComments === 1 ? "1 comentário" : `${selectedPostComments} comentários`;
    $: selectedPostCommentList = selectedPost?.comments ?? [];
    $: commentPanelTitle = selectedPost?.title ?? "Comentários";

    onMount(() => router.on("start", (event) => {
        const visitUrl = event.detail.visit?.url;
        if (!visitUrl) return;

        const targetPath = new URL(visitUrl, window.location.origin).pathname;
        if (!targetPath.startsWith("/panel/post")) {
            closeEditor();
            window.sessionStorage.removeItem(pendingEditorKey);
        }
    }));
</script>

<Meta meta={{ title: metaTitle }} />
<Layout>
    <h1 class="sr-only">Posts</h1>
    <Section id="post-editor" title="Criar" {actions}>
        {#if show}
            {#if FormComponent}
                {#key post?.data?.uuid ?? form}
                    <svelte:component this={FormComponent} {post} {draft} />
                {/key}
            {:else}
                <div class="h-64 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            {/if}
        {/if}
    </Section>

    {#if selectedPost}
        <button
            type="button"
            class="fixed bottom-22 right-5 z-[210] flex min-h-11 cursor-pointer items-center gap-2.5 rounded-full border border-orange-citric/45 bg-blue-marinho/95 px-3 py-2 font-noto-sans text-suspense-aurora shadow-[0_0.75rem_1.75rem_rgba(0,0,20,0.32)] backdrop-blur-sm transition duration-300 ease-out hover:-translate-y-0.5 hover:border-orange-citric hover:bg-blue-ocean focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none lg:bottom-8 lg:right-8"
            aria-label={"Abrir comentários de " + commentPanelTitle}
            on:click={() => commentModalRef.open()}
            on:mousedown|preventDefault={() => commentModalRef.open()}
        >
            <span class="pointer-events-none relative flex size-7 items-center justify-center rounded-full bg-orange-citric text-sm font-black italic text-blue-marinho" aria-hidden="true">
                {selectedPostComments}
            </span>
            <span class="pointer-events-none relative hidden text-left text-[0.7rem] font-black uppercase italic leading-tight sm:block" aria-hidden="true">
                Comentários
            </span>
        </button>

        <Modal bind:this={commentModalRef} title="Comentários" size="md" flush>
            <div slot="content" class="overflow-x-hidden font-noto-sans text-blue-marinho">
                <PostCommentPanel
                    post={selectedPost}
                    comments={selectedPostCommentList}
                    total={selectedPostComments}
                />
            </div>
        </Modal>
    {/if}
    {#if contentView === "feed" && canViewNewsFeed}
        <Deferred data={["newsFeedSources", "newsFeedPosts"]}>
            <AnimeNewsFeedGrid
                title="Feed externo de notícias"
                actions={gridActions}
                sources={newsFeedSources}
                selectedSource={selectedNewsFeedSource}
                feedPosts={newsFeedPosts}
                onCreatePost={createPostFromFeed}
            />
            <svelte:fragment slot="fallback">
                <Section title="Feed externo de notícias" actions={gridActions}>
                    <div class="mb-5 h-10 w-56 animate-pulse rounded-full bg-blue-ocean/55" aria-hidden="true"></div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        {#each Array.from({ length: 6 }, (_, index) => index) as item}
                            <div class="h-82 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                        {/each}
                    </div>
                </Section>
            </svelte:fragment>
        </Deferred>
    {:else}
        <PostGrid title="Todas as matérias, reviews e eventos" posts={posts} actions={gridActions} />
    {/if}
</Layout>
