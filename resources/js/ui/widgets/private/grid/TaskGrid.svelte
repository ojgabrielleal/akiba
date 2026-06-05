<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Offcanvas, Section } from "@/ui/components/private/";
    import { TaskForm } from "@/ui/widgets/private/";
    import { taskPermissions } from "@/utils";

    $: ({ tasks } = $page.props);

    let can = taskPermissions();

    let offcanvasRef;
    let identifier;
</script>

<Offcanvas bind:this={offcanvasRef} title={identifier ? "Atualizar tarefa" : "Cadastrar tarefa"}>
    <div slot="content" let:close>
        <TaskForm {identifier} {close} />
    </div>
</Offcanvas>

{#if tasks}
    <Section {title}>
        {#if can.create}
            <div class="flex justify-center gap-5 mb-8">
                <button type="button" class="cursor-pointer bg-blue-skywave px-4 py-2 rounded-md font-noto-sans font-extrabold italic uppercase text-suspense-aurora" on:click={() => { identifier = null; offcanvasRef.open(); }}>
                    Cadastrar tarefa
                </button>
            </div>
        {/if}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
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
                        <button type="button"
                            aria-label="Atualizar tarefa"
                            class="cursor-pointer"
                            on:click={() => { identifier = task.uuid; offcanvasRef.open(); }}
                        >
                            <img
                                src="/svg/edit.svg"
                                alt=""
                                aria-hidden="true"
                                loading="lazy"
                                class="w-5 h-5 filter-suspense-aurora"
                            />
                        </button>
                    {/if}
                </div>
            {/each}
        </div>
    </Section>
{/if}
