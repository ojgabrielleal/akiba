<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Section, ButtonPagination, Tooltip } from "@/ui/components/private/";
    import { taskPermissions } from "@/utils";

    $: ({ tasks } = $page.props);

    let can = taskPermissions();

    const requestmarkTaskToReview = (task) => {
        router.post(`/panel/dashboard/task/${task}/complete`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };

</script>

{#if tasks}
    <Section {title}>
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
                        {#if task.status === 'pending'}
                            <div class={["grid w-full shrink-0 overflow-hidden rounded-md bg-blue-night",
                                { "md:w-52 grid-cols-[1fr_1fr]": task.is_overdue },
                                { "md:w-30 grid-cols-[1fr_2.5rem]": !task.is_overdue },
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
                                        Você tem 1 strike
                                    </div>
                                {:else}
                                    <div class="flex items-center justify-center bg-blue-night px-3">
                                        {#if can.completed}
                                            <button
                                                type="button"
                                                aria-label="Marcar tarefa como concluida"
                                                class="group flex h-full w-full cursor-pointer items-center justify-center"
                                                on:click={() => requestmarkTaskToReview(task.uuid)}
                                            >
                                                <img
                                                    src="/svg/verify.svg"
                                                    alt=""
                                                    aria-hidden="true"
                                                    class="w-5 filter-orange-citric"
                                                    loading="lazy"
                                                />
                                            </button>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        {:else if task.status === 'in_review'}
                            <div class="flex w-full md:w-35 h-12 p-1 shrink-0 items-center justify-center rounded-md bg-blue-marinho px-4 font-noto-sans font-extrabold italic uppercase text-[0.8rem] text-center text-suspense-aurora">
                               Em avaliação
                            </div>
                        {/if}
                    </div>
                </article>
            {/each}
        </div>
        <ButtonPagination
            pages={tasks}
            only={["tasks"]}
        />
    </Section>
{/if}
