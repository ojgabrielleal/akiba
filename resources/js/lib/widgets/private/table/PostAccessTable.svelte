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
    let search = "";
    $: filters = $page.url ? new URL($page.url, window.location.origin).searchParams : new URLSearchParams();
    $: selectedType = filters.get("post_access_type") ?? "all";
    $: selectedPeriod = filters.get("post_access_period") ?? "all";

    $: selectedSort = filters.get("post_access_sort") ?? "views";
    $: search = filters.get("post_access_search") ?? "";

    function applyFilters(event) {
        const params = new URLSearchParams(window.location.search);
        const { name, value } = event.currentTarget;
        value === "all" || value === "views" || !value.trim() ? params.delete(name) : params.set(name, value.trim());
        params.delete("posts_page");
        router.get(`/panel/reports?${params.toString()}`, {}, { preserveScroll: true, preserveState: true, only: ["postAccess"] });
    }

    async function showViewers(post) {
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

<Section aria-label="Desempenho das matérias">
    <header class="mb-4">
        <div class="flex items-center gap-4">
            <h2 class="font-noto-sans text-[1.3rem] font-black uppercase italic text-orange-amber">Desempenho das matérias</h2>
            <div class="h-px flex-1 bg-orange-amber"></div>
        </div>
        <p class="mt-2 text-sm text-suspense-aurora/70">Acompanhe visualizações e interações das publicações.</p>
    </header>
    <div class="mb-4 grid gap-3 font-noto-sans sm:grid-cols-2 xl:grid-cols-[minmax(16rem,1fr)_auto_auto_auto]">
        <form on:submit|preventDefault={(event) => applyFilters({ currentTarget: event.currentTarget.elements.namedItem("post_access_search") })} class="min-w-0">
            <label for="post-access-search" class="mb-2 block text-xs font-extrabold uppercase italic text-suspense-aurora">Buscar</label>
            <div class="flex gap-2">
                <input id="post-access-search" name="post_access_search" type="search" bind:value={search} maxlength="200" placeholder="Digite o título" class="h-10 min-w-0 flex-1 rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm text-suspense-aurora placeholder:text-suspense-aurora/60 focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric" />
                <button type="submit" class="inline-flex h-10 cursor-pointer items-center justify-center rounded-md bg-orange-citric px-4 text-sm font-extrabold uppercase italic text-blue-night transition hover:bg-orange-citric/85 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-citric">Buscar</button>
            </div>
        </form>
        <label class="text-xs font-extrabold uppercase italic text-suspense-aurora">
            Tipo
            <select name="post_access_type" value={selectedType} on:change={applyFilters} class="mt-2 block h-10 w-full cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric">
                <option value="all">Todos</option>
                <option value="recognized">Com conta</option>
                <option value="anonymous">Anônimos</option>
            </select>
        </label>
        <label class="text-xs font-extrabold uppercase italic text-suspense-aurora">
            Período
            <select name="post_access_period" value={selectedPeriod} on:change={applyFilters} class="mt-2 block h-10 w-full cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric">
                <option value="all">Todo o histórico</option>
                <option value="15">Últimos 15 dias</option>
                <option value="30">Últimos 30 dias</option>
            </select>
        </label>
        <label class="text-xs font-extrabold uppercase italic text-suspense-aurora">
            Ordenar por
            <select name="post_access_sort" value={selectedSort} on:change={applyFilters} class="mt-2 block h-10 w-full cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora focus:border-orange-citric focus:outline-none focus:ring-1 focus:ring-orange-citric">
                <option value="views">Mais visualizadas</option>
                <option value="interactions">Mais interações</option>
                <option value="recent">Visualização mais recente</option>
            </select>
        </label>
    </div>
    <p class="mb-3 text-xs leading-relaxed text-suspense-aurora/65">Visualizações são acessos registrados, incluindo repetições. O período e o tipo de visitante filtram os acessos; as interações mostram o total acumulado.</p>
    {#if posts?.data?.length}
        <table class="block w-full border-collapse font-noto-sans lg:table">
            <caption class="sr-only">Desempenho das matérias conforme os filtros selecionados</caption>
            <thead class="hidden lg:table-header-group">
                <tr class="border-b border-suspense-aurora/35 text-sm font-extrabold uppercase italic text-orange-amber">
                    {#each ["Matéria", "Visualizações", "Interações", "Última visualização"] as label}
                        <th scope="col" class="px-3 py-3 text-start">{label}</th>
                    {/each}
                </tr>
            </thead>
            <tbody class="grid gap-3 lg:table-row-group">
                {#each posts.data as post (post.uuid)}
                    <tr class="grid min-w-0 gap-4 rounded-md border border-suspense-aurora/35 p-4 text-sm text-suspense-aurora lg:table-row lg:rounded-none lg:border-x-0 lg:border-t-0 lg:p-0">
                        <td class="block min-w-0 lg:table-cell lg:w-[38%] lg:px-3 lg:py-3 lg:align-middle">
                            {#if post.url}
                                <a href={post.url} target="_blank" rel="noopener noreferrer" class="inline-block break-words font-normal leading-snug text-suspense-aurora transition duration-300 ease-out hover:-translate-y-0.5 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-citric motion-reduce:transform-none motion-reduce:transition-none">{post.title}<span class="sr-only"> (abre em nova aba)</span></a>
                            {:else}
                                <span class="break-words font-normal leading-snug">{post.title}</span>
                            {/if}
                        </td>
                        <td class="block lg:table-cell lg:px-3 lg:py-3 lg:align-middle">
                            <span class="mb-1 block text-xs text-suspense-aurora/60 lg:hidden">Visualizações</span>
                            <div class="text-base font-extrabold tabular-nums">{number.format(post.views ?? 0)} <span class="text-xs font-normal text-suspense-aurora/60">acessos</span></div>
                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-suspense-aurora/65">
                                {#if selectedType !== "anonymous"}<span>Com conta: <strong class="font-semibold text-orange-amber">{number.format(post.recognized_views ?? 0)}</strong></span>{/if}
                                {#if selectedType !== "recognized"}<span>Anônimos: <strong class="font-semibold text-orange-amber">{number.format(post.anonymous_views ?? 0)}</strong></span>{/if}
                            </div>
                            {#if post.recognized_views > 0}
                                <button type="button" on:click={() => showViewers(post)} class="mt-2 inline-flex cursor-pointer items-center rounded-md border border-orange-citric/40 px-2 py-1 text-xs font-extrabold uppercase italic text-orange-citric transition hover:bg-orange-citric/10 motion-reduce:transition-none hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-citric" aria-label={`Ver contas que visualizaram: ${post.title}`}>Ver contas</button>
                            {/if}
                        </td>
                        <td class="block lg:table-cell lg:px-3 lg:py-3 lg:align-middle">
                            <span class="mb-2 block text-xs text-suspense-aurora/60 lg:hidden">Interações acumuladas</span>
                            <div class="flex flex-wrap gap-x-4 gap-y-2">
                                {#each [["Reações", post.reactions], ["Curtidas", post.likes], ["Comentários", post.comments]] as [label, count]}
                                    <div class="text-xs text-suspense-aurora/60"><strong class={`block text-base font-extrabold tabular-nums ${count > 0 ? 'text-orange-amber' : 'text-suspense-aurora/40'}`}>{number.format(count ?? 0)}</strong>{label}</div>
                                {/each}
                            </div>
                        </td>
                        <td class="block text-sm tabular-nums lg:table-cell lg:px-3 lg:py-3 lg:align-middle">
                            <span class="mb-1 block text-suspense-aurora/60 lg:hidden">Última visualização</span>
                            <span class="whitespace-nowrap">{post.last_view ?? "Sem acessos"}</span>
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
        <Pagination pages={posts} only={["postAccess"]} pageName="posts_page" activeClass="bg-orange-citric text-blue-night opacity-100" />
    {:else}
        <EmptyState title="Nenhuma matéria encontrada" description="Não há matérias com acessos para estes filtros. Tente outro título, período ou tipo de visitante." />
    {/if}
</Section>

<Modal bind:this={viewersModal} title="Contas que visualizaram" size="sm">
    <svelte:fragment slot="content">
        {#if viewersLoading}
            <p class="p-4 text-sm text-blue-night/70">Carregando...</p>
        {:else if viewersError}
            <p class="p-4 text-sm text-blue-night/70">Não foi possível carregar as contas.</p>
        {:else if viewers.length}
            <ul class="grid gap-2 font-noto-sans">
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
