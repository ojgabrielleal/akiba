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

    export let post = null;
    export let draft = null;

    const can = postPermissions();

    function normalizeTags(tags = []) {
        return [
            { uuid: null, name: null, ...tags[0] },
            { uuid: null, name: null, ...tags[1] },
        ];
    }

    function normalizeReferences(references = []) {
        return [
            { uuid: null, name: null, url: null, ...references[0] },
            { uuid: null, name: null, url: null, ...references[1] },
        ];
    }

    $: draftKey = draft ? `${draft.title ?? ""}|${draft.references?.[0]?.url ?? ""}` : "";

    $: form = useForm({
        _method: post ? "PATCH" : "POST",
        module: "post",
        status: post?.data.status ?? null,
        image: post?.data.image ?? null,
        title: post?.data.title ?? draft?.title ?? null,
        cover: post?.data.cover ?? null,
        content: post?.data.content ?? draft?.content ?? null,
        tags: normalizeTags(post?.data.tags),
        references: normalizeReferences(post?.data.references ?? draft?.references),
    });

    $: if (!post && draftKey && $form.title !== draft?.title) {
        $form.title = draft?.title ?? null;
        $form.content = draft?.content ?? null;
        $form.references = normalizeReferences(draft?.references);
    }

    function submit(event) {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.status = event.submitter.value;
        $form.post(url, {
            preserveState: false,
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
            <FormField for="title" label="Título" error={$form.errors.title} labelVariant="editorial" spacing="lg">
                <TextInput
                    id="title"
                    type="text"
                    name="title"
                    variant="editorial"
                    required={!post}
                    bind:value={$form.title}
                    error={$form.errors.title}
                />
            </FormField>
            <FormField for="cover" label="Capa" error={$form.errors.cover} labelVariant="editorial" spacing="lg" >
                <Preview
                    name="cover"
                    src={$form.cover}
                    onchange={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                    error={$form.errors.cover}
                />
            </FormField>
            <FormField for="content" label="Escreva" error={$form.errors.content} labelVariant="editorial" spacing="none" >
                <Wysiwyg
                    name="content"
                    required
                    bind:value={$form.content}
                    error={$form.errors.content}
                />
            </FormField>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
        <div class="block">
            <FormField for="image" label="Imagem em destaque" labelVariant="editorial" spacing="sm" error={$form.errors.image}>
                <Preview
                    name="image"
                    size="featured"
                    src={$form.image}
                    required={!post}
                    error={$form.errors.image}
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
                    <div class="text-center text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Tags
                    </div>
                    <FormField for="tag-0" label="Primeira Tag" labelVariant="metadata-indented" spacing="section" error={$form.errors["tags.0.name"]} >
                        <SelectInput
                            id="tag-0"
                            name="tags[0][name]"
                            variant="pill"
                            required={!post}
                            bind:value={$form.tags[0].name}
                            error={$form.errors["tags.0.name"]}
                        >
                            {#each Object.values(postTags) as item}
                                <option value={item.value}>
                                    {item.label}
                                </option>
                            {/each}
                        </SelectInput>
                    </FormField>
                    <FormField for="tag-1" label="Segunda Tag" error={$form.errors["tags.1.name"]} labelVariant="metadata-indented" spacing="none" >
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
                        Escolha até 2 tags para a sua matéria
                    </div>
                </div>
                <div>
                    <div class="text-center text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <FormField for="reference-0-name" label="Nome:" error={$form.errors["references.0.name"]} labelVariant="metadata-indented" spacing="none" class="flex-1">
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
                        <FormField for="reference-0-url" label="Link:" error={$form.errors["references.0.url"]} labelVariant="metadata" spacing="none" class="flex-1">
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
                        <FormField for="reference-1-name" label="Nome:" error={$form.errors["references.1.name"]} labelVariant="metadata-indented" spacing="none" class="flex-1">
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
                        <FormField for="reference-1-url" label="Link:" error={$form.errors["references.1.url"]} labelVariant="metadata" spacing="none" class="flex-1">
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
                        Preencha até duas fontes de pesquisa usadas para montar a matéria
                    </div>
                </div>
            </div>
            <PostActions
                post={post}
                status={$form.status}
                can={can}
                processing={$form.processing}
            />
        </div>
    </div>
</form>
