<script>
    import { Link } from "@inertiajs/svelte";
    import { AdvertisementSlot, Section } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let events = [];

    $: eventList = Array.isArray(events) ? events : events?.data ?? [];

    const resolveEventDate = (event) => event.metadata?.dates ?? "";
    const resolveEventPlace = (event) => event.metadata?.address ?? "";
</script>

{#if eventList.length > 0}
    <Section styles="public-event-calendar-original container-page mb-10">
        <div class="public-section-heading mb-5 flex items-center gap-3 after:h-px after:min-w-10 after:flex-1 after:bg-orange-citric after:content-[''] sm:gap-4">
            <h2 class="public-section-heading-title whitespace-nowrap font-noto-sans text-[1.3rem] font-black text-orange-citric uppercase italic">
                <span class="md:hidden">Eventos</span>
                <span class="hidden md:inline">Calendário de eventos</span>
            </h2>
        </div>
        <div class="grid grid-cols-1 gap-x-5 gap-y-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
            <div class="min-w-0">
                <div class="w-full font-noto-sans text-lg uppercase italic">
                    <ul class="grid grid-cols-1 gap-8 md:hidden">
                        {#each eventList as item (item.uuid)}
                            <li class="min-w-0">
                                <Link
                                    href={`/event/${item.slug}`}
                                    aria-label={`Ver evento: ${item.title}`}
                                    class="group block overflow-hidden rounded-md bg-blue-ocean font-black transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                                >
                                    <div class="relative aspect-[16/9] overflow-hidden bg-neutral-gray">
                                        <img
                                            src={resolvePlaceholderImage(item.cover || item.image, "placeholder")}
                                            alt=""
                                            aria-hidden="true"
                                            class="h-full w-full object-cover transition duration-300 ease-out group-hover:scale-[1.03] group-focus-visible:scale-[1.03] motion-reduce:transform-none motion-reduce:transition-none"
                                        />
                                    </div>
                                    <article class="min-h-[6rem] px-3 py-5">
                                        <h3 class="line-clamp-3 font-noto-sans text-lg leading-tight font-bold text-suspense-aurora uppercase italic">
                                            {item.title}
                                        </h3>
                                    </article>
                                </Link>
                            </li>
                        {/each}
                    </ul>

                    <div class="mb-2 hidden grid-cols-[1.5fr_0.85fr_1fr] gap-2 text-suspense-aurora/80 md:grid">
                        <div class="px-4 pb-1 text-center text-xl font-black">Evento</div>
                        <div class="px-4 pb-1 text-center text-xl font-black">Data</div>
                        <div class="px-4 pb-1 text-center text-xl font-black">Local</div>
                    </div>
                    <ul class="hidden gap-2 md:grid">
                        {#each eventList as item (item.uuid)}
                            <li class="min-w-0">
                                <Link
                                    href={`/event/${item.slug}`}
                                    aria-label={`Ver evento: ${item.title}`}
                                    class="group grid grid-cols-[1.5fr_0.85fr_1fr] gap-2 rounded-md font-black transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                                >
                                    <div class="min-w-0 rounded-md bg-orange-amber px-5 py-2.5 text-center text-lg text-blue-night">
                                        <span class="block truncate">
                                            {item.title}
                                        </span>
                                    </div>
                                    <div class="min-w-0 rounded-md bg-blue-ocean px-5 py-2.5 text-center text-lg text-suspense-aurora">
                                        <span class="block truncate">
                                            {resolveEventDate(item)}
                                        </span>
                                    </div>
                                    <div class="min-w-0 rounded-md bg-blue-ocean px-5 py-2.5 text-center text-lg text-suspense-aurora">
                                        <span class="block truncate">
                                            {resolveEventPlace(item)}
                                        </span>
                                    </div>
                                </Link>
                            </li>
                        {/each}
                    </ul>
                </div>
            </div>
            <div class="overflow-hidden rounded-md lg:h-full">
                <AdvertisementSlot class="h-44 lg:h-full lg:min-h-60" />
            </div>
        </div>
    </Section>
{/if}
