<script>
    import { useForm, page } from "@inertiajs/svelte";
    import { Section, Preview, Wysiwyg } from "@/ui/components/private";
    import { podcastPermissions } from "@/utils";

    $: ({ podcast } = $page.props);
    let can = podcastPermissions();

    $: form = useForm({
        _method: podcast ? 'PATCH' : 'POST',
        image: podcast?.data.image ?? null,
        season: podcast?.data.season ?? null,
        episode: podcast?.data.episode ?? null,
        title: podcast?.data.title ?? null,
        summary: podcast?.data.summary ?? null,
        description: podcast?.data.description ?? null,
        audio: podcast?.data.audio ?? null,
    });

    const submit = () => {
        let url = podcast
            ? `/panel/podcast/${podcast.data.uuid}`
            : "/panel/podcast";

        $form.post(url, {
            preserveState: podcast,
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                podcast ? null : $form.reset();
            },
        });
    };
</script>

<Section title={podcast ? "Atualizar Podcast" : "Adicionar Podcast"}>
    <form on:submit|preventDefault={submit}>
        <div class="grid grid-cols-1 xl:grid-cols-[18rem_1fr] gap-8 mb-8">
            <div>
                <div class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Capa
                </div>
                <Preview
                    src={$form.image}
                    oninput={(event) => ($form.image = event.target.files[0])}
                    required={!podcast}
                />
            </div>
            <div class="flex flex-col gap-5">
                <div class="grid grid-cols-1 xl:grid-cols-[9rem_9rem_1fr] gap-5">
                    <div>
                        <label for="season" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Temporada
                        </label>
                        <input
                            id="season"
                            type="number"
                            name="season"
                            class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4"
                            bind:value={$form.season}
                            required
                        />
                    </div>
                    <div>
                        <label for="episode" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Episódio
                        </label>
                        <input
                            id="episode"
                            type="number"
                            name="episode"
                            class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4"
                            bind:value={$form.episode}
                            required
                        />
                    </div>
                    <div>
                        <label for="title" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Título
                        </label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4"
                            bind:value={$form.title}
                            required
                        />
                    </div>
                </div>
                <div>
                    <label for="summary" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                        Resumo
                    </label>
                    <Wysiwyg
                        height="7.5rem"
                        name="summary"
                        bind:value={$form.summary}
                        required
                    />
                </div>
            </div>
        </div>
        <div class="flex flex-col px-0 xl:px-40 mb-8">
            <div class="mb-8">
                <label for="description" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Escreva
                </label>
                <Wysiwyg
                    height="25rem"
                    name="description"
                    bind:value={$form.description}
                    required
                />
            </div>
            <div>
                <label for="audio" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Spotify embeded
                </label>
                <input
                    id="audio"
                    type="url"
                    name="audio"
                    class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4"
                    placeholder="https://open.spotify.com/embed/episode/...."
                    bind:value={$form.audio}
                    required
                />
            </div>
        </div>
        {#if can.create || can.update}
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho text-md py-2 px-6 rounded-full bg-orange-citric"
                >
                    {podcast ? "Atualizar podcast" : "Publicar podcast"}
                </button>
            </div>
        {/if}
    </form>
</Section>
