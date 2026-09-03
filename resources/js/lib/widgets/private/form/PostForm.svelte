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

    let appliedDraftKey = "";

    $: draftKey = draft ? `${draft.title ?? ""}|${draft.references?.[0]?.url ?? ""}` : "";

    const form = useForm({
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

    $: errors = {
        ...normalizeErrors($form.errors),
    };
    $: titleError = errorFor(errors, ["title"]);
    $: coverError = errorFor(errors, ["cover"]);
    $: contentError = errorFor(errors, ["content"]);
    $: imageError = errorFor(errors, ["image"]);
    $: firstTagError = errorFor(errors, ["tags.0.name", "tags[0][name]", "tags"]);
    $: secondTagError = errorFor(errors, ["tags.1.name", "tags[1][name]", "tags"]);
    $: firstReferenceNameError = errorFor(errors, ["references.0.name", "references[0][name]", "references"]);
    $: firstReferenceUrlError = errorFor(errors, ["references.0.url", "references[0][url]", "references"]);
    $: secondReferenceNameError = errorFor(errors, ["references.1.name", "references[1][name]", "references"]);
    $: secondReferenceUrlError = errorFor(errors, ["references.1.url", "references[1][url]", "references"]);

    $: if (!post && draftKey && draftKey !== appliedDraftKey) {
        $form.title = draft?.title ?? null;
        $form.content = draft?.content ?? null;
        $form.references = normalizeReferences(draft?.references);
        appliedDraftKey = draftKey;
    }

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
            <FormField for="title" label="Título" error={titleError} labelVariant="editorial" spacing="lg">
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
            <FormField for="cover" label="Capa" error={coverError} labelVariant="editorial" spacing="lg" >
                <Preview
                    name="cover"
                    src={$form.cover}
                    fit="cover"
                    onchange={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                    error={coverError}
                />
            </FormField>
            <FormField for="content" label="Escreva" error={contentError} labelVariant="editorial" spacing="none" >
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
                    <FormField for="tag-0" label="Primeira Tag" labelVariant="metadata-indented" spacing="section" error={firstTagError} >
                        <SelectInput
                            id="tag-0"
                            name="tags[0][name]"
                            variant="pill"
                            required={!post}
                            bind:value={$form.tags[0].name}
                            error={firstTagError}
                        >
                            {#each Object.values(postTags) as item}
                                <option value={item.value}>
                                    {item.label}
                                </option>
                            {/each}
                        </SelectInput>
                    </FormField>
                    <FormField for="tag-1" label="Segunda Tag" error={secondTagError} labelVariant="metadata-indented" spacing="none" >
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
                        Escolha até 2 tags para a sua matéria
                    </div>
                </div>
                <div>
                    <div class="text-center text-orange-citric font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <FormField for="reference-0-name" label="Nome:" error={firstReferenceNameError} labelVariant="metadata-indented" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-0-name"
                                type="text"
                                name="references[0][name]"
                                variant="pillLeft"
                                bind:value={$form.references[0].name}
                                error={firstReferenceNameError}
                            />
                        </FormField>
                        <FormField for="reference-0-url" label="Link:" error={firstReferenceUrlError} labelVariant="metadata" spacing="none" class="flex-1">
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
                        <FormField for="reference-1-name" label="Nome:" error={secondReferenceNameError} labelVariant="metadata-indented" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-1-name"
                                type="text"
                                name="references[1][name]"
                                variant="pillLeft"
                                bind:value={$form.references[1].name}
                                error={secondReferenceNameError}
                            />
                        </FormField>
                        <FormField for="reference-1-url" label="Link:" error={secondReferenceUrlError} labelVariant="metadata" spacing="none" class="flex-1">
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
                        Preencha até duas fontes de pesquisa usadas para montar a matéria
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
