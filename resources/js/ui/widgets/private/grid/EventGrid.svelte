<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { ButtonPagination, IconButton, Section } from "@/ui/components/private";
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
                        <IconButton
                            variant="eye"
                            label="Visualizar"
                            href={`/materia/${item.slug}`}
                            size="sm"
                            surface="transparent"
                            tone="light"
                            target="_blank"
                            rel="noopener noreferrer"
                        />
                        {#if can.update}
                            <IconButton
                                variant="edit"
                                label="Atualizar"
                                href={`/panel/post/${item.uuid}`}
                                size="sm"
                                surface="transparent"
                                tone="light"
                            />
                        {/if}
                    </div>
                </div>
                </article>
            </li>
        {/each}
    </ul>
</Section>
<ButtonPagination pages={events} only={["events"]} />
