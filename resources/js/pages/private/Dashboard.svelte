<script>
    import { Deferred, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private/";
    import { Section } from "@/lib/components/private";
    import {
        WellcomeHero,
        ActivityCarousel,
        TaskList,
        PostGrid,
        CalendarGrid,
    } from "@/lib/widgets/private";

    $: ({ user, activities, tasks, posts, calendar } = $page.props);

    const skeletonItems = (count) => Array.from({ length: count }, (_, index) => index);
</script>

<Meta meta={{ title: "Dashboard" } } />
<Layout>
    <WellcomeHero />
    <Deferred data={["activities", "tasks", "posts", "calendar"]}>
        <ActivityCarousel title="Avisos e Atividades" {activities} {user} />
        <TaskList title="Minhas Tarefas" {tasks} />
        <PostGrid title="Minhas últimas matérias" {posts} />
        <CalendarGrid title="Calendário" {calendar} />
        <svelte:fragment slot="fallback">
            <Section title="Avisos e Atividades">
                <div class="flex gap-3 overflow-hidden">
                    {#each skeletonItems(4) as item}
                        <div class="h-44 w-56 shrink-0 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
            <Section title="Minhas Tarefas">
                <div class="grid gap-3">
                    {#each skeletonItems(5) as item}
                        <div class="h-16 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
            <Section title="Minhas últimas matérias">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    {#each skeletonItems(5) as item}
                        <div class="h-53 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
                    {/each}
                </div>
            </Section>
            <Section title="Calendário">
                <div class="h-64 animate-pulse rounded-md bg-blue-ocean/55" aria-hidden="true"></div>
            </Section>
        </svelte:fragment>
    </Deferred>
</Layout>
