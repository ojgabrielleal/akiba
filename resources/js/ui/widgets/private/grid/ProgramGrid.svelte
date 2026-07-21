<script>
    export let title;

    import { router, page } from "@inertiajs/svelte";
    import { EmptyState, GridList, IconButton, Offcanvas, Section } from "@/ui/components/private/";
    import { ProgramForm } from "@/ui/widgets/private";
    import { programPermissions, resolveDateTime, resolveDay, resolveHour, resolvePlaceholderImage } from "@/utils";

    $: ({ programs } = $page.props);
    
    let can = programPermissions();

    let selectedExecutionMode = "live";
    
    let offcanvasRef;
    let programSelected = null;
    $: offcanvasTitle = programSelected?.name ?? "Cadastrar Programa";

    let actions = [
        {
            title: "Cadastrar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                offcanvasRef.open();
                programSelected = null;
            }
        },
    ];

    $: filters = [
        {
            title: "Ao vivo",
            execution_mode: "live",
            icon: "/svg/onair.svg",
        },
        {
            title: "Gravados",
            execution_mode: "scheduled",
            icon: "/svg/bestAvaliable.svg",
        },
        {
            title: "Playlist",
            execution_mode: "playlist",
            icon: "/svg/disc.svg",
        },
        {
            title: "Auto DJ",
            execution_mode: "auto_dj",
            icon: "/svg/robot.svg",
        },
    ];

    const requestDeactivateProgram = (program) => {
        router.patch(`/panel/radio/program/${program}/deactivate`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <ProgramForm {programSelected} {close} />
    </div>
</Offcanvas>

{#if programs}
    <Section {title} {actions}>
        <div class="flex flex-wrap justify-center gap-8 mt-5" role="group" aria-label="Filtrar programas por formato">
            {#each filters as item}
                <button
                    type="button"
                    aria-pressed={selectedExecutionMode === item.execution_mode}
                    class={["cursor-pointer flex items-center gap-1 text-lg font-noto-sans font-extrabold italic uppercase hover:text-blue-skywave group/item", 
                        {"text-blue-skywave": selectedExecutionMode === item.execution_mode},
                        {"text-neutral-gray": selectedExecutionMode !== item.execution_mode}
                    ]}
                    on:click={() => selectedExecutionMode = item.execution_mode}
                >
                    <img
                        src={item.icon}
                        alt=""
                        aria-hidden="true"
                        class={["w-5 group-hover/item:filter-blue-skywave", 
                            {"filter-blue-skywave": selectedExecutionMode === item.execution_mode},
                            {"filter-neutral-gray": selectedExecutionMode !== item.execution_mode}
                        ]}
                        loading="lazy"
                    />
                    {item.title}
                </button>
            {/each}
        </div>
        {#if programs.data[selectedExecutionMode].length > 0}
        <GridList preset="wide" class="mt-10">
            {#each programs.data[selectedExecutionMode] as item}
                <li>
                    <article class="w-full">
                    <div>
                        <img
                            class="w-40 mb-3"
                            src={resolvePlaceholderImage(item.image, "program")}
                            alt={item.name}
                            loading="lazy"
                        />
                        <div class="w-full h-13 flex items-center rounded-md px-3 bg-suspense-aurora relative mb-2">
                            <div class="w-36 min-w-0 flex items-center gap-1 text-blue-ocean text-sm font-noto-sans font-extrabold italic uppercase">
                                <span class="shrink-0 not-italic font-normal text-[0.7rem]">
                                    Com:
                                </span>
                                <span class="block min-w-0 flex-1 truncate">
                                    {item.host.nickname}
                                </span>
                            </div>
                            <div class={["z-10 absolute bottom-2 right-22 px-2 rounded-xl float-end text-center text-[0.6rem] text-suspense-aurora font-noto-sans font-extrabold italic uppercase",
                                {'bg-neutral-gray': item.host.is_virtual},
                                {'bg-green-mint': !item.host.is_virtual}
                            ]}>
                                {item.host.is_virtual ? "Robô" : 'Humano'}
                            </div>
                            <div class="flex gap-1 absolute bottom-3 right-4 z-10">
                                {#if can.deactivate}
                                    <IconButton
                                        variant="trash"
                                        label="Desativar"
                                        size="sm"
                                        on:click={() => requestDeactivateProgram(item.uuid)}
                                    />
                                {/if}
                                {#if can.update}
                                    <IconButton
                                        variant="edit"
                                        label="Atualizar"
                                        size="sm"
                                        on:click={() => {
                                                programSelected = item;
                                                offcanvasRef.open();
                                            }}
                                    />
                                {/if}
                            </div>
                            <img
                                class="w-36 aspect-square absolute right-0 bottom-0 object-cover object-top"
                                src={resolvePlaceholderImage(item.host.avatar, "avatar", item.host.gender)}
                                alt={item.host.nickname}
                                loading="lazy"
                            />
                        </div>
                        {#if item.airtimes.length > 0}
                            {#each item.airtimes as schedule}
                                <dl class="w-full rounded-md py-2 px-4 bg-suspense-aurora flex justify-between mb-2">
                                    <dt class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                        {resolveDay(schedule.day)}
                                    </dt>
                                    <dd class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                        {resolveHour(schedule.hour)}
                                    </dd>
                                </dl>
                            {/each}
                        {:else}
                            {#each item.schedules as schedule}
                                <dl class="w-full rounded-md py-2 px-4 bg-suspense-aurora flex justify-between mb-2">
                                    <dt class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                        Agendado para:
                                    </dt>
                                    <dd class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                        {resolveDateTime(schedule.scheduled_at)}
                                    </dd>
                                </dl>
                            {/each}
                        {/if}
                    </div>
                    </article>
                </li>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                class="mt-10"
                title="Nenhum programa encontrado"
                description="Não há programas cadastrados neste formato."
            />
        {/if}
    </Section>
{/if}
