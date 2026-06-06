<script>
    export let title;

    import { router, page, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination } from "@/ui/components/private/";
    import { podcastPermissions } from "@/utils";

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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 lg:gap-y-10 lg:gap-x-5">
            {#each podcasts.data as item}
                <article>
                    <div class="aspect-square">
                        <img
                            class="w-full h-full rounded-md"
                            src={item.image}
                            alt={`Capa do podcast ${item.title}`}
                        />
                    </div>
                    <dl class="flex justify-between mt-3">
                        <dt class="text-orange-amber text-2xl font-noto-sans font-extrabold uppercase italic">
                            S{item.season}-EP{item.episode}
                        </dt>
                        <dd class="flex items-center gap-3">
                            {#if can.update}
                                <Link
                                    title=""
                                    href={`/podcast/${item.uuid}`}
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
                            {/if}
                            {#if can.deactivate}
                                <button type="button" class="cursor-pointer" aria-label="Desativar" on:click={() => requestDeactivatePodcast(item.uuid)}>
                                    <img
                                        src="/svg/trash.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-5 filter-red-crimson"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                        </dd>
                    </dl>
                </article>
            {/each}
        </div>
        <ButtonPagination pages={podcasts} only={["podcasts"]} />
    </Section>
{/if}
