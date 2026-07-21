<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { GridList, IconButton, Offcanvas, Section } from "@/ui/components/private/";
    import { CalendarForm } from "@/ui/widgets/private";
    import { calendarPermissions, resolveHour } from "@/utils";
    import { calendarTags } from "@/data";

    $: ({ calendar } = $page.props);

    let can = calendarPermissions();

    let offcanvasRef;
    let eventSelected = null;
    $: eventName = eventSelected?.activity?.title ?? eventSelected?.content;
    $: offcanvasTitle = eventSelected ? eventName : "Cadastrar evento";

    const openEvent = (event = null) => {
        eventSelected = event;
        offcanvasRef.open();
    };

    let actions = [
        {
            title: "Cadastrar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => openEvent(),
        },
    ];
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <CalendarForm {eventSelected} {close} />
    </div>
</Offcanvas>

<Section {title} {actions}>
    <div class="w-full overflow-x-auto pb-2">
        <div class="min-w-[70rem] lg:min-w-0">
            <GridList preset="calendar" class="mb-9" aria-label="Legenda do calendário">
                {#each calendarTags as item}
                    <li class={`flex min-h-8 items-center justify-center rounded-md px-2 py-1 font-noto-sans text-lg font-extrabold uppercase italic ${item.color} ${item.textcolor}`} aria-hidden={!item.label}>
                        {item.label}
                    </li>
                {/each}
            </GridList>
            <GridList as="div" preset="calendar" class="items-start" aria-label="Eventos por dia">
                {#each Object.entries(calendar?.data ?? {}) as [day, events], dayIndex}
                    <section class="flex w-full flex-col gap-3" aria-labelledby={`calendar-day-${dayIndex}`}>
                        <h3 id={`calendar-day-${dayIndex}`} class="mb-2 text-center font-noto-sans text-xl font-extrabold uppercase italic text-suspense-aurora">
                            {day}
                        </h3>
                        {#each events as item}
                            <article class={["flex w-full flex-col rounded-md px-3 pt-4 pb-2",
                                { "bg-blue-skywave": item.type === "show" && !item.activity },
                                { "bg-purple-mystic": item.type === "live" && !item.activity },
                                { "bg-red-crimson": item.type === "video" && !item.activity },
                                { "bg-green-mint": item.type === "podcast" && !item.activity },
                                { "bg-suspense-honeycream": item.activity },
                            ]}>
                                <time class={["w-full text-center font-noto-sans text-2xl uppercase leading-tight",
                                    { "text-blue-night": item.activity },
                                    { "text-suspense-aurora": !item.activity },
                                ]}>
                                    {resolveHour(item.hour)}
                                </time>
                                <h4 class={["my-5 w-full flex-1 text-center font-noto-sans text-2xl font-extrabold italic leading-tight",
                                    { "text-blue-night": item.activity },
                                    { "text-suspense-aurora": !item.activity },
                                ]}>
                                    {item.activity ? item.activity.title : item.content}
                                </h4>
                                <div class="flex min-h-4 items-end justify-between gap-2">
                                    {#if can.update}
                                        <IconButton
                                            variant="edit"
                                            label={`Atualizar evento ${item.activity ? item.activity.title : item.content}`}
                                            tone={item.activity ? "dark" : "light"}
                                            size="sm"
                                            surface="transparent"
                                            on:click={() => openEvent(item)}
                                        />
                                    {/if}
                                    <span class={["ml-auto truncate text-end font-noto-sans text-sm",
                                        { "text-blue-night": item.activity },
                                        { "text-suspense-aurora": !item.activity }
                                    ]}>
                                        {item.responsible.nickname}
                                    </span>
                                </div>
                            </article>
                        {/each}
                    </section>
                {/each}
            </GridList>
        </div>
    </div>
</Section>
