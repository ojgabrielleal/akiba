<script>
    export let title = "Visitantes online";
    export let presence = null;
</script>

<section class="mb-10 font-noto-sans">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-blue-night">{title}</h2>
            <p class="text-sm text-blue-night/70">
                {presence?.total_conected ?? 0} conectados, {presence?.listeners ?? 0} ouvindo nos ultimos {presence?.window_seconds ?? 90}s
            </p>
        </div>
        {#if presence?.updated_at}
            <span class="text-xs font-semibold uppercase tracking-wide text-blue-night/50">
                Atualizado {presence.updated_at}
            </span>
        {/if}
    </div>

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-md bg-blue-ocean px-4 py-3 text-white">
            <span class="block text-xs uppercase text-white/60">Autenticados</span>
            <strong class="text-2xl">{presence?.recognized_users ?? 0}</strong>
        </div>
        <div class="rounded-md bg-blue-night px-4 py-3 text-white">
            <span class="block text-xs uppercase text-white/60">Anônimos</span>
            <strong class="text-2xl">{presence?.anonimus_user ?? 0}</strong>
        </div>
        <div class="rounded-md bg-blue-cerulean px-4 py-3 text-white">
            <span class="block text-xs uppercase text-white/70">Ouvindo</span>
            <strong class="text-2xl">{presence?.listeners ?? 0}</strong>
        </div>
    </div>

    <div class="mb-4 overflow-hidden rounded-md border border-blue-night/10 bg-white">
        {#if presence?.pages?.length}
            <div class="divide-y divide-blue-night/10">
                {#each presence.pages as page (page.path)}
                    <article class="grid gap-3 px-4 py-3 sm:grid-cols-[minmax(0,1fr)_8rem_10rem] sm:items-center">
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-bold text-blue-night">
                                {page.title || page.path}
                            </h3>
                            <p class="truncate text-xs text-blue-night/60">
                                {page.path}
                            </p>
                        </div>
                        <strong class="text-sm text-blue-cerulean">
                            {page.visitors} {page.visitors === 1 ? "visitante" : "visitantes"}
                        </strong>
                        <span class="text-xs text-blue-night/50">
                            {page.last_seen_at}
                        </span>
                    </article>
                {/each}
            </div>
        {:else}
            <div class="px-4 py-8 text-center text-sm text-blue-night/60">
                Nenhum visitante conectado agora.
            </div>
        {/if}
    </div>

    {#if presence?.visitors?.length}
        <div class="overflow-hidden rounded-md border border-blue-night/10 bg-white">
            <div class="divide-y divide-blue-night/10">
                {#each presence.visitors as visitor, index (`${visitor.identity?.uuid ?? "anonymous"}-${visitor.page_path}-${index}`)}
                    <article class="grid gap-3 px-4 py-3 sm:grid-cols-[12rem_minmax(0,1fr)_7rem_10rem] sm:items-center">
                        <strong class="truncate text-sm text-blue-night">
                            {visitor.identity?.name ?? "Anônimo"}
                        </strong>
                        <span class="truncate text-xs text-blue-night/60">
                            {visitor.page_title || visitor.page_path}
                        </span>
                        <span class={[
                            "rounded px-2 py-1 text-center text-xs font-bold",
                            visitor.listening ? "bg-blue-cerulean text-white" : "bg-blue-night/5 text-blue-night/60",
                        ].filter(Boolean).join(" ")}>
                            {visitor.listening ? "Ouvindo" : "Navegando"}
                        </span>
                        <span class="text-xs text-blue-night/50">
                            {visitor.last_seen_at}
                        </span>
                    </article>
                {/each}
            </div>
        </div>
    {/if}
</section>
