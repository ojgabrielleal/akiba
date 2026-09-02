<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        FormField,
        Preview,
        SelectInput,
        TextInput,
        Wysiwyg,
    } from "@/lib/components/private";
    import { PostActions } from "@/lib/components/private";
    import { postPermissions } from "@/lib/utils";
    import { postTags } from "@/lib/constants";
    import { errorFor, normalizeErrors } from "./postFormErrors.js";

    export let post = null;

    const can = postPermissions();

    function normalizeTags(tags = []) {
        return [
            { uuid: null, name: "event", ...tags[0] },
            { uuid: null, name: null, ...tags[1] },
        ];
    }

    function normalizeReferences(references = []) {
        return [
            { uuid: null, name: null, url: null, ...references[0] },
            { uuid: null, name: null, url: null, ...references[1] },
        ];
    }

    const form = useForm({
        _method: post ? "PATCH" : "POST",
        module: "event",
        status: post?.data.status ?? null,
        image: post?.data.image ?? null,
        title: post?.data.title ?? null,
        cover: post?.data.cover ?? null,
        content: post?.data.content ?? null,
        metadata: {
            dates: post?.data.metadata?.dates ?? null,
            event_date: post?.data.metadata?.event_date ?? null,
            address: post?.data.metadata?.address ?? null,
        },
        tags: normalizeTags(post?.data.tags),
        references: normalizeReferences(post?.data.references),
    });

    $: errors = {
        ...normalizeErrors($form.errors),
    };
    $: titleError = errorFor(errors, ["title"]);
    $: eventDateError = errorFor(errors, ["metadata.event_date", "metadata[event_date]", "metadata"]);
    $: datesError = errorFor(errors, ["metadata.dates", "metadata[dates]", "metadata"]);
    $: addressError = errorFor(errors, ["metadata.address", "metadata[address]", "metadata"]);
    $: coverError = errorFor(errors, ["cover"]);
    $: contentError = errorFor(errors, ["content"]);
    $: imageError = errorFor(errors, ["image"]);
    $: secondTagError = errorFor(errors, ["tags.1.name", "tags[1][name]", "tags"]);
    $: firstReferenceNameError = errorFor(errors, ["references.0.name", "references[0][name]", "references"]);
    $: firstReferenceUrlError = errorFor(errors, ["references.0.url", "references[0][url]", "references"]);
    $: secondReferenceNameError = errorFor(errors, ["references.1.name", "references[1][name]", "references"]);
    $: secondReferenceUrlError = errorFor(errors, ["references.1.url", "references[1][url]", "references"]);

    function submit(event) {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";
        const status = event.submitter.value;

        $form.transform((data) => ({
            ...data,
            status,
        })).post(url, {
            preserveState: true,
            forceFormData: true,
            onSuccess: () => {
                post ? null : $form.reset();
            },
        });
    }
</script>

<form novalidate on:submit|preventDefault={submit}>
    <div class="lg:px-40">
        <div class="mb-8">
            <FormField for="title" label="Nome" labelVariant="editorial" spacing="lg" error={titleError}>
                <TextInput
                    id="title"
                    type="text"
                    name="title"
                    variant="editorial"
                    required
                    bind:value={$form.title}
                    error={titleError}
                />
            </FormField>
            <div class="mb-8 grid grid-cols-1 gap-5 lg:grid-cols-[0.7fr_1.15fr_1.15fr]">
                <FormField for="event_date" label="Dia do evento" labelVariant="editorial" spacing="lg" error={eventDateError} class="lg:mb-0">
                    <TextInput
                        id="event_date"
                        type="date"
                        name="metadata[event_date]"
                        variant="editorial"
                        required={!post}
                        bind:value={$form.metadata.event_date}
                        error={eventDateError}
                    />
                </FormField>
                <FormField for="dates" label="Datas" labelVariant="editorial" spacing="lg" error={datesError} class="lg:mb-0">
                    <TextInput
                        id="dates"
                        type="text"
                        name="metadata[dates]"
                        variant="editorial"
                        placeholder="Ex: 20 a 25 de Dezembro de 2024"
                        required={!post}
                        bind:value={$form.metadata.dates}
                        error={datesError}
                    />
                </FormField>
                <FormField for="address" label="Locais" labelVariant="editorial" spacing="lg" error={addressError} class="lg:mb-0">
                    <TextInput
                        id="address"
                        type="text"
                        name="metadata[address]"
                        variant="editorial"
                        placeholder="Ex: Av. Paulista, 1000 - São Paulo/SP"
                        required={!post}
                        bind:value={$form.metadata.address}
                        error={addressError}
                    />
                </FormField>
            </div>
            <FormField for="cover" label="Capa" labelVariant="editorial" spacing="lg" error={coverError}>
                <Preview
                    name="cover"
                    src={$form.cover}
                    onchange={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                    error={coverError}
                />
            </FormField>
            <FormField for="content" label="Escreva" labelVariant="editorial" spacing="none" error={contentError}>
                <Wysiwyg
                    name="content"
                    required
                    bind:value={$form.content}
                    error={contentError}
                />
            </FormField>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
        <div class="block">
            <FormField for="image" label="Imagem em destaque" labelVariant="editorial" spacing="sm" error={imageError}>
                <Preview
                    name="image"
                    size="featured"
                    src={$form.image}
                    required={!post}
                    error={imageError}
                    onchange={(event) => ($form.image = event.target.files[0])}
                />
            </FormField>
            <ul class="mt-4 ml-5 list-disc font-noto-sans font-light text-orange-morning">
                <li>
                    <strong>Tamanho:</strong> 708x827
                </li>
                <li>
                    <strong>Fundo:</strong> Transparente
                </li>
            </ul>
        </div>
        <div class="block">
            <div class="grid grid-cols-1 lg:grid-cols-[0.4fr_1fr] gap-5 mb-15">
                <div>
                    <div class="text-center text-orange-citric font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Tags
                    </div>
                    <FormField for="tag-0" label="Primeira Tag" labelVariant="metadata-indented" spacing="section">
                        <SelectInput
                            id="tag-0"
                            name="tags[0][name]"
                            variant="pill"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                            bind:value={$form.tags[0].name}
                        >
                            <option value="event">
                                Evento
                            </option>
                        </SelectInput>
                    </FormField>
                    <FormField for="tag-1" label="Segunda Tag" labelVariant="metadata-indented" spacing="none" error={secondTagError}>
                        <SelectInput
                            id="tag-1"
                            name="tags[1][name]"
                            variant="pill"
                            required={!post}
                            bind:value={$form.tags[1].name}
                            error={secondTagError}
                        >
                            {#each Object.values(postTags) as item}
                                <option value={item.value}>
                                    {item.label}
                                </option>
                            {/each}
                        </SelectInput>
                    </FormField>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Escolha 1 tag para o evento
                    </div>
                </div>
                <div>
                    <div class="text-center text-orange-citric font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <FormField for="reference-0-name" label="Nome:" labelVariant="metadata-indented" spacing="none" error={firstReferenceNameError} class="flex-1">
                            <TextInput
                                id="reference-0-name"
                                type="text"
                                name="references[0][name]"
                                variant="pillLeft"
                                bind:value={$form.references[0].name}
                                error={firstReferenceNameError}
                            />
                        </FormField>
                        <FormField for="reference-0-url" label="Link:" labelVariant="metadata" spacing="none" error={firstReferenceUrlError} class="flex-1">
                            <TextInput
                                id="reference-0-url"
                                type="url"
                                name="references[0][url]"
                                variant="pillRight"
                                bind:value={$form.references[0].url}
                                error={firstReferenceUrlError}
                            />
                        </FormField>
                    </div>
                    <div class="w-full flex">
                        <FormField for="reference-1-name" label="Nome:" labelVariant="metadata-indented" spacing="none" error={secondReferenceNameError} class="flex-1">
                            <TextInput
                                id="reference-1-name"
                                type="text"
                                name="references[1][name]"
                                variant="pillLeft"
                                bind:value={$form.references[1].name}
                                error={secondReferenceNameError}
                            />
                        </FormField>
                        <FormField for="reference-1-url" label="Link:" labelVariant="metadata" spacing="none" error={secondReferenceUrlError} class="flex-1">
                            <TextInput
                                id="reference-1-url"
                                type="url"
                                name="references[1][url]"
                                variant="pillRight"
                                bind:value={$form.references[1].url}
                                error={secondReferenceUrlError}
                            />
                        </FormField>
                    </div>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Preencha até duas fontes de pesquisa usadas para montar o evento
                    </div>
                </div>
            </div>
            <PostActions
                post={post}
                status={post?.data.status ?? $form.status}
                can={can}
                processing={$form.processing}
            />
        </div>
    </div>
</form>
