<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        Button,
        FormField,
        Preview,
        Section,
        TextInput,
        Wysiwyg,
    } from "@/lib/components/private";
    import { podcastPermissions } from "@/lib/utils";

    export let podcast = null;

    const can = podcastPermissions();

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

    function submit() {
        let url = podcast
            ? `/panel/podcast/${podcast.data.uuid}`
            : "/panel/podcast";

        $form.post(url, {
            preserveState: podcast,
            preserveScroll: false,
            forceFormData: true,
            onSuccess: () => {
                podcast ? null : $form.reset();
            },
        });
    }
</script>

<Section title={podcast ? "Atualizar Podcast" : "Adicionar Podcast"}>
    <form on:submit|preventDefault={submit}>
        <div class="grid grid-cols-1 xl:grid-cols-[18rem_1fr] gap-8 mb-8">
            <FormField for="image" label="Capa" labelVariant="editorial" spacing="sm" error={$form.errors.image}>
                <Preview
                    name="image"
                    src={$form.image}
                    oninput={(event) => ($form.image = event.target.files[0])}
                    required={!podcast}
                    error={$form.errors.image}
                />
            </FormField>
            <div class="flex flex-col gap-5">
                <div class="grid grid-cols-1 xl:grid-cols-[9rem_9rem_1fr] gap-5">
                    <FormField for="season" label="Temporada" labelVariant="editorial" spacing="none" error={$form.errors.season}>
                        <TextInput
                            id="season"
                            type="number"
                            name="season"
                            variant="editorial"
                            bind:value={$form.season}
                            error={$form.errors.season}
                            required
                        />
                    </FormField>
                    <FormField for="episode" label="Episódio" labelVariant="editorial" spacing="none" error={$form.errors.episode}>
                        <TextInput
                            id="episode"
                            type="number"
                            name="episode"
                            variant="editorial"
                            bind:value={$form.episode}
                            error={$form.errors.episode}
                            required
                        />
                    </FormField>
                    <FormField for="title" label="Título" labelVariant="editorial" spacing="none" error={$form.errors.title}>
                        <TextInput
                            id="title"
                            type="text"
                            name="title"
                            variant="editorial"
                            bind:value={$form.title}
                            error={$form.errors.title}
                            required
                        />
                    </FormField>
                </div>
                <FormField for="summary" label="Resumo" labelVariant="editorial" spacing="none" error={$form.errors.summary}>
                    <Wysiwyg
                        height="7.5rem"
                        name="summary"
                        bind:value={$form.summary}
                        error={$form.errors.summary}
                        required
                    />
                </FormField>
            </div>
        </div>
        <div class="flex flex-col px-0 xl:px-40 mb-8">
            <FormField for="description" label="Escreva" labelVariant="editorial" spacing="lg" error={$form.errors.description}>
                <Wysiwyg
                    height="25rem"
                    name="description"
                    bind:value={$form.description}
                    error={$form.errors.description}
                    required
                />
            </FormField>
            <FormField for="audio" label="Spotify embeded" labelVariant="editorial" spacing="none" error={$form.errors.audio}>
                <TextInput
                    id="audio"
                    type="url"
                    name="audio"
                    variant="editorial"
                    placeholder="https://open.spotify.com/embed/episode/...."
                    bind:value={$form.audio}
                    error={$form.errors.audio}
                    required
                />
            </FormField>
        </div>
        {#if can.create || can.update}
            <div class="flex justify-end">
                <Button
                    type="submit"
                    variant="accent"
                    shape="pill"
                    loading={$form.processing}
                >
                    {podcast ? "Atualizar podcast" : "Publicar podcast"}
                </Button>
            </div>
        {/if}
    </form>
</Section>
