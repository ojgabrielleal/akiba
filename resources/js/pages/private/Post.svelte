<script>
    import Cookies from "js-cookie";
    import { router, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import { PostForm, ReviewForm, EventForm, PostGrid } from "@/lib/widgets/private";

    $: ({ post, posts } = $page.props);
    
    let show = post ?? Cookies.get("akiba_post_show_editor");
    $: form = post?.data.module ?? Cookies.get("akiba_post_module");

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
    <Section title="Criar" {actions}>
        {#if show}
            {#if form === 'post'}
                <PostForm {post} />
            {:else if form === 'review'}
                <ReviewForm {post} />
            {:else if form === 'event'}
                <EventForm {post} />
            {/if}
        {/if}
    </Section>

    <PostGrid title="Todas as matérias, reviews e eventos" {posts} />
</Layout>
