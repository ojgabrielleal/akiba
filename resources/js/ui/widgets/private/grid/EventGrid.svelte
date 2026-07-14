<script>
    export let title;

    import { page, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination } from "@/ui/components/private";
    import { eventPermissions } from "@/utils";

    $: ({ events } = $page.props);

    let can = eventPermissions();
</script>

<Section {title}>
    <ul class="gap-6 grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5">
        {#each events.data as item}
            <li>
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
                            href={`/materia/${item.slug}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label={`Visualizar ${item.title}`}
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
                        {#if can.update}
                            <Link
                                href={`/panel/post/${item.uuid}`}
                                aria-label={`Editar ${item.title}`}
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
                        {/if}
                    </div>
                </div>
                </article>
            </li>
        {/each}
    </ul>
</Section>
<ButtonPagination pages={events} only={["events"]} />
