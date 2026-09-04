<script>
    import { Link } from "@inertiajs/svelte";
    import { AdvertisementSlot, Modal, Section } from "@/lib/components/public";
    import { resolveDate, resolvePlaceholderImage, themeClass } from "@/lib/utils";
    import EventRegistrationForm from "../form/EventRegistrationForm.svelte";

    export let events = [];

    $: eventList = Array.isArray(events) ? events : events?.data ?? [];
    const resolveEventDate = (event) => resolveDate(event.metadata?.dates) || "";
    const resolveEventPlace = (event) => event.metadata?.address ?? "";

    let registrationModalRef;
</script>

<Modal bind:this={registrationModalRef} title="Informar evento" size="sm">
    <EventRegistrationForm close={() => registrationModalRef.close()} />
</Modal>

<Section styles="public-event-calendar-original container-page mb-10">
    <div class={["public-section-heading mb-5 flex items-center gap-3 after:h-px after:min-w-10 after:flex-1 after:bg-orange-citric after:content-[''] sm:gap-4", themeClass("after:bg", "blue-cerulean", { theme: "light" })]}>
        <h2 class={["public-section-heading-title whitespace-nowrap font-noto-sans text-[1.3rem] font-black text-orange-citric uppercase italic", themeClass("text", "blue-cerulean", { theme: "light" })]}>
            <span class="md:hidden">Eventos</span>
            <span class="hidden md:inline">Calendário de eventos</span>
        </h2>
    </div>
    <div class="grid grid-cols-1 gap-x-5 gap-y-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
        <div class="min-w-0">
            <div class="w-full font-noto-sans text-lg uppercase italic">
                {#if eventList.length > 0}
                    <ul class="grid grid-cols-1 gap-8 md:hidden">
                        {#each eventList as item (item.uuid)}
                            <li class="min-w-0">
                                <div class="overflow-hidden rounded-md bg-blue-ocean">
                                    <Link
                                        href={`/event/${item.slug}`}
                                        aria-label={`Ver evento: ${item.title}`}
                                        class="group block font-black transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
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
                                </div>
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
                                    <div class={["min-w-0 rounded-md bg-blue-cerulean px-5 py-2.5 text-center text-lg", themeClass("text", "suspense-aurora", { fixed: true })]}>
                                        <span class="block truncate">
                                            {resolveEventDate(item)}
                                        </span>
                                    </div>
                                    <div class={["min-w-0 rounded-md bg-blue-cerulean px-5 py-2.5 text-center text-lg", themeClass("text", "suspense-aurora", { fixed: true })]}>
                                        <span class="block truncate">
                                            {resolveEventPlace(item)}
                                        </span>
                                    </div>
                                </Link>
                            </li>
                        {/each}
                    </ul>
                {:else}
                    <div class="flex min-h-64 flex-col items-center justify-center rounded-md border border-blue-skywave/15 bg-blue-ocean/25 px-5 py-10 text-center">
                        <img
                            src="/svg/angry.svg"
                            alt=""
                            aria-hidden="true"
                            class="size-18 opacity-90"
                        />
                        <h3 class="mt-5 font-noto-sans text-2xl font-black leading-tight text-orange-amber uppercase italic">
                            Sem eventos por enquanto
                        </h3>
                        <p class="mt-2 max-w-md font-noto-sans text-sm font-bold leading-6 text-neutral-gray not-italic normal-case">
                            Nossa agenda ainda está silenciosa. Quando pintar um evento otaku no radar, ele aparece aqui.
                        </p>
                    </div>
                {/if}

                <div class="mt-8 flex items-center justify-center gap-2 px-2 py-1 font-noto-sans not-italic uppercase">
                    <p class="text-center text-xs font-normal leading-none tracking-normal text-orange-citric not-italic lg:text-sm">
                        Algum evento otaku vai acontecer na sua região?
                    </p>
                    <button
                        type="button"
                        class="inline-flex min-h-7 shrink-0 cursor-pointer items-center justify-center rounded-md bg-orange-amber px-3 py-1 font-noto-sans text-sm font-black leading-none text-blue-night uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                        on:click={() => registrationModalRef.open()}
                    >
                        Conta pra gente!
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-hidden rounded-md lg:h-full">
            <AdvertisementSlot class="h-44 lg:h-full lg:min-h-60" />
        </div>
    </div>
</Section>
