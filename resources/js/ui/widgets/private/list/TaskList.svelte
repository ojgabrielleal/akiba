<script>
    export let title;
    export let variant = null;

    import { page, router } from "@inertiajs/svelte";
    import { Button, IconButton, Offcanvas, Pagination, Section, Tooltip } from "@/ui/components/private/";
    import { TaskForm } from "@/ui/widgets/private";
    import { taskPermissions } from "@/utils";

    $: ({ tasks } = $page.props);

    let can = taskPermissions();

    let offcanvasRef;
    let taskSelected = null;
    let completingTask = null;
    $: offcanvasTitle = taskSelected ? `Atualizar ${taskSelected.title}` : "Cadastrar tarefa";

    let actions = [
        {
            title: "Criar tarefa",
            icon: "/svg/plus.svg",
            permission: can.create && variant === "administration",
            onClick: () => {
                taskSelected = null;
                offcanvasRef.open();
            },
        },
    ];

    const requestMarkTaskToReview = (task) => {
        router.post(`/panel/dashboard/task/${task}/review`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const requestDeactivateTask = (task) => {
        router.patch(`/panel/administration/task/${task}/deactivate`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const requestCompleteTask = (task) => {
        router.patch(`/panel/administration/task/${task}/complete`, {}, {
            preserveScroll: true,
            preserveState: true,
            onStart: () => completingTask = task,
            onFinish: () => completingTask = null,
        });
    };

</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <TaskForm {taskSelected} {close} />
    </div>
</Offcanvas>

{#if tasks}
    <Section {title} {actions}>
        <div class="flex flex-col gap-4">
            {#each tasks.data as task}
                <article class={["w-full rounded-md px-4 py-3",
                    { "bg-gradient-blue-cerulean-glow": task.status === 'pending' },
                    { "bg-gradient-green-forest-pine": task.status === 'in_review' },
                    { "bg-gradient-red-crimson-blood": task.is_overdue && task.status === 'pending' },
                ]}>
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="block min-w-0">
                            <div class="text-xl text-suspense-aurora font-extrabold uppercase italic lg:truncate">
                                {task.title}
                            </div>
                            <div class="text-md text-suspense-aurora lg:line-clamp-2">
                                {task.description}
                            </div>
                        </div>
                        <div class="flex w-full shrink-0 items-center justify-end gap-2 md:w-auto">
                            {#if task.status === 'pending'}
                                <div class={["grid w-full shrink-0 overflow-hidden rounded-md bg-blue-night",
                                    { "md:w-52 grid-cols-[1fr_1fr]": task.is_overdue },
                                    { "md:w-20 grid-cols-1": !task.is_overdue && variant === "administration" },
                                    { "md:w-30 grid-cols-[1fr_2.5rem]": !task.is_overdue && variant !== "administration" },
                                ]}>
                                <div class="flex min-w-0 flex-col gap-1 justify-center bg-suspense-aurora p-1 font-noto-sans text-blue-night">
                                    <span class="text-[0.8rem] text-center font-extrabold uppercase leading-none">
                                        Faltam
                                    </span>
                                    <div class="flex justify-center items-center gap-1 min-w-0">
                                        <span class="text-lg font-black leading-[0.85] text-blue-skywave tabular-nums">
                                            {task.is_overdue ? '0' : task.days_remaining}
                                        </span>
                                        <span class="text-[0.8rem] font-medium uppercase leading-none">
                                            {task.days_remaining === 1 ? "dia" : "dias"}
                                        </span>
                                    </div>
                                </div>
                                {#if task.is_overdue}
                                    <div class="flex items-center justify-center bg-blue-night px-3 font-noto-sans font-extrabold italic uppercase text-orange-amber text-[0.8rem] text-center leading-5">
                                        {variant === "administration" ? "Tarefa não concluída" : "Você tem 1 strike"}
                                    </div>
                                {:else if variant !== "administration"}
                                    <div class="flex items-center justify-center bg-blue-night px-3">
                                        {#if can.review}
                                            <IconButton
                                                variant="verify"
                                                label="Enviar tarefa para avaliação"
                                                surface="transparent"
                                                tone="accent"
                                                on:click={() => requestMarkTaskToReview(task.uuid)}
                                            />
                                        {/if}
                                    </div>
                                {/if}
                                </div>
                            {:else if task.status === 'in_review'}
                                {#if variant === "administration" && can.review}
                                    <Button
                                        variant="secondary"
                                        class="h-12 w-full shrink-0 px-4 text-center text-[0.8rem] md:w-35"
                                        loading={completingTask === task.uuid}
                                        on:click={() => requestCompleteTask(task.uuid)}
                                    >
                                        Confirmar conclusão
                                    </Button>
                                {:else}
                                    <div class="flex h-12 w-full shrink-0 items-center justify-center rounded-md bg-blue-marinho p-1 px-4 text-center font-noto-sans text-[0.8rem] font-extrabold uppercase italic text-suspense-aurora md:w-35">
                                       Em avaliação
                                    </div>
                                {/if}
                            {/if}
                            {#if variant === "administration" && (can.deactivate || can.update)}
                                <div class="flex shrink-0 flex-col gap-1">
                                    {#if can.deactivate}
                                        <IconButton
                                            variant="trash"
                                            label="Remover tarefa"
                                            size="sm"
                                            surface="dark"
                                            on:click={() => requestDeactivateTask(task.uuid)}
                                        />
                                    {/if}
                                    {#if can.update}
                                        <IconButton
                                            variant="edit"
                                            label="Atualizar tarefa"
                                            size="sm"
                                            surface="dark"
                                            on:click={() => { taskSelected = task; offcanvasRef.open(); }}
                                        />
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </article>
            {/each}
        </div>
        <Pagination
            pages={tasks}
            only={["tasks"]}
        />
    </Section>
{/if}
