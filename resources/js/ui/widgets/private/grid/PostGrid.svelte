<script>
    export let title;

    import Cookies from "js-cookie";
    import { page, router } from "@inertiajs/svelte";
    import { EmptyState, GridList, IconButton, Pagination, Section } from "@/ui/components/private";
    import { postPermissions, resolveStatusBackground } from "@/utils";

    $: ({ posts } = $page.props);

    let can = postPermissions();

    const operation = (module) => {
        Cookies.set("akiba_post_show_editor", true)
        Cookies.set("akiba_post_module", module);
    }

    const requestDeactivate = (post) => {
        router.patch(`/panel/post/${post.uuid}/deactivate`, {}, {
            preserveScroll: true,
        });
    };
</script>

{#if posts}
    <Section {title}>
        {#if posts.data.length > 0}
        <GridList preset="content">
            {#each posts.data as item}
                <li class="w-full h-53 bg-blue-ocean rounded-md overflow-hidden relative">
                    <article>
                    <div class="p-4">
                        <h3 class="font-noto-sans text-lg text-suspense-aurora line-clamp-4 uppercase">
                            {item.title}
                        </h3>
                    </div>
                    <div class={`grid grid-cols-[0.4fr_1fr_0.6fr] items-center absolute bottom-0 w-full py-1 px-4 ${resolveStatusBackground(item, { useValidity: false })}`}>
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
                            {item.module === "review" ? "Review" : item.author.nickname}
                        </div>
                        <div class="flex gap-1 justify-end mt-1">
                            {#if can.deactivate}
                                <IconButton
                                    variant="trash"
                                    label="Desativar"
                                    size="sm"
                                    surface="dark"
                                    on:click={() => requestDeactivate(item)}
                                />
                            {/if}
                            {#if can.update}
                                <IconButton
                                    variant="edit"
                                    label="Atualizar"
                                    href={`/panel/post/${item.uuid}`}
                                    size="sm"
                                    surface="dark"
                                    on:click={() => operation(item.module)}
                                />
                            {/if}
                        </div>
                    </div>
                    </article>
                </li>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                title="Nenhuma matéria encontrada"
                description="As matérias cadastradas aparecerão aqui."
            />
        {/if}
        <Pagination pages={posts} only={["posts"]} />
    </Section>
{/if}
