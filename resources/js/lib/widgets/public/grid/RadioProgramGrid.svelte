<script>
    import { onMount } from "svelte";
    import { EditorialTitle, GridList } from "@/lib/components/public";
    import { resolveDay, resolveHour, resolvePlaceholderImage, themeClass } from "@/lib/utils";

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
        "America/Sao_Paulo": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Bahia": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Belem": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Fortaleza": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Maceio": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Recife": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Araguaina": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Santarem": { name: "BRT", region: "Brasília", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Noronha": { name: "FNT", region: "Fernando de Noronha", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Manaus": { name: "AMT", region: "Manaus", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Boa_Vista": { name: "AMT", region: "Boa Vista", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Porto_Velho": { name: "AMT", region: "Porto Velho", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Cuiaba": { name: "AMT", region: "Cuiabá", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Campo_Grande": { name: "AMT", region: "Campo Grande", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Rio_Branco": { name: "ACT", region: "Rio Branco", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "America/Eirunepe": { name: "ACT", region: "Eirunepé", country: "Brasil", mainTimeZone: "America/Sao_Paulo" },
        "Europe/Lisbon": { name: "WET/WEST", region: "Lisboa", country: "Portugal", mainTimeZone: "Europe/Lisbon" },
        "Atlantic/Madeira": { name: "WET/WEST", region: "Lisboa", country: "Portugal", mainTimeZone: "Europe/Lisbon" },
        "Atlantic/Azores": { name: "AZOT/AZOST", region: "Açores", country: "Portugal", mainTimeZone: "Europe/Lisbon" },
        "Africa/Luanda": { name: "WAT", region: "Luanda", country: "Angola", mainTimeZone: "Africa/Luanda" },
        "Africa/Maputo": { name: "CAT", region: "Maputo", country: "Moçambique", mainTimeZone: "Africa/Maputo" },
        "Atlantic/Cape_Verde": { name: "CVT", region: "Praia", country: "Cabo Verde", mainTimeZone: "Atlantic/Cape_Verde" },
        "Africa/Bissau": { name: "GMT", region: "Bissau", country: "Guiné-Bissau", mainTimeZone: "Africa/Bissau" },
        "Africa/Sao_Tome": { name: "GMT", region: "São Tomé", country: "São Tomé e Príncipe", mainTimeZone: "Africa/Sao_Tome" },
        "Asia/Dili": { name: "TLT", region: "Díli", country: "Timor-Leste", mainTimeZone: "Asia/Dili" },
        "Asia/Macau": { name: "CST", region: "Macau", country: "Macau", mainTimeZone: "Asia/Macau" },
        "Asia/Tokyo": { name: "JST", region: "Tokyo", country: "Japão", mainTimeZone: "Asia/Tokyo" },
    };

    let activeDay = 1;
    let visitorTimeZone = baseTimeZone;

    $: selectedPrograms = programs?.data ?? [];
    $: visitorTimeZoneLabel = resolveTimeZoneLabel(visitorTimeZone);
    $: mainTimeZone = visitorTimeZoneLabel.mainTimeZone ?? visitorTimeZone;
    $: mainTimeZoneLabel = resolveTimeZoneLabel(mainTimeZone);
    $: showLocalTime = visitorTimeZoneLabel.name !== mainTimeZoneLabel.name || visitorTimeZoneLabel.region !== mainTimeZoneLabel.region;
    $: showBrasiliaTime = visitorTimeZoneLabel.country === "Brasil" && mainTimeZone !== baseTimeZone;
    $: dayPrograms = resolveProgramsByDay(selectedPrograms, activeDay, visitorTimeZone, mainTimeZone);

    onMount(() => {
        visitorTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || baseTimeZone;
    });

    function resolveProgramsByDay(items, day, localTimeZone, countryMainTimeZone) {
        return items
            .flatMap((program) =>
                (program.airtimes ?? [])
                    .map((schedule) => ({
                        ...program,
                        schedule,
                        baseSchedule: convertScheduleTime(schedule, baseTimeZone),
                        mainSchedule: convertScheduleTime(schedule, countryMainTimeZone),
                        localSchedule: convertScheduleTime(schedule, localTimeZone),
                    }))
                    .filter((program) => Number(program.baseSchedule.day) === day)
            )
            .sort((first, second) => String(first.baseSchedule.hour).localeCompare(String(second.baseSchedule.hour)));
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

    function resolveConvertedScheduleLabel(schedule, comparedSchedule) {
        const convertedHour = resolveHour(schedule.hour);

        if (Number(schedule.day) === Number(comparedSchedule.day)) {
            return convertedHour;
        }

        return `${resolveDay(schedule.day)} · ${convertedHour}`;
    }

    function resolveTimeZoneLabel(timeZone) {
        return timeZoneLabels[timeZone] ?? {
            name: "Local",
            region: timeZone.split("/").pop()?.replaceAll("_", " ") ?? "sua região",
            country: "Local",
            mainTimeZone: timeZone,
        };
    }
</script>

<section class="bg-blue-marinho">
    <EditorialTitle title="Programação" listLabel="Filtrar programação por dia">
        {#each weekDays as item}
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
                                <dl class={["w-full rounded-md bg-suspense-aurora px-4 py-3 mb-2", themeClass("bg", "orange-morning", { theme: "light" })]}>
                                    <dt class={["mb-3 flex items-center justify-between gap-3 font-noto-sans italic uppercase text-blue-marinho", themeClass("text", "blue-marinho", { fixed: true, theme: "light" })]}>
                                        <span class="text-sm font-extrabold">
                                            {resolveDay(showLocalTime ? item.localSchedule.day : item.mainSchedule.day)}
                                        </span>
                                        <span class={["rounded-md bg-orange-amber px-2 py-1 text-xs font-black text-blue-night", themeClass("text", "blue-night", { fixed: true, theme: "light" })]}>
                                            {visitorTimeZoneLabel.country}
                                        </span>
                                    </dt>
                                    <dd>
                                        <div class="rounded-md bg-blue-marinho px-3 py-2 font-noto-sans italic uppercase text-suspense-aurora">
                                            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-3 gap-y-1">
                                                <span class="text-xs font-black text-orange-amber">
                                                    {showLocalTime ? `${visitorTimeZoneLabel.name} · ${visitorTimeZoneLabel.region}` : `${mainTimeZoneLabel.name} · ${mainTimeZoneLabel.region}`}
                                                </span>
                                                <span class={["text-lg font-black leading-none", showLocalTime && "row-span-2"]}>
                                                    {resolveHour(showLocalTime ? item.localSchedule.hour : item.mainSchedule.hour)}
                                                </span>
                                                {#if showLocalTime}
                                                    <span class="text-[0.62rem] font-bold text-suspense-aurora/60">
                                                        Seu horário local
                                                    </span>
                                                {/if}
                                            </div>
                                        </div>
                                        {#if showLocalTime}
                                            <div class="mt-2 flex items-center justify-between gap-4 rounded-md bg-blue-marinho/10 px-4 py-2 font-noto-sans italic uppercase text-blue-marinho">
                                                <span class="text-[0.62rem] font-black">
                                                    {mainTimeZone === baseTimeZone ? "Horário de Brasília" : "Horário principal"}
                                                </span>
                                                <span class="text-[0.68rem] font-black">
                                                    {resolveConvertedScheduleLabel(item.mainSchedule, item.localSchedule)}
                                                </span>
                                            </div>
                                        {/if}
                                        {#if showBrasiliaTime && !showLocalTime}
                                            <div class="mt-2 flex items-center justify-between gap-4 rounded-md bg-blue-marinho/10 px-4 py-2 font-noto-sans italic uppercase text-blue-marinho">
                                                <span class="text-[0.62rem] font-black">
                                                    Horário de Brasília
                                                </span>
                                                <span class="text-[0.68rem] font-black">
                                                    {resolveConvertedScheduleLabel(item.baseSchedule, item.mainSchedule)}
                                                </span>
                                            </div>
                                        {/if}
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
