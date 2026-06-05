<script>
    export let title;

    import Cookies from "js-cookie";
    import { page, router, Link } from "@inertiajs/svelte";
    import { Section, ButtonPagination, Tooltip } from "@/ui/components/private";
    import { postPermissions } from "@/utils";

    $: ({ posts } = $page.props);

    let can = postPermissions();

    const operation = (module) => {
        Cookies.set("akiba_post_show_editor", true)
        Cookies.set("akiba_post_module", module);
    }

    const requestDeactivate = (post) => {
        router.delete(`/panel/post/${post.uuid}`, {
            preserveScroll: true,
        });
    };
</script>

{#if posts}
    <Section {title}>
        <div class="gap-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            {#each posts.data as item}
                <article class="w-full h-53 bg-blue-ocean rounded-md overflow-hidden relative ">
                    <div class="p-4">
                          <div class="font-noto-sans text-lg text-suspense-aurora line-clamp-4 uppercase">
                            {item.title}
                        </div>
                    </div>
                    <div class={["grid grid-cols-[0.4fr_1fr_0.6fr] items-center absolute bottom-0 w-full py-1 px-4",
                        { "bg-orange-amber": item.status === "draft" },
                        { "bg-blue-cerulean": item.status === "published" },
                        { "bg-green-mint": item.status === "revision" },
                    ]}>
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
                                <Tooltip>
                                    <button
                                        type="button"
                                        aria-label="Remover"
                                        class="w-7 h-7 bg-blue-night rounded-md flex items-center justify-center cursor-pointer"
                                        on:click={() => requestDeactivate(item)}
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
                                        href={`/panel/post/${item.uuid}`}
                                        aria-label="Editar"
                                        class="w-7 h-7 bg-blue-night rounded-md flex items-center justify-center cursor-pointer"
                                        on:click={() => operation(item.module)}
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
            {/each}
        </div>
        <ButtonPagination pages={posts} only={["posts"]} />
    </Section>
{/if}
