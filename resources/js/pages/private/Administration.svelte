<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import {
        ActivityCarousel,
        CalendarGrid,
        RoleCarousel,
        TaskList,
        UserGrid,
    } from "@/lib/widgets/private";

    $: ({ user, activities, calendar, roles, tasks, users } = $page.props);
</script>

<Meta meta={{ title: "ADM's" } } />
<Layout>
    <CalendarGrid title="Calendário" variant="administration" {calendar} {users} />
    <ActivityCarousel title="Avisos e atividades" variant="administration" {activities} {user} />
    <TaskList title="Tarefas da equipe" variant="administration" {tasks} {users} />
    <UserGrid title="Membros" variant="administration" {users} {roles} />
    <RoleCarousel title="Cargos" variant="administration" {roles} {permissions} />
</Layout>
