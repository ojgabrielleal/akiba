<script>
    import Cookies from "js-cookie";
    import { router } from "@inertiajs/svelte";

    import { EmptyState, GridList, IconButton, Pagination, Section, Tooltip } from "@/lib/components/private";
    import { postPermissions, resolveStatusBackground } from "@/lib/utils";

    export let title;
    export let posts = null;
    export let actions = [];

    const can = postPermissions();
    $: canReviewOpinions = can.approve || can.publish;
    const reviewStatusBadges = [
        { status: "draft", label: "Rascunho", class: "bg-green-mint text-blue-marinho" },
        { status: "revision", label: "Avaliação", class: "bg-orange-amber text-blue-marinho" },
        { status: "published", label: "Publicada", class: "bg-blue-cerulean text-suspense-aurora" },
    ];

    function operation(module) {
        Cookies.set("akiba_post_show_editor", true)
        Cookies.set("akiba_post_module", module);
    }

    function requestDeactivate(post) {
        router.patch(`/panel/post/${post.uuid}/deactivate`, {}, {
            preserveScroll: true,
        });
    }

    function statusItem(item) {
        if (!canReviewOpinions && item.module === "review" && item.review_status) {
            return { ...item, status: item.review_status };
        }

        return item;
    }
</script>

{#if posts}
    <Section {title} {actions}>
        {#if posts.data.length > 0}
        <GridList preset="content">
            {#each posts.data as item (item.uuid)}
                <li
                    class="relative h-53 w-full overflow-hidden rounded-md bg-blue-ocean transition hover:-translate-y-0.5 focus-within:ring-2 focus-within:ring-orange-citric"
                >
                    <article class="h-full">
                        <a
                            href={item.href}
                            class="block h-full focus:outline-none"
                            aria-label={`Abrir ${item.title} no site`}
                        >
                            <div class="p-4">
                                <h3 class="font-noto-sans text-lg text-suspense-aurora line-clamp-4 uppercase">
                                    {item.title}
                                </h3>
                            </div>
                            {#if canReviewOpinions && item.module === "review" && item.review_status_counts}
                                <div class="absolute bottom-11 left-3 right-3 flex justify-center">
                                    <Tooltip position="top">
                                        <div class="flex min-h-6 items-center justify-center gap-1 rounded-sm px-1">
                                            {#each reviewStatusBadges as badge}
                                                {@const count = item.review_status_counts[badge.status] ?? 0}
                                                {#if count > 0}
                                                    <span
                                                        class={`inline-flex items-center justify-center rounded px-1.5 py-0.5 font-noto-sans text-[0.55rem] font-extrabold italic uppercase leading-none ${badge.class}`}
                                                        aria-label={`${count} ${badge.label}`}
                                                    >
                                                        {count} {badge.label}
                                                    </span>
                                                {/if}
                                            {/each}
                                        </div>
                                        <div slot="content" class="space-y-0.5 text-left">
                                            {#each reviewStatusBadges as badge}
                                                {@const count = item.review_status_counts[badge.status] ?? 0}
                                                {#if count > 0}
                                                    <div>{count} {badge.label}</div>
                                                {/if}
                                            {/each}
                                        </div>
                                    </Tooltip>
                                </div>
                            {/if}
                        </a>
                        <div class={`grid grid-cols-[0.4fr_1fr_0.6fr] items-center absolute bottom-0 w-full py-1 px-4 ${resolveStatusBackground(statusItem(item), { useValidity: false })}`}>
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
                            <div class="mt-[0.1rem] w-full truncate text-center font-noto-sans text-sm font-extrabold italic uppercase text-suspense-aurora">
                                {item.module === "review" ? "Review" : item.author.nickname}
                            </div>
                            <div class="relative z-10 flex gap-1 justify-end mt-1">
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
