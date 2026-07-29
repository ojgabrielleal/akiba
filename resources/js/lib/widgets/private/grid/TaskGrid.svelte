<script>
    import { Button, EmptyState, GridList, IconButton, Offcanvas, Section } from "@/lib/components/private/";
    import { TaskForm } from "@/lib/widgets/private/";
    import { taskPermissions } from "@/lib/utils";

    export let title;
    export let tasks = null;
    export let users = null;

    const can = taskPermissions();
    let offcanvasRef;
    let identifier;
</script>

<Offcanvas bind:this={offcanvasRef} title={identifier ? "Atualizar tarefa" : "Cadastrar tarefa"}>
    <div slot="content" let:close>
        <TaskForm taskSelected={identifier} {users} {close} />
    </div>
</Offcanvas>

{#if tasks}
    <Section {title}>
        {#if can.create}
            <div class="flex justify-center gap-5 mb-8">
                <Button
                    on:click={() => { identifier = null; offcanvasRef.open(); }}
                >
                    Cadastrar tarefa
                </Button>
            </div>
        {/if}
        {#if tasks.data.length > 0}
        <GridList as="div" preset="split">
            {#each tasks.data as task}
                <div class={["flex items-center justify-between p-3 rounded-md",
                    { "bg-red-crimson": task.is_overdue },
                    { "bg-orange-amber": !task.is_overdue && task.days_remaining <= 7 },
                    { "bg-blue-skywave": !task.is_overdue && task.days_remaining > 7 },
                ]}>
                    <div class="text-suspense-aurora font-noto-sans">
                        {task.title}
                    </div>
                    {#if can.update}
                        <IconButton
                            label="Atualizar"
                            icon="/svg/edit.svg"
                            tone="light"
                            surface="transparent"
                            on:click={() => { identifier = task.uuid; offcanvasRef.open(); }}
                        />
                    {/if}
                </div>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                title="Nenhuma tarefa encontrada"
                description="As tarefas cadastradas aparecerão aqui."
            />
        {/if}
    </Section>
{/if}
