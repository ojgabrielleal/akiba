<script>
    import { EmptyState, Modal, Pagination, Section } from "@/lib/components/private";
    import { router } from "@inertiajs/svelte";
    import { page } from "@inertiajs/svelte";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let posts = null;

    const number = new Intl.NumberFormat("pt-BR");
    let viewersModal;
    let viewers = [];
    let viewersLoading = false;
    let viewersError = false;
    let selectedPost = "";
    $: filters = $page.url ? new URL($page.url, window.location.origin).searchParams : new URLSearchParams();
    $: selectedType = filters.get("post_access_type") ?? "all";
    $: selectedPeriod = filters.get("post_access_period") ?? "all";

    function applyFilters(event) {
        const params = new URLSearchParams(window.location.search);
        const [name, value] = event.currentTarget.name === "post_access_type"
            ? ["post_access_type", event.currentTarget.value]
            : ["post_access_period", event.currentTarget.value];
        value === "all" ? params.delete(name) : params.set(name, value);
        params.delete("posts_page");
        router.get(`/panel/reports?${params.toString()}`, {}, { preserveScroll: true, preserveState: true, only: ["postAccess"] });
    }

    async function showViewers(post) {
        selectedPost = post.title;
        viewers = [];
        viewersError = false;
        viewersLoading = true;
        viewersModal.open();
        try {
            const response = await fetch(`/panel/reports/post/${post.uuid}/viewers`);
            if (!response.ok) throw new Error("viewer request failed");
            const payload = await response.json();
            viewers = payload.viewers ?? payload.data ?? payload;
        } catch (error) {
            viewersError = true;
        } finally {
            viewersLoading = false;
        }
    }
</script>

<Section title="Histórico de matérias">
    <div class="mb-4 flex flex-wrap gap-3">
        <label class="font-noto-sans text-sm text-suspense-aurora">
            Visualizações
            <select name="post_access_type" value={selectedType} on:change={applyFilters} class="ml-2 rounded-md border-0 bg-blue-ocean px-3 py-2 text-sm text-suspense-aurora">
                <option value="all">Todos</option>
                <option value="recognized">Com conta</option>
                <option value="anonymous">Anônimos</option>
            </select>
        </label>
        <label class="font-noto-sans text-sm text-suspense-aurora">
            Período
            <select name="post_access_period" value={selectedPeriod} on:change={applyFilters} class="ml-2 rounded-md border-0 bg-blue-ocean px-3 py-2 text-sm text-suspense-aurora">
                <option value="all">Todo o histórico</option>
                <option value="15">Últimos 15 dias</option>
                <option value="30">Último mês</option>
            </select>
        </label>
    </div>
    {#if posts?.data?.length}
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse font-noto-sans">
                <thead>
                    <tr class="text-sm font-extrabold uppercase italic text-orange-amber">
                        {#each ["Matéria", "Visualizações", "Interações", "Última visualização"] as label}
                            <th scope="col" class="px-3 py-3 text-start whitespace-nowrap">{label}</th>
                        {/each}
                    </tr>
                </thead>
                <tbody>
                    {#each posts.data as post (post.uuid)}
                        <tr class="border-t border-suspense-aurora/35 text-sm text-suspense-aurora">
                            <td class="min-w-64 max-w-96 px-3 py-3 font-normal">
                                {#if post.url}
                                    <a href={post.url} target="_blank" rel="noopener noreferrer" class="text-suspense-aurora  focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-citric">{post.title}<span class="sr-only"> (abre em nova aba)</span></a>
                                {:else}
                                    {post.title}
                                {/if}
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="font-bold tabular-nums">{number.format(post.views ?? 0)} total</div>
                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs font-normal text-suspense-aurora/70">
                                    {#if post.recognized_views > 0}
                                        <button type="button" class="cursor-pointer text-start underline-offset-2 hover:underline" on:click={() => showViewers(post)}>
                                            Com conta: <strong class="text-orange-amber">{number.format(post.recognized_views)}</strong>
                                        </button>
                                    {:else}
                                        <span>Com conta: <strong class="text-orange-amber">0</strong></span>
                                    {/if}
                                    <span>Anônimos: <strong class="text-orange-amber">{number.format(post.anonymous_views ?? 0)}</strong></span>
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle text-xs tabular-nums text-suspense-aurora/75">
                                <div>Reações: <strong class="text-orange-amber">{number.format(post.reactions ?? 0)}</strong></div>
                                <div class="mt-1">Curtidas: <strong class="text-orange-amber">{number.format(post.likes ?? 0)}</strong></div>
                                <div class="mt-1">Comentários: <strong class="text-orange-amber">{number.format(post.comments ?? 0)}</strong></div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 align-middle">
                                {#if post.last_view}
                                    <div>{post.last_view}</div>
                                    <div class="mt-1 text-xs font-normal text-suspense-aurora/70">Visualizado por: {post.last_viewer}</div>
                                {:else}
                                    Sem acessos
                                {/if}
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
        <Pagination pages={posts} only={["postAccess"]} pageName="posts_page" />
    {:else}
        <EmptyState title="Nenhuma postagem encontrada" description="As postagens e seus acessos aparecerão aqui." />
    {/if}
</Section>

<Modal bind:this={viewersModal} title="Contas que visualizaram" size="sm">
    <svelte:fragment slot="content">
        {#if viewersLoading}
            <p class="p-4 text-sm text-blue-night/70">Carregando...</p>
        {:else if viewersError}
            <p class="p-4 text-sm text-blue-night/70">Não foi possível carregar as contas.</p>
        {:else if viewers.length}
            <ul class="grid gap-2 p-2 font-noto-sans">
                {#each viewers as viewer}
                    <li class="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-2 border-b border-blue-night/10 pb-2 last:border-b-0 last:pb-0">
                        <img src={resolvePlaceholderImage(viewer.avatar, "avatar")} alt="" aria-hidden="true" class="size-10 rounded-full border-2 border-blue-night/15 object-cover object-top" loading="lazy" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black uppercase italic leading-tight text-blue-night">{viewer.name}</p>
                            <p class="mt-1 text-xs font-bold uppercase italic text-blue-night">Visualizou: <span class="text-orange-amber">{viewer.views}x</span></p>
                            <p class="mt-1 text-xs font-bold uppercase italic text-neutral-gray">Última visualização em: {viewer.last_view}</p>
                        </div>
                    </li>
                {/each}
            </ul>
        {:else}
            <p class="p-4 text-sm text-blue-night/70">Nenhuma conta encontrada.</p>
        {/if}
    </svelte:fragment>
</Modal>
