<script>
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { UserGrid, CalendarGrid, RoleTable, ActivityCarousel, TaskGrid } from "@/ui/widgets/private";
</script>

<Meta meta={{ title: "ADM's" } } />
<Layout>
    <UserGrid title="Membros" />
    <RoleTable title="Cargos" />
    <CalendarGrid title="Calendário" variant="administration" />
    <ActivityCarousel title="Avisos e atividades" variant="administration" />
    <TaskGrid title="Tarefas" />
</Layout>
