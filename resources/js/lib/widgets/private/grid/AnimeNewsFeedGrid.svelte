<script>
    import { router } from "@inertiajs/svelte";
    import { Button, EmptyState, Pagination, Section } from "@/lib/components/private";
    import { resolveDateTime } from "@/lib/utils";

    export let title = "Feeds externos";
    export let actions = [];
    export let sources = [];
    export let selectedSource = null;
    export let feedPosts = null;
    export let endpoint = "/panel/post";
    export let onCreatePost = () => {};

    let currentSource = "";

    $: currentSource = selectedSource ?? "";

    function selectSource(event) {
        const source = event.currentTarget.value;

        router.get(
            endpoint,
            source ? { source } : {},
            {
                preserveState: true,
                preserveScroll: true,
                only: ["newsFeedPosts", "selectedNewsFeedSource"],
            }
        );
    }

    function createPost(item) {
        onCreatePost(item);
    }
</script>

<Section {title} {actions}>
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <select
            class="h-10 rounded-full border-2 border-blue-marinho bg-suspense-aurora px-4 font-noto-sans text-sm font-bold uppercase italic text-blue-marinho outline-none"
            bind:value={currentSource}
            on:change={selectSource}
            aria-label="Filtrar portal"
        >
            <option value="">Todos os portais</option>
            {#each sources as source}
                <option value={source.slug}>{source.name}</option>
            {/each}
        </select>
    </div>

    {#if feedPosts?.data?.length > 0}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {#each feedPosts.data as item}
                <article class="flex min-h-82 flex-col overflow-hidden rounded-md bg-blue-ocean">
                    <button
                        type="button"
                        class="h-40 bg-blue-marinho"
                        on:click={() => createPost(item)}
                        aria-label={`Ler ${item.title}`}
                    >
                        {#if item.image}
                            <img src={item.image} alt="" class="h-full w-full object-cover" loading="lazy" />
                        {/if}
                    </button>
                    <div class="flex min-w-0 flex-1 flex-col gap-3 p-4">
                        <div class="flex flex-wrap items-center gap-2 font-noto-sans text-xs font-black uppercase italic text-orange-citric">
                            <span>{item.source.name}</span>
                            <span class="text-suspense-aurora/70">{item.source.language}</span>
                            {#if item.published_at}
                                <span class="text-suspense-aurora/70">{resolveDateTime(item.published_at)}</span>
                            {/if}
                        </div>
                        <h3 class="font-noto-sans text-lg font-black uppercase italic leading-tight text-suspense-aurora">
                            {item.title}
                        </h3>
                        <p class="line-clamp-3 font-noto-sans text-sm text-suspense-aurora/80">
                            {item.excerpt}
                        </p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <Button size="sm" variant="secondary" shape="pill" on:click={() => createPost(item)}>Criar matéria</Button>
                            <a
                                class="inline-flex items-center rounded-full px-3 py-1 font-noto-sans text-sm font-extrabold uppercase italic text-orange-citric underline-offset-4 hover:underline"
                                href={item.url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Ler no site
                            </a>
                        </div>
                    </div>
                </article>
            {/each}
        </div>
    {:else}
        <EmptyState
            title="Nenhuma matéria encontrada"
            description="Os feeds externos disponíveis aparecerão aqui."
        />
    {/if}
    <Pagination pages={feedPosts} only={["newsFeedPosts", "selectedNewsFeedSource"]} pageName="feed_page" />
</Section>
