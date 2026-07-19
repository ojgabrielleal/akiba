<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private/";
    import {
        WellcomeHero,
        ActivityCarousel,
        TaskList,
        PostGrid,
        CalendarGrid,
    } from "@/ui/widgets/private";

    $: ({ user } = $page.props);
</script>

<Meta meta={{ title: "Dashboard" } } />
<Layout>
    <WellcomeHero />
    <ActivityCarousel title="Avisos e Atividades" />
    <TaskList title="Minhas Tarefas" />
    <PostGrid title="Minhas últimas matérias" />
    <CalendarGrid title="Calendário" />
</Layout>
