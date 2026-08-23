<script>
    import Cookies from "js-cookie";
    import { router, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { AnimeNewsFeedGrid, PostForm, ReviewForm, EventForm, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts, newsFeedSources, selectedNewsFeedSource, newsFeedPosts } = $page.props);
    
    let draft = null;
    let contentView = "posts";
    let show = post ?? Cookies.get("akiba_post_show_editor");
    $: form = post?.data.module ?? Cookies.get("akiba_post_module");
    $: canViewNewsFeed = Boolean(newsFeedSources && newsFeedPosts);
    $: if (!canViewNewsFeed && contentView === "feed") {
        contentView = "posts";
    }

    $: gridActions = canViewNewsFeed
        ? [
            {
                title: "Matérias, reviews e eventos da akiba",
                icon: "/svg/materials.svg",
                permission: true,
                background: contentView === "posts" ? "bg-orange-amber" : "bg-blue-skywave",
                onClick: () => contentView = "posts",
            },
            {
                title: "Feed externo de notícias",
                icon: "/svg/news.svg",
                permission: true,
                background: contentView === "feed" ? "bg-orange-amber" : "bg-blue-skywave",
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

        form = "post";
        show = true;

        Cookies.set("akiba_post_module", "post");
        Cookies.set("akiba_post_show_editor", true);

        requestAnimationFrame(() => {
            document.getElementById("post-editor")?.scrollIntoView({ behavior: "smooth" });
        });
    }

    let operation = (module) => {
        form = module;
        show ? router.visit("/panel/post") : show = true;

        Cookies.set("akiba_post_module", module);
        if(!Cookies.get("akiba_post_show_editor")){
            Cookies.set("akiba_post_show_editor", true);
        }
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
</script>

<Meta meta={{ title: pageName[form]}} />
<Layout>
    <h1 class="sr-only">Posts</h1>
    <Section id="post-editor" title="Criar" {actions}>
        {#if show}
            {#key post?.data?.uuid ?? form}
                {#if form === 'post'}
                    <PostForm {post} {draft} />
                {:else if form === 'review'}
                    <ReviewForm {post} />
                {:else if form === 'event'}
                    <EventForm {post} />
                {/if}
            {/key}
        {/if}
    </Section>

    {#if contentView === "feed" && canViewNewsFeed}
        <AnimeNewsFeedGrid
            title="Feed externo de notícias"
            actions={gridActions}
            sources={newsFeedSources}
            selectedSource={selectedNewsFeedSource}
            feedPosts={newsFeedPosts}
            onCreatePost={createPostFromFeed}
        />
    {:else}
        <PostGrid title="Todas as matérias, reviews e eventos" posts={posts} actions={gridActions} />
    {/if}
</Layout>
