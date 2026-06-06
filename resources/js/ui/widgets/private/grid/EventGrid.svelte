<script>
    export let title;
    export let variant = "default";

    import { router, page, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination } from "@/ui/components/private";
    import { eventPermissions } from "@/utils";

    $: ({ events } = $page.props);

    let can = eventPermissions();

    const requestDeactivateEvent = (event) => {
        router.delete(`/panel/media/event/${event}`, {}, {
                preserveScroll: true,
            });
    };
</script>

{#if events && variant === "default"}
    <Section {title}>
        <div class="gap-6 grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5">
            {#each events.data as item}
                <article class="w-full h-56 rounded-md p-4 relative bg-blue-skywave">
                    <div class="font-noto-sans text-lg text-suspense-aurora line-clamp-5 uppercase">
                        {item.title}
                    </div>
                    <div class="grid grid-cols-3 absolute bottom-2 left-4 w-[calc(100%-2rem)]">
                        <div class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-lg text-suspense-aurora truncate">
                            <img
                                src="/svg/statistics.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-5 filter-suspense-aurora"
                                loading="lazy"
                            />
                            {item.views ?? 0}
                        </div>
                        <div class="font-noto-sans font-extrabold italic uppercase text-lg text-suspense-aurora text-center truncate">
                            {item.author.nickname}
                        </div>
                        <div class="flex gap-3 justify-end mt-1">
                            <a
                                title=""
                                href={`/materia/${item.slug}`}
                                target="_blank"
                                aria-label="Visualizar"
                                class="cursor-pointer"
                            >
                                <img
                                    src="/svg/eye.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 filter-suspense-aurora"
                                    loading="lazy"
                                />
                            </a>
                            <Link
                                title=""
                                href={`/panel/post/${item.uuid}`}
                                aria-label="Editar"
                                class="cursor-pointer"
                            >
                                <img
                                    src="/svg/edit.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-4 filter-suspense-aurora"
                                    loading="lazy"
                                />
                            </Link>
                        </div>
                    </div>
                </article>
            {/each}
        </div>
    </Section>
    <ButtonPagination pages={events} only={["events"]} />
{/if}

{#if events && variant === "detailed"}
    <Section title="Eventos">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            {#each events.data as item}
                <article class="flex flex-col gap-2">
                    <div class="h-65 bg-blue-skywave rounded-sm relative overflow-hidden">
                        <img
                            class="w-full h-65 object-cover aspect-square brightness-50"
                            src={item.cover}
                            alt={`Evento ${item.title}`}
                        />
                        <div class="flex gap-4 absolute bottom-3 right-3">
                            <Link
                                title=""
                                href={`/event/${item.uuid}`}
                                type="button"
                                class="cursor-pointer"
                                aria-label="Editar"
                            >
                                <img
                                    src="/svg/edit.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-5 filter-suspense-aurora"
                                    loading="lazy"
                                />
                            </Link>
                            {#if can.deactivate}
                                <button
                                    type="button"
                                    class="cursor-pointer"
                                    aria-label="Desativar"
                                    on:click={requestDeactivateEvent(item.uuid)}
                                >
                                    <img
                                        src="/svg/trash.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 filter-suspense-aurora"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                        </div>
                    </div>
                    <div class="rounded-sm bg-orange-amber py-1 px-5 text-suspense-aurora text-center font-noto-sans font-semibold line-clamp-2">
                        {item.address}
                    </div>
                    <div class="rounded-sm bg-orange-amber py-1 px-5 text-suspense-aurora text-center font-noto-sans font-semibold">
                        {item.dates}
                    </div>
                </article>
            {/each}
        </div>
    </Section>
    <ButtonPagination pages={events} only={["events"]} />
{/if}
