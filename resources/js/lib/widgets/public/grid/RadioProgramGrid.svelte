<script>
    import { onMount } from "svelte";
    import { EditorialTitle, GridList } from "@/lib/components/public";
    import { resolveDay, resolveHour, resolvePlaceholderImage } from "@/lib/utils";

    export let programs = null;

    const weekDays = [
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
    const timeZoneLabels = {
        "America/Sao_Paulo": { name: "BRT", region: "Brasília" },
        "America/Noronha": { name: "FNT", region: "Fernando de Noronha" },
        "America/Manaus": { name: "AMT", region: "Amazonas" },
        "America/Boa_Vista": { name: "AMT", region: "Amazonas" },
        "America/Porto_Velho": { name: "AMT", region: "Amazonas" },
        "America/Cuiaba": { name: "AMT", region: "Amazonas" },
        "America/Rio_Branco": { name: "ACT", region: "Acre" },
        "America/Eirunepe": { name: "ACT", region: "Acre" },
    };

    let activeDay = 1;
    let visitorTimeZone = baseTimeZone;

    $: selectedPrograms = programs?.data ?? [];
    $: dayPrograms = resolveProgramsByDay(selectedPrograms, activeDay);
    $: visitorTimeZoneLabel = resolveTimeZoneLabel(visitorTimeZone);

    onMount(() => {
        visitorTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || baseTimeZone;
    });

    function resolveProgramsByDay(items, day) {
        return items
            .flatMap((program) =>
                (program.airtimes ?? [])
                    .map((schedule) => ({
                        ...program,
                        schedule,
                        localSchedule: convertScheduleTime(schedule, visitorTimeZone),
                    }))
                    .filter((program) => Number(program.localSchedule.day) === day)
            )
            .sort((first, second) => String(first.localSchedule.hour).localeCompare(String(second.localSchedule.hour)));
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

    function resolveTimeZoneLabel(timeZone) {
        return timeZoneLabels[timeZone] ?? {
            name: "Local",
            region: timeZone.split("/").pop()?.replaceAll("_", " ") ?? "sua região",
        };
    }
</script>

<section class="bg-blue-marinho">
    <EditorialTitle title="Programação" listLabel="Filtrar programação por dia">
        {#each weekDays as item}
            <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                <button
                    type="button"
                    class={[
                        "cursor-pointer whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-amber focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
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
                                <div class="w-full h-13 flex items-center rounded-md px-3 bg-suspense-aurora relative mb-2">
                                    <div class="w-36 min-w-0 flex items-center gap-1 text-blue-ocean text-sm font-noto-sans font-extrabold italic uppercase">
                                        <span class="shrink-0 not-italic font-normal text-[0.7rem]">
                                            Com:
                                        </span>
                                        <span class="block min-w-0 flex-1 truncate">
                                            {item.host.nickname}
                                        </span>
                                    </div>
                                    <img
                                        class="w-36 aspect-square absolute right-0 bottom-0 object-cover object-top"
                                        src={resolvePlaceholderImage(item.host.avatar, "avatar", item.host.gender)}
                                        alt={item.host.nickname}
                                        loading="lazy"
                                    />
                                </div>
                                <dl class="w-full rounded-md bg-suspense-aurora px-4 py-3 mb-2">
                                    <dt class="mb-3 flex items-center justify-between gap-3 font-noto-sans italic uppercase text-blue-marinho">
                                        <span class="text-sm font-extrabold">
                                            {resolveDay(item.localSchedule.day)}
                                        </span>
                                        <span class="rounded-md bg-orange-amber px-2 py-1 text-xs font-black text-blue-night">
                                            Horário local
                                        </span>
                                    </dt>
                                    <dd>
                                        <div class="rounded-md bg-blue-marinho px-3 py-2 font-noto-sans italic uppercase text-suspense-aurora">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-xs font-black text-orange-amber">
                                                    {visitorTimeZoneLabel.name} · {visitorTimeZoneLabel.region}
                                                </span>
                                                <span class="text-lg font-black leading-none">
                                                    {resolveHour(item.localSchedule.hour)}
                                                </span>
                                            </div>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </article>
                    </li>
                {/each}
            </GridList>
        {:else}
            <p class="text-center font-noto-sans text-lg font-extrabold italic uppercase text-neutral-gray">
                Nenhum programa encontrado neste dia.
            </p>
        {/if}
    </div>
</section>
