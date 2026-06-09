<script>
    export let title;
    export let variant;

    import { page } from "@inertiajs/svelte";
    import { Offcanvas, Section } from "@/ui/components/private/";
    import { CalendarForm } from "@/ui/widgets/private";
    import { calendarPermissions, resolveHour } from "@/utils";
    import { calendarTags } from "@/data";

    $: ({ calendar } = $page.props);

    let can = calendarPermissions();

    let offcanvasRef;
    let identifier;
</script>

<Offcanvas bind:this={offcanvasRef} title={identifier ? "Atualizar evento" : "Cadastrar evento"}>
    <div slot="content" let:close>
        <CalendarForm {identifier} {close} />
    </div>
</Offcanvas>

<Section {title}>
    {#if can.create && variant === "administration"}
        <div class="flex justify-center gap-5 mb-8">
            <button type="button" class="cursor-pointer bg-blue-skywave px-4 py-2 rounded-md font-noto-sans font-extrabold italic uppercase text-suspense-aurora" on:click={() => { identifier = null; offcanvasRef.open(); }}>
                Cadastrar evento
            </button>
        </div>
    {/if}
    <ul class="w-full mb-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2" aria-label="Legenda do calendario">
        {#each calendarTags as item}
            <li class={`py-1 text-md font-noto-sans font-extrabold uppercase italic rounded-md flex justify-center items-center ${item.color} ${item.textcolor}`}>
                {item.label}
            </li>
        {/each}
    </ul>
    <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2" aria-label="Eventos por dia">
        {#each Object.entries(calendar?.data ?? {}) as [day, events], dayIndex}
            <section class="flex flex-col gap-2 w-full" aria-labelledby={`calendar-day-${dayIndex}`}>
                <h3 id={`calendar-day-${dayIndex}`} class="text-suspense-aurora text-lg font-noto-sans text-center font-extrabold uppercase italic">
                    {day}
                </h3>
                {#each events as item}
                    <article class={["w-full rounded-md pt-4 pl-4 pr-4 pb-3 mt-5",
                        { "bg-blue-skywave": item.type === "show" },
                        { "bg-purple-mystic": item.type === "live" },
                        { "bg-red-crimson": item.type === "video" },
                        { "bg-green-mint": item.type === "podcast" },
                        { "bg-suspense-honeycream": item.activity },
                    ]}>
                        <div class="flex events-center">
                            <time class={["w-full font-noto-sans text-2xl text-center uppercase",
                                { "text-blue-night": item.activity },
                                { "text-suspense-aurora": !item.activity },
                            ]}>
                                {resolveHour(item.hour)}
                            </time>
                        </div>
                        <h4 class={["w-full font-noto-sans font-extrabold text-xl text-center italic mt-4 mb-4",
                            { "text-blue-night": item.activity },
                            { "text-suspense-aurora": !item.activity },
                        ]}>
                            {item.activity ? item.activity.title : item.content}
                        </h4>
                        <div class="flex justify-between items-center">
                            {#if can.update && variant === "administration"}
                                <button
                                    type="button"
                                    aria-label="Editar evento"
                                    class={["w-full cursor-pointer ", 
                                        { "filter-suspense-aurora": !item.activity }, 
                                        { "filter-suspense-aurora-0": item.activity }, 
                                    ]}
                                >
                                    <img
                                        src="/svg/edit.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-4"
                                    />
                                </button>
                            {/if}
                            <button
                                type="button"
                                aria-label="Atualizar evento"
                                class={["w-full font-noto-sans text-sm text-end", 
                                    { "text-blue-night": item.activity }, 
                                    { "text-suspense-aurora": !item.activity }
                                ]}
                                on:click={() => { 
                                    identifier = item.uuid; 
                                    offcanvasRef.open(); 
                                }}
                            >

                                {item.responsible.nickname}
                            </button>
                        </div>
                    </article>
                {/each}
            </section>
        {/each}
    </div>
</Section>
