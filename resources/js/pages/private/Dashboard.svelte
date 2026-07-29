<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private/";
    import {
        WellcomeHero,
        ActivityCarousel,
        TaskList,
        PostGrid,
        CalendarGrid,
    } from "@/lib/widgets/private";

    $: ({ user, activities, tasks, posts, calendar } = $page.props);
</script>

<Meta meta={{ title: "Dashboard" } } />
<Layout>
    <WellcomeHero />
    <ActivityCarousel title="Avisos e Atividades" {activities} {user} />
    <TaskList title="Minhas Tarefas" {tasks} />
    <PostGrid title="Minhas últimas matérias" {posts} />
    <CalendarGrid title="Calendário" {calendar} />
</Layout>
