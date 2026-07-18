<script>
    import { page, useForm } from "@inertiajs/svelte";
    import {
        FormField,
        Preview,
        SelectInput,
        TextInput,
        Tooltip,
        Wysiwyg,
    } from "@/ui/components/private";
    import PostActions from "./actions/PostActions.svelte";
    import { postPermissions } from "@/utils";

    let { post } = $page.props;
    let can = postPermissions();

    const normalizeTags = (tags = []) => [
        { uuid: null, name: "reviews", ...tags[0] },
        { uuid: null, name: "anime", ...tags[1] },
    ];

    const normalizeReferences = (references = []) => [
        { uuid: null, name: null, url: null, ...references[0] },
        { uuid: null, name: null, url: null, ...references[1] },
    ];

    $: form = useForm({
        _method: post ? "PATCH" : "POST",
        module: "review",
        image: post?.data.image ?? null,
        title: post?.data.title ?? null,
        cover: post?.data.cover ?? null,
        metadata: {
            year_of_release: post?.data.metadata?.year_of_release ?? null,
            sinopse: post?.data.metadata?.sinopse ?? null,
        },
        review: post?.data.review ?? { uuid: null, content: null, status: null, author: null },
        tags: normalizeTags(post?.data.tags),
        references: normalizeReferences(post?.data.references),
    });

    const submit = (event) => {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.review.status = event.submitter.value;
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
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_13rem] lg:gap-5">
                <FormField for="title" label="Nome" labelVariant="editorial" spacing="lg" error={$form.errors.title} class="lg:mb-0">
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
                <FormField for="year_of_release" label="Ano de lançamento" labelVariant="editorial" spacing="lg" error={$form.errors["metadata.year_of_release"]}>
                    <TextInput
                        id="year_of_release"
                        type="number"
                        name="metadata[year_of_release]"
                        variant="editorial"
                        required={!post}
                        bind:value={$form.metadata.year_of_release}
                        error={$form.errors["metadata.year_of_release"]}
                    />
                </FormField>
            </div>
            <FormField for="sinopse" label="Sinopse" labelVariant="editorial" spacing="lg" error={$form.errors["metadata.sinopse"]}>
                <Wysiwyg
                    height="13rem"
                    name="sinopse"   
                    required={!post}
                    bind:value={$form.metadata.sinopse}
                />
            </FormField>
            <FormField for="cover" label="Capa" labelVariant="editorial" spacing="lg" error={$form.errors.cover}>
                <Preview
                    name="cover"
                    src={$form.cover}
                    oninput={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                />
            </FormField>
            <FormField for="content" label="Escreva" labelVariant="editorial" spacing="none" error={$form.errors["review.content"]}>
                {#if post?.data.reviews?.length}
                    <div class="mb-3 flex flex-wrap gap-2">
                        {#each post.data.reviews as opinion}
                            <Tooltip>
                                <button 
                                    type="button"
                                    aria-label={`Review de ${opinion.author.nickname}`}
                                    class={["py-1 px-4 rounded-md font-noto-sans font-extrabold italic uppercase cursor-pointer",
                                        {"bg-neutral-gray text-blue-marinho": opinion.status === 'not_created'},
                                        {"bg-blue-ocean text-suspense-aurora": opinion.status === 'published'},
                                        {"bg-green-forest text-blue-marinho": opinion.status === 'draft'},
                                        {"bg-orange-citric text-blue-marinho": opinion.status === 'revision'},
                                        {"text-suspense-aurora": $form.review.uuid === opinion.uuid},
                                    ]}
                                    on:click={() => $form.review = normalizeReview(opinion)}
                                >
                                    {opinion.author.nickname}
                                </button>
                                <div slot="content">
                                    Review de {opinion.author.nickname}
                                </div>
                            </Tooltip>
                        {/each}
                    </div>
                {/if}
                <Wysiwyg
                    name="content"
                    required
                    bind:value={$form.review.content}
                />
            </FormField>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
        <div class="mb-3">
            <div class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-2">
                Imagem em destaque
            </div>
            <Preview
                name="image"
                src={$form.image}
                oninput={(event) => ($form.image = event.target.files[0])}
                required={!post}
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
                            <option value="reviews">
                                Reviews
                            </option>
                        </SelectInput>
                    </FormField>
                    <FormField for="tag-1" label="Segunda Tag" labelVariant="metadata-indented" spacing="none">
                        <SelectInput
                            id="tag-1"
                            name="tags[1][name]"
                            variant="pill"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                            bind:value={$form.tags[1].name}
                        >
                            <option value="anime">
                                Anime
                            </option>
                        </SelectInput>
                    </FormField>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Tags escolhidas automaticamente para reviews
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
                        Preencha até duas fontes de pesquisa usadas para obter informações sobre o anime
                    </div>
                </div>
            </div>
            <PostActions
                status={$form.review?.status}
                can={can}
            />
        </div>
    </div>
</form>
