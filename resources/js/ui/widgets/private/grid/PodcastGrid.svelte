<script>
    export let title;

    import { router, page, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination, Tooltip } from "@/ui/components/private/";
    import { podcastPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ podcasts } = $page.props);

    let can = podcastPermissions();

    const requestDeactivate = (item) => {
        router.patch(`/panel/podcast/${item.uuid}/deactivate`, {},
            { preserveScroll: true },
        );
    };
</script>

{#if podcasts}
     <Section {title}>
        <ul class="gap-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            {#each podcasts.data as item}
                <li class="w-full h-65 bg-blue-ocean rounded-md overflow-hidden relative">
                    <article>
                    <img 
                        src={item.image}
                        class="w-full h-65 object-cover"
                        alt={item.title}
                    />
                    <div class="grid grid-cols-[0.4fr_1fr_0.6fr] items-center bg-blue-cerulean absolute bottom-0 w-full py-1 px-4">
                        <div class="flex items-center gap-2 font-noto-sans font-extrabold italic uppercase text-md text-suspense-aurora truncate">
                            <img
                                src="/svg/eye.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-4 filter-suspense-aurora"
                                loading="lazy"
                            />
                            {item.views ?? 0}
                        </div>
                        <div class="mt-[0.1rem] w-full font-noto-sans font-extrabold text-sm text-center text-suspense-aurora italic uppercase truncate">
                            S{item.season} - Ep{item.episode}
                        </div>
                        <div class="flex gap-1 justify-end mt-1">
                            {#if can.deactivate}
                                <Tooltip>
                                    <button
                                        type="button"
                                        aria-label={`Remover ${item.title}`}
                                        class="w-7 h-7 bg-blue-night rounded-md flex items-center justify-center cursor-pointer"
                                        on:click={()=> requestDeactivate(item)}
                                    >
                                        <img
                                            src="/svg/trash.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-4 filter-red-crimson"
                                            loading="lazy"
                                        />
                                    </button>
                                    <div slot="content">
                                        Desativar
                                    </div>
                                </Tooltip>
                            {/if}
                            {#if can.update}
                                <Tooltip>
                                    <Link
                                        href={`/panel/podcast/${item.uuid}`}
                                        aria-label={`Atualizar ${item.title}`}
                                        class="w-7 h-7 bg-blue-night rounded-md flex items-center justify-center cursor-pointer"
                                    >
                                        <img
                                            src="/svg/edit.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-4 filter-orange-citric"
                                            loading="lazy"
                                        />
                                    </Link>
                                    <div slot="content">
                                        Atualizar
                                    </div>
                                </Tooltip>
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
