<script>
    import { Deferred, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { Section } from "@/lib/components/private";
    import {
        ActivityCarousel,
        BadgeGrid,
        BlockedRequesterList,
        CalendarGrid,
        RoleCarousel,
        TaskList,
        UserGrid,
        FormSubmissionList,
    } from "@/lib/widgets/private";

    $: ({ user, activities, calendar, roles, permissions, badgeSearch, badges, badgeTargets, tasks, users, formSubmissions, blockedRequesters, oauthAccounts } = $page.props);

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);
</script>

<Meta meta={{ title: "ADM's" } } />
<Layout>
    <Deferred data={["calendar", "activities"]}>
        <CalendarGrid title="Calendário" variant="administration" {calendar} {users} />
        <ActivityCarousel title="Avisos e atividades" variant="administration" {activities} {user} />
        <svelte:fragment slot="fallback">
            <Section title="Calendário">
                <div class="h-64 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            </Section>
            <Section title="Avisos e atividades">
                <div class="flex gap-3 overflow-hidden">
                    {#each skeletonItems(4) as item}
                        <div class="h-44 w-56 shrink-0 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <Deferred data={["tasks", "formSubmissions"]}>
        <TaskList title="Tarefas da equipe" variant="administration" {tasks} {users} />
        <FormSubmissionList title="Formulários recebidos" submissions={formSubmissions} />
        <svelte:fragment slot="fallback">
            <Section title="Tarefas da equipe">
                <div class="grid gap-3">
                    {#each skeletonItems(5) as item}
                        <div class="h-16 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
            <Section title="Formulários recebidos">
                <div class="grid gap-3">
                    {#each skeletonItems(6) as item}
                        <div class="h-20 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
    <UserGrid title="Membros" variant="administration" {users} {roles} />
    <BlockedRequesterList title="Moderação de ouvintes" {blockedRequesters} {oauthAccounts} />
    <RoleCarousel title="Cargos" variant="administration" {roles} {permissions} />
    <Deferred data={["badges", "badgeTargets"]}>
        <BadgeGrid title="Emblemas" variant="administration" {badges} {badgeTargets} {badgeSearch} />
        <svelte:fragment slot="fallback">
            <Section title="Emblemas">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {#each skeletonItems(8) as item}
                        <div class="h-48 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
        </svelte:fragment>
    </Deferred>
</Layout>
