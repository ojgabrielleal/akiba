<script>
    import { Link } from "@inertiajs/svelte";

    import { EditorialTitle, GridList, Pagination } from "@/lib/components/public";
    import { resolveDateTime, resolveDay, resolveHour, resolvePlaceholderImage } from "@/lib/utils";

    export let programs = null;
    export let activeProgramMode = "live";

    const filters = [
        { title: "Ao vivo", executionMode: "live", icon: "/svg/onair.svg" },
        { title: "Gravados", executionMode: "scheduled", icon: "/svg/bestAvaliable.svg" },
        { title: "Automáticos", executionMode: "playlist", icon: "/svg/disc.svg" },
    ];

    $: selectedPrograms = programs?.data ?? [];

    function filterHref(executionMode) {
        return executionMode === "live"
            ? "/radio"
            : `/radio?program_mode=${executionMode}`;
    }

    function resolveScheduledDate(dateTime) {
        return resolveDateTime(dateTime).split(" - ")[0] ?? "";
    }

    function resolveScheduledHour(dateTime) {
        return resolveDateTime(dateTime).split(" - ")[1] ?? "";
    }
</script>

<section class="bg-blue-marinho">
    <EditorialTitle title="Programação" listLabel="Filtrar programas por formato">
        {#each filters as item}
            <li class="flex h-8 items-center border-l border-neutral-gray/35 px-3 first:border-none first:pl-0 xl:px-5">
                <Link
                    href={filterHref(item.executionMode)}
                    only={["programs", "activeProgramMode"]}
                    preserveScroll
                    class={[
                        "group/item flex cursor-pointer items-center gap-1 whitespace-nowrap rounded-md font-noto-sans text-base font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:text-orange-citric focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none",
                        activeProgramMode === item.executionMode ? "text-orange-citric" : "text-neutral-gray",
                    ]}
                >
                    <img
                        src={item.icon}
                        alt=""
                        aria-hidden="true"
                        class={[
                            "size-6 group-hover/item:scale-105 group-hover/item:filter-orange-citric group-focus-visible/item:scale-105 group-focus-visible/item:filter-orange-citric motion-reduce:transform-none",
                            activeProgramMode === item.executionMode ? "filter-orange-citric" : "filter-neutral-gray",
                        ]}
                        loading="lazy"
                    />
                    {item.title}
                </Link>
            </li>
        {/each}
    </EditorialTitle>

    <div class="container-page py-12">
        {#if selectedPrograms.length > 0}
            <GridList preset="wide">
                {#each selectedPrograms as item}
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
                            {#if activeProgramMode === "live" && item.access_type === "free"}
                                <dl class="w-full rounded-md py-2 px-4 bg-suspense-aurora flex justify-center mb-2">
                                    <dd class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                        A qualquer momento
                                    </dd>
                                </dl>
                            {:else if item.airtimes.length > 0}
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
                                            {resolveScheduledDate(schedule.scheduled_at)}
                                        </dt>
                                        <dd class="block text-blue-marinho text-sm font-noto-sans italic uppercase font-extrabold">
                                            {resolveScheduledHour(schedule.scheduled_at)}
                                        </dd>
                                    </dl>
                                {/each}
                            {/if}
                        </div>
                        </article>
                    </li>
                {/each}
            </GridList>
            <Pagination pages={programs} only={["programs", "activeProgramMode"]} loadingLabel="Carregando programação..." />
        {:else}
            <p class="text-center font-noto-sans text-lg font-extrabold italic uppercase text-neutral-gray">
                Nenhum programa encontrado neste formato.
            </p>
        {/if}
    </div>
</section>
