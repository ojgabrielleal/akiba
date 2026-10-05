<script>
    import { onMount } from "svelte";
    import { EditorialTitle, GridList } from "@/lib/components/public";
    import { resolveDay, resolveHour, resolvePlaceholderImage, themeClass } from "@/lib/utils";

    export let programs = null;

    const filterTabs = [
        { day: "all", label: "Todos" },
        { day: 1, label: "Segunda-feira" },
        { day: 2, label: "Terça-feira" },
        { day: 3, label: "Quarta-feira" },
        { day: 4, label: "Quinta-feira" },
        { day: 5, label: "Sexta-feira" },
        { day: 6, label: "Sábado" },
        { day: 0, label: "Domingo" },
    ];

    const baseTimeZone = "America/Sao_Paulo";
    const baseWeekStart = "2024-01-07";
    const baseTimeZoneOffset = "-03:00";
    const brazilTimeZones = new Set([
        "America/Sao_Paulo",
        "America/Bahia",
        "America/Belem",
        "America/Fortaleza",
        "America/Maceio",
        "America/Recife",
        "America/Araguaina",
        "America/Santarem",
        "America/Noronha",
        "America/Manaus",
        "America/Boa_Vista",
        "America/Porto_Velho",
        "America/Cuiaba",
        "America/Campo_Grande",
        "America/Rio_Branco",
        "America/Eirunepe",
    ]);
    const weekDayOrder = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };
    let activeDay = "all";
    let visitorTimeZone = baseTimeZone;

    $: selectedPrograms = (programs?.data ?? []).filter((program) => program.execution_mode !== "auto_dj");
    $: isBrazilTimeZone = brazilTimeZones.has(visitorTimeZone);
    $: dayPrograms = resolveProgramsByDay(selectedPrograms, activeDay, visitorTimeZone);

    onMount(() => {
        visitorTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || baseTimeZone;
    });

    function resolveProgramsByDay(items, day, localTimeZone) {
        return items
            .flatMap((program) =>
                (program.airtimes ?? [])
                    .map((schedule) => ({
                        ...program,
                        schedule,
                        baseSchedule: convertScheduleTime(schedule, baseTimeZone),
                        localSchedule: convertScheduleTime(schedule, localTimeZone),
                    }))
                    .filter((program) => day === "all" || Number(program.localSchedule.day) === Number(day))
            )
            .sort((first, second) => {
                if (Number(first.localSchedule.day) !== Number(second.localSchedule.day)) {
                    return weekDayOrder[first.localSchedule.day] - weekDayOrder[second.localSchedule.day];
                }

                return String(first.localSchedule.hour).localeCompare(String(second.localSchedule.hour));
            });
    }

    function convertScheduleTime(schedule, timeZone) {
        const [hours = "0", minutes = "0"] = String(schedule.hour).split(":");
        const date = new Date(`${resolveBaseDate(schedule.day)}T${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}:00${baseTimeZoneOffset}`);
        const parts = Object.fromEntries(
            new Intl.DateTimeFormat("en-US", {
                timeZone,
                weekday: "short",
                hour: "2-digit",
                minute: "2-digit",
                hourCycle: "h23",
                hour12: false,
            }).formatToParts(date).map((part) => [part.type, part.value])
        );
        const convertedDay = resolveWeekDayIndex(parts.weekday);

        return {
            day: convertedDay,
            hour: `${parts.hour}:${parts.minute}`,
            shiftedDay: convertedDay !== Number(schedule.day),
        };
    }

    function resolveBaseDate(day) {
        const date = new Date(`${baseWeekStart}T12:00:00${baseTimeZoneOffset}`);
        date.setUTCDate(date.getUTCDate() + Number(day));

        return date.toISOString().slice(0, 10);
    }

    function resolveWeekDayIndex(weekday) {
        return {
            Sun: 0,
            Mon: 1,
            Tue: 2,
            Wed: 3,
            Thu: 4,
            Fri: 5,
            Sat: 6,
        }[weekday] ?? 0;
    }

    function resolveEmptyMessage(day) {
        return day === "all" ? "Nenhum programa encontrado." : "Nenhum programa encontrado neste dia.";
    }
</script>

<section class="bg-blue-marinho">
    <EditorialTitle title="Programação" listLabel="Filtrar programação por dia">
        {#each filterTabs as item}
            <li class="flex h-7 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                <button
                    type="button"
                    class={[
                        "cursor-pointer whitespace-nowrap rounded-md font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-amber focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                        activeDay === item.day ? "text-orange-amber" : "text-neutral-gray",
                    ]}
                    on:click={() => (activeDay = item.day)}
                >
                    {item.label}
                </button>
            </li>
        {/each}
    </EditorialTitle>

    <div class="container-page py-12">
        {#if dayPrograms.length > 0}
            <GridList preset="wide">
                {#each dayPrograms as item (`${activeDay}-${item.uuid}-${item.schedule.uuid}`)}
                    <li>
                        <article class="w-full">
                            <div>
                                <img
                                    class="w-40 mb-3"
                                    src={resolvePlaceholderImage(item.image, "program")}
                                    alt={item.name}
                                    loading="lazy"
                                />
                                <div class={["w-full h-13 flex items-center rounded-md px-3 bg-suspense-aurora relative mb-2", themeClass("bg", "orange-morning", { theme: "light" })]}>
                                    <div class="w-36 min-w-0 flex items-center gap-1 text-blue-ocean text-sm font-noto-sans font-extrabold italic uppercase">
                                        <span class="shrink-0 not-italic font-normal text-[0.7rem]">
                                            Com:
                                        </span>
                                        <span class="block min-w-0 flex-1 truncate">
                                            {item.host?.nickname ?? "Programa aberto"}
                                        </span>
                                    </div>
                                    <img
                                        class="w-36 aspect-square absolute right-0 bottom-0 object-cover object-top"
                                        src={resolvePlaceholderImage(item.host?.avatar, "avatar", item.host?.gender)}
                                        alt={item.host?.nickname ?? "Programa aberto"}
                                        loading="lazy"
                                    />
                                </div>
                                <div class={["mb-2 w-full rounded-md bg-suspense-aurora px-4 py-3 font-noto-sans italic uppercase text-blue-marinho", themeClass("bg", "orange-morning", { theme: "light" }), themeClass("text", "blue-marinho", { fixed: true, theme: "light" })]}>
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="min-w-0 truncate text-sm font-extrabold">
                                            {resolveDay(item.localSchedule.day)}
                                        </span>
                                        <span class="shrink-0 text-lg font-black leading-none">
                                            {resolveHour(item.localSchedule.hour)}
                                        </span>
                                    </div>
                                    {#if !isBrazilTimeZone}
                                        <div class="mt-2 flex items-center justify-between gap-3 rounded-md bg-blue-marinho/10 px-3 py-2 text-[0.62rem] font-black">
                                            <span class="shrink-0 text-orange-amber">
                                                Horário de Brasília
                                            </span>
                                            <span class="min-w-0 truncate text-right">
                                                {resolveDay(item.baseSchedule.day)} · {resolveHour(item.baseSchedule.hour)}
                                            </span>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </article>
                    </li>
                {/each}
            </GridList>
        {:else}
            <p class="text-center font-noto-sans text-lg font-extrabold italic uppercase text-neutral-gray">
                {resolveEmptyMessage(activeDay)}
            </p>
        {/if}
    </div>
</section>
