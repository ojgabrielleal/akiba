<script>
    import { Link, page } from "@inertiajs/svelte";
    import { AdvertisementSlot, Section } from "@/ui/components/public";

    $: events = $page.props.events?.data ?? [];

    const resolveEventDate = (event) => event.metadata?.dates ?? "";
    const resolveEventPlace = (event) => event.metadata?.address ?? "";
</script>

{#if events.length > 0}
    <Section title="Calendário de eventos">
        <div class="grid grid-cols-1 gap-x-5 gap-y-2 lg:grid-cols-[minmax(0,1fr)_15rem]">
            <div class="min-w-0">
                <div class="w-full font-noto-sans text-lg uppercase italic">
                    <div class="mb-2 grid grid-cols-[1.5fr_0.85fr_1fr] gap-2 text-suspense-aurora/80">
                        <div class="px-4 pb-1 text-center text-xl font-black">Evento</div>
                        <div class="px-4 pb-1 text-center text-xl font-black">Data</div>
                        <div class="px-4 pb-1 text-center text-xl font-black">⌖ Local</div>
                    </div>
                    <ul class="grid gap-2">
                        {#each events as item (item.uuid)}
                            <li class="min-w-0">
                                <Link
                                    href={`event/${item.slug}`}
                                    aria-label={`Ver evento: ${item.title}`}
                                    class="group grid grid-cols-[1.5fr_0.85fr_1fr] gap-2 rounded-md font-black transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                                >
                                    <div class="min-w-0 rounded-md bg-orange-morning px-5 py-2.5 text-center text-lg text-blue-night">
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
            <div class="hidden self-end overflow-hidden rounded-md transition duration-300 ease-out hover:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none lg:block lg:h-[calc(100%-2.875rem)]">
                <AdvertisementSlot class="h-full" />
            </div>
        </div>
    </Section>
{/if}
