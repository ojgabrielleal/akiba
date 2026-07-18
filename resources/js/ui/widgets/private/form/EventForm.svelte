<script>
    import { useForm, page } from "@inertiajs/svelte";
    import {
        FormField,
        Preview,
        SelectInput,
        TextInput,
        Wysiwyg,
    } from "@/ui/components/private";
    import PostActions from "./actions/PostActions.svelte";
    import { postPermissions } from "@/utils";
    import { postTags } from "@/data";

    let { post } = $page.props;
    let can = postPermissions();

    const normalizeTags = (tags = []) => [
        { uuid: null, name: "event", ...tags[0] },
        { uuid: null, name: null, ...tags[1] },
    ];

    const normalizeReferences = (references = []) => [
        { uuid: null, name: null, url: null, ...references[0] },
        { uuid: null, name: null, url: null, ...references[1] },
    ];

    $: form = useForm({
        _method: post ? "PATCH" : "POST",
        module: "event",
        status: post?.data.status ?? null,
        image: post?.data.image ?? null,
        title: post?.data.title ?? null,
        cover: post?.data.cover ?? null,
        content: post?.data.content ?? null,
        metadata: {
            dates: post?.data.metadata?.dates ?? null,
            address: post?.data.metadata?.address ?? null,
        },
        tags: normalizeTags(post?.data.tags),
        references: normalizeReferences(post?.data.references),
    });

    const submit = (event) => {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.status = event.submitter.value;
        $form.post(url, {
            preserveState: false,
            forceFormData: true,
            onSuccess: () => {
                post ? null : $form.reset();
            },
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="lg:px-40">
        <div class="mb-8">
            <FormField for="title" label="Nome" labelVariant="editorial" spacing="lg" error={$form.errors.title}>
                <TextInput
                    id="title"
                    type="text"
                    name="title"
                    labelVariant="editorial"
                    required={!post}
                    bind:value={$form.title}
                    error={$form.errors.title}
                />
            </FormField>
            <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-5 mb-8">
                <FormField for="dates" label="Datas" labelVariant="editorial" spacing="lg" error={$form.errors["metadata.dates"]} class="lg:mb-0">
                    <TextInput
                        id="dates"
                        type="text"
                        name="metadata[dates]"
                        variant="editorial"
                        placeholder="Ex: 20 a 25 de Dezembro de 2024"
                        required={!post}
                        bind:value={$form.metadata.dates}
                        error={$form.errors["metadata.dates"]}
                    />
                </FormField>
                <FormField for="address" label="Locais" labelVariant="editorial" spacing="none" error={$form.errors["metadata.address"]}>
                    <TextInput
                        id="address"
                        type="text"
                        name="metadata[address]"
                        variant="editorial"
                        placeholder="Ex: Av. Paulista, 1000 - São Paulo/SP"
                        required={!post}
                        bind:value={$form.metadata.address}
                        error={$form.errors["metadata.address"]}
                    />
                </FormField>
            </div>
            <FormField for="cover" label="Capa" labelVariant="editorial" spacing="lg" error={$form.errors.cover}>
                <Preview
                    name="cover"
                    src={$form.cover}
                    oninput={(event)=>($form.cover = event.target.files[0])}
                    required={!post}
                />
            </FormField>
            <FormField for="content" label="Escreva" labelVariant="editorial" spacing="none" error={$form.errors.content}>
                <Wysiwyg
                    name="content"
                    required
                    bind:value={$form.content}
                />
            </FormField>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
        <div class="block">
            <div class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                Imagem em destaque
            </div>
            <Preview
                name="image"
                size="featured"
                src={$form.image}
                required={!post}
                oninput={(event) => ($form.image = event.target.files[0])}
            />
            <ul class="mt-4 ml-5 list-disc font-noto-sans font-light text-orange-citric">
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
                    <div class="text-center text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-5">
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
                    <FormField for="tag-1" label="Segunda Tag" labelVariant="metadata-indented" spacing="none" error={$form.errors["tags.1.name"]}>
                        <SelectInput
                            id="tag-1"
                            name="tags[1][name]"
                            variant="pill"
                            required={!post}
                            bind:value={$form.tags[1].name}
                            error={$form.errors["tags.1.name"]}
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
                    <div class="text-center text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <FormField for="reference-0-name" label="Nome:" labelVariant="metadata-indented" spacing="none" error={$form.errors["references.0.name"]} class="flex-1">
                            <TextInput
                                id="reference-0-name"
                                type="text"
                                name="references[0][name]"
                                variant="pillLeft"
                                required={!post}
                                bind:value={$form.references[0].name}
                                error={$form.errors["references.0.name"]}
                            />
                        </FormField>
                        <FormField for="reference-0-url" label="Link:" labelVariant="metadata" spacing="none" error={$form.errors["references.0.url"]} class="flex-1">
                            <TextInput
                                id="reference-0-url"
                                type="url"
                                name="references[0][url]"
                                variant="pillRight"
                                required={!post}
                                bind:value={$form.references[0].url}
                                error={$form.errors["references.0.url"]}
                            />
                        </FormField>
                    </div>
                    <div class="w-full flex">
                        <FormField for="reference-1-name" label="Nome:" labelVariant="metadata-indented" spacing="none" error={$form.errors["references.1.name"]} class="flex-1">
                            <TextInput
                                id="reference-1-name"
                                type="text"
                                name="references[1][name]"
                                variant="pillLeft"
                                required={!post}
                                bind:value={$form.references[1].name}
                                error={$form.errors["references.1.name"]}
                            />
                        </FormField>
                        <FormField for="reference-1-url" label="Link:" labelVariant="metadata" spacing="none" error={$form.errors["references.1.url"]} class="flex-1">
                            <TextInput
                                id="reference-1-url"
                                type="url"
                                name="references[1][url]"
                                variant="pillRight"
                                required={!post}
                                bind:value={$form.references[1].url}
                                error={$form.errors["references.1.url"]}
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
                status={$form.status}
                can={can}
            />
        </div>
    </div>
</form>
