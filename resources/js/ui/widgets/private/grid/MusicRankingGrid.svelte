<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Section, Preview } from "@/ui/components/private";
    import { musicPermissions } from "@/utils";

    $: ({ musicRanking } = $page.props);

    let can = musicPermissions();

    const submit = (event, uuid) => {
        const formData = new FormData();

        formData.append("_method", "PATCH");
        formData.append("image_ranking", event.target.files[0]);

        router.post(`/panel/radio/music-ranking/${uuid}`, formData, {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    const setRanking = () => {
        router.post("/panel/radio/music-ranking", {}, {
                preserveScroll: true,
            });
    };
</script>

{#if musicRanking}
    <Section {title}>
        <div class="flex flex-col gap-5">
            {#if musicRanking.data.length >= 3}
                {#each musicRanking.data as item, index}
                    <article class="flex flex-wrap lg:flex-nowrap items-center gap-5">
                        <div class="flex items-center gap-5">
                            {#if can.update}
                                <Preview
                                    name="image_ranking"
                                    size="thumb"
                                    src={item.ranking.image || "https://placehold.co/500x500?text=Rede+Akiba"}
                                    oninput={(event) => submit(event, item.uuid)}
                                />
                            {:else}
                                <img
                                    class="w-24 h-24 rounded-md"
                                    src={item.ranking.image || "https://placehold.co/500x500?text=Rede+Akiba"}
                                    alt={item.name}
                                />
                            {/if}
                            <strong class="text-suspense-aurora text-6xl font-noto-sans font-extrabold uppercase italic">
                                #{index + 1}
                            </strong>
                        </div>
                        <div class="text-suspense-aurora font-noto-sans uppercase">
                            {item.name} - {item.type} - {item.production} - {item.artist}
                        </div>
                    </article>
                {/each}
            {/if}
        </div>
        {#if musicRanking.data.length >= 3 && can.set}
            <div class="flex justify-end mt-5">
                <button type="button" class="cursor-pointer bg-blue-skywave px-4 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold uppercase italic disabled:opacity-50 disabled:pointer-events-none" on:click={() => setRanking()}>
                    Atualizar ranking
                </button>
            </div>
        {/if}
    </Section>
{/if}
