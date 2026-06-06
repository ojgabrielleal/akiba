<script>
    import { useForm, page } from "@inertiajs/svelte";
    import { Section, Preview, Wysiwyg } from "@/ui/components/private";
    import { podcastPermissions } from "@/utils";

    $: ({ podcast } = $page.props);

    let can = podcastPermissions();

    let form = useForm({
        _method: null,
        image: null,
        season: null,
        episode: null,
        title: null,
        summary: null,
        description: null,
        audio: null,
    });

    $: if (podcast) {
        $form._method = "PATCH";
        $form.image = podcast.data.image;
        $form.season = podcast.data.season;
        $form.episode = podcast.data.episode;
        $form.title = podcast.data.title;
        $form.summary = podcast.data.summary;
        $form.description = podcast.data.description;
        $form.audio = podcast.data.audio;
    }

    const submit = () => {
        let url = podcast
            ? `/panel/podcast/${podcast.data.uuid}`
            : "/panel/podcast";

        $form.post(url, {
            preserveState: podcast,
            preserveScroll: true,
            onSuccess: () => {
                podcast ? null : $form.reset();
            },
        });
    };
</script>

<Section title={podcast ? "Editar Podcast" : "Adicionar Podcast"}>
    <form class="mt-10" on:submit|preventDefault={submit}>
        <div class="grid grid-cols-1 xl:grid-cols-[20rem_1fr] items-center gap-8 mb-8">
            <div>
                <div class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Capa do podcast
                </div>
                <Preview
                    src={$form.image}
                    oninput={(event) => ($form.image = event.target.files[0])}
                    required={!podcast}
                />
            </div>
            <div class="flex flex-col gap-8">
                <div class="grid grid-cols-1 xl:grid-cols-[9rem_9rem_1fr] gap-8 lg:gap-5">
                    <div>
                        <label for="season" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Season
                        </label>
                        <input
                            id="season"
                            type="number"
                            name="season"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.season}
                            required
                        />
                    </div>
                    <div>
                        <label for="episode" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Episode
                        </label>
                        <input
                            id="episode"
                            type="number"
                            name="episode"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.episode}
                            required
                        />
                    </div>
                    <div>
                        <label for="title" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                            Título do episódio
                        </label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                            bind:value={$form.title}
                            required
                        />
                    </div>
                </div>
                <div>
                    <label for="summary" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                        Resumo do episódio
                    </label>
                    <Wysiwyg
                        height="13rem"
                        name="summary"
                        bind:value={$form.summary}
                        required
                    />
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="mb-8">
                <label for="description" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Escreva sobre o episódio
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
                    URL Embeded do Spotify do episódio
                </label>
                <input
                    id="audio"
                    type="url"
                    name="audio"
                    class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-md outline-none pl-4"
                    bind:value={$form.audio}
                    required
                />
            </div>
        </div>
        {#if can.create || can.update}
            <div class="flex flex-wrap gap-4 justify-center lg:flex-nowrap mt-10">
                <button
                    aria-label=""
                    type="submit"
                    class="cursor-pointer w-full lg:w-auto py-2 px-6 border-4 border-solid border-blue-skywave rounded-xl text-blue-skywave text-xl font-extrabold font-noto-sans italic uppercase"
                >
                    {podcast ? "Atualizar podcast" : "Publicar podcast"}
                </button>
            </div>
        {/if}
    </form>
</Section>
