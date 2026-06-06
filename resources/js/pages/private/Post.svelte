<script>
    import Cookies from "js-cookie";
    import { router, page } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { Section } from "@/ui/components/private";
    import { PostForm, ReviewForm, EventForm, PostGrid } from "@/ui/widgets/private";

    $: ({ post } = $page.props);
    
    let show = post ?? Cookies.get("akiba_post_show_editor");
    let form = post?.data.module ?? Cookies.get("akiba_post_module");

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
    <Section title="Criar" {actions} />
        {#if show}
            {#if form === 'post'}
                <PostForm />
            {:else if form === 'review'}
                <ReviewForm />
            {:else if form === 'event'}
                <EventForm />
            {/if}
        {/if}
        <PostGrid title="Todas as matérias, reviews e eventos"/>
    <Section/>
</Layout>
