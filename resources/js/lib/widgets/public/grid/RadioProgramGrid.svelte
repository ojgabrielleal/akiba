<script>
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

    const brazilTimeZones = [
        { name: "BRT", region: "Brasília", offset: 0, featured: true },
        { name: "FNT", region: "Fernando de Noronha", offset: 1 },
        { name: "AMT", region: "Amazonas", offset: -1 },
        { name: "ACT", region: "Acre", offset: -2 },
    ];

    let activeDay = 1;

    $: selectedPrograms = programs?.data ?? [];
    $: dayPrograms = resolveProgramsByDay(selectedPrograms, activeDay);

    function resolveProgramsByDay(items, day) {
        return items
            .flatMap((program) =>
                (program.airtimes ?? [])
                    .filter((schedule) => Number(schedule.day) === day)
                    .map((schedule) => ({ ...program, schedule }))
            )
            .sort((first, second) => String(first.schedule.hour).localeCompare(String(second.schedule.hour)));
    }

    function resolveScheduleTimeZones(schedule) {
        return brazilTimeZones.map((timezone) => {
            const converted = convertScheduleTime(schedule.day, schedule.hour, timezone.offset);

            return {
                ...timezone,
                ...converted,
            };
        });
    }

    function convertScheduleTime(day, hour, offset) {
        const [hours = "0", minutes = "0"] = String(hour).split(":");
        const totalMinutes = (Number(hours) * 60) + Number(minutes) + (offset * 60);
        const dayOffset = Math.floor(totalMinutes / 1440);
        const normalizedMinutes = ((totalMinutes % 1440) + 1440) % 1440;
        const convertedDay = (Number(day) + dayOffset + 7) % 7;
        const convertedHours = Math.floor(normalizedMinutes / 60);
        const convertedMinutes = normalizedMinutes % 60;

        return {
            day: convertedDay,
            hour: `${String(convertedHours).padStart(2, "0")}:${String(convertedMinutes).padStart(2, "0")}`,
            shiftedDay: convertedDay !== Number(day),
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
                        "cursor-pointer whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric motion-reduce:transform-none motion-reduce:transition-none",
                        activeDay === item.day ? "text-orange-citric" : "text-neutral-gray",
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
                                            {resolveDay(item.schedule.day)}
                                        </span>
                                        <span class="rounded-md bg-orange-amber px-2 py-1 text-xs font-black text-blue-night">
                                            Horários BR
                                        </span>
                                    </dt>
                                    <dd class="grid gap-2">
                                        {#each resolveScheduleTimeZones(item.schedule) as timezone}
                                            {#if timezone.featured}
                                                <div class="rounded-md bg-blue-marinho px-3 py-2 font-noto-sans italic uppercase text-suspense-aurora">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-xs font-black text-orange-amber">
                                                            {timezone.name} · {timezone.region}
                                                        </span>
                                                        <span class="text-lg font-black leading-none">
                                                            {resolveHour(timezone.hour)}
                                                        </span>
                                                    </div>
                                                </div>
                                            {:else}
                                                <div class="flex items-center justify-between gap-3 border-t border-blue-marinho/10 pt-2 font-noto-sans text-xs italic uppercase text-blue-marinho">
                                                    <span class="font-black">
                                                        {timezone.name}
                                                        <span class="font-extrabold normal-case not-italic text-blue-marinho/65">
                                                            {timezone.region}
                                                        </span>
                                                    </span>
                                                    <span class="text-right font-extrabold">
                                                        {resolveHour(timezone.hour)}
                                                        {#if timezone.shiftedDay}
                                                            <span class="ml-1 text-[0.65rem] font-black text-orange-amber">
                                                                {resolveDay(timezone.day, "short")}
                                                            </span>
                                                        {/if}
                                                    </span>
                                                </div>
                                            {/if}
                                        {/each}
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
