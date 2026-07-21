<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import {
        ActivityCarousel,
        CalendarGrid,
        RoleCarousel,
        TaskList,
        UserGrid,
    } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "ADM's" } } />
<Layout>
    <CalendarGrid title="Calendário" variant="administration" />
    <ActivityCarousel title="Avisos e atividades" variant="administration" />
    <TaskList title="Tarefas da equipe" variant="administration" />
    <UserGrid title="Membros" variant="administration" />
    <RoleCarousel title="Cargos" variant="administration" />
</Layout>
