<script>
    export let title;

    import { router, page, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination } from "@/ui/components/private/";
    import { podcastPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ podcasts } = $page.props);

    let can = podcastPermissions();

    const requestDeactivatePodcast = (podcast) => {
        router.delete(`/panel/podcast/${podcast}`, {},
            { preserveScroll: true },
        );
    };
</script>

{#if podcasts}
    <Section {title}>
        <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 lg:gap-y-10 lg:gap-x-5">
            {#each podcasts.data as item}
                <li>
                    <article>
                        <div class="aspect-square">
                            <img
                                class="w-full h-full rounded-md"
                                src={resolvePlaceholderImage(item.image, "placeholder")}
                                alt={`Capa do podcast ${item.title}`}
                            />
                        </div>
                        <div class="flex justify-between mt-3">
                            <div class="text-orange-amber text-2xl font-noto-sans font-extrabold uppercase italic">
                                S{item.season}-EP{item.episode}
                            </div>
                            <div class="flex items-center gap-3">
                                {#if can.update}
                                    <Link
                                        href={`/podcast/${item.uuid}`}
                                        aria-label={`Editar ${item.title}`}
                                    >
                                        <img
                                            src="/svg/edit.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-5 filter-suspense-aurora"
                                            loading="lazy"
                                        />
                                    </Link>
                                {/if}
                                {#if can.deactivate}
                                    <button type="button" class="cursor-pointer" aria-label={`Desativar ${item.title}`} on:click={() => requestDeactivatePodcast(item.uuid)}>
                                        <img
                                            src="/svg/trash.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-5 filter-red-crimson"
                                            loading="lazy"
                                        />
                                    </button>
                                {/if}
                            </div>
                        </div>
                    </article>
                </li>
            {/each}
        </ul>
        <ButtonPagination pages={podcasts} only={["podcasts"]} />
    </Section>
{/if}
