<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        FormField,
        Preview,
        SelectInput,
        TextInput,
        Tooltip,
        Wysiwyg,
    } from "@/lib/components/private";
    import { PostActions } from "@/lib/components/private";
    import { postPermissions } from "@/lib/utils";
    import { errorFor, normalizeErrors } from "./postFormErrors.js";

    export let post = null;

    const can = postPermissions();

    function normalizeTags(tags = []) {
        return [
            { uuid: tags[0]?.uuid ?? null, name: "reviews" },
            { uuid: tags[1]?.uuid ?? null, name: "anime" },
        ];
    }

    function normalizeReferences(references = []) {
        return [
            { name: null, url: null, ...references[0] },
            { name: null, url: null, ...references[1] },
        ];
    }

    function normalizeReview(review = {}) {
        return {
            uuid: null,
            content: null,
            status: null,
            author: null,
            ...review,
        };
    }


    function reviewerName(opinion = {}) {
        return opinion.author?.nickname ?? opinion.author?.name ?? "Review";
    }

    function reviewerInitials(opinion = {}) {
        return reviewerName(opinion)
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0])
            .join("")
            .toUpperCase();
    }

    const form = useForm({
        _method: post ? "PATCH" : "POST",
        module: "review",
        image: post?.data.image ?? null,
        title: post?.data.title ?? null,
        cover: post?.data.cover ?? null,
        studio: post?.data.metadata?.studio ?? null,
        metadata: {
            date_of_release: post?.data.metadata?.date_of_release ?? post?.data.metadata?.year_of_release ?? null,
            sinopse: post?.data.metadata?.sinopse ?? null,
        },
        review: normalizeReview(post?.data.review),
        tags: normalizeTags(post?.data.tags),
        references: normalizeReferences(post?.data.references),
    });

    $: errors = {
        ...normalizeErrors($form.errors),
    };
    $: titleError = errorFor(errors, ["title"]);
    $: releaseDateError = errorFor(errors, ["metadata.date_of_release", "metadata[date_of_release]", "metadata.year_of_release", "metadata[year_of_release]", "metadata"]);
    $: studioError = errorFor(errors, ["studio"]);
    $: sinopseError = errorFor(errors, ["metadata.sinopse", "metadata[sinopse]", "metadata"]);
    $: coverError = errorFor(errors, ["cover"]);
    $: reviewContentError = errorFor(errors, ["review.content", "review[content]", "review"]);
    $: imageError = errorFor(errors, ["image"]);
    $: persistedSelectedReview = post?.data.reviews?.find((opinion) => {
        if ($form.review?.uuid) {
            return opinion.uuid === $form.review.uuid;
        }

        return opinion.author?.uuid === $form.review?.author?.uuid;
    });
    $: selectedReviewStatus = persistedSelectedReview?.status ?? $form.review?.status;

    function submit(event) {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";
        const status = event.submitter.value;

        $form.transform((data) => {
            const { tags, references, ...payload } = data;

            return {
                ...payload,
                review: {
                    ...payload.review,
                    status,
                },
            };
        }).post(url, {
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
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_12rem_15rem] lg:gap-5">
                <FormField for="title" label="Nome" labelVariant="editorial" spacing="lg" error={titleError} class="lg:mb-0">
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
                <FormField for="date_of_release" label="Lançamento" labelVariant="editorial" spacing="lg" error={releaseDateError}>
                    <TextInput
                        id="date_of_release"
                        type="date"
                        name="metadata[date_of_release]"
                        variant="editorial"
                        required={!post}
                        bind:value={$form.metadata.date_of_release}
                        error={releaseDateError}
                    />
                </FormField>
                <FormField for="studio" label="Estúdio" labelVariant="editorial" spacing="lg" error={studioError}>
                    <TextInput
                        id="studio"
                        type="text"
                        name="studio"
                        variant="editorial"
                        bind:value={$form.studio}
                        error={studioError}
                    />
                </FormField>
            </div>
            <FormField for="sinopse" label="Sinopse" labelVariant="editorial" spacing="lg" error={sinopseError}>
                <Wysiwyg
                    id="sinopse"
                    height="13rem"
                    name="metadata[sinopse]"
                    required={!post}
                    bind:value={$form.metadata.sinopse}
                    error={sinopseError}
                />
            </FormField>
            <FormField for="cover" label="Capa" labelVariant="editorial" spacing="lg" error={coverError}>
                <Preview
                    name="cover"
                    src={$form.cover}
                    fit="cover"
                    minWidth={1200}
                    minHeight={400}
                    minDimensionsMessage="A capa precisa ter pelo menos 1200x400 pixels para funcionar bem como fundo."
                    onchange={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                    error={coverError}
                />
                <p class="mt-2 font-noto-sans text-sm text-orange-morning">
                    Tamanho mínimo: 1200x400
                </p>
            </FormField>
            <FormField for="content" label="Escreva" labelVariant="editorial" spacing="none" error={reviewContentError}>
                {#if post?.data.reviews?.length}
                    <div class="mb-3 flex flex-wrap gap-2.5">
                        {#each post.data.reviews as opinion (opinion.uuid ?? opinion.author.uuid)}
                            {@const active = $form.review.uuid === opinion.uuid}
                            <Tooltip>
                                <button
                                    type="button"
                                    aria-label={`Review de ${reviewerName(opinion)}`}
                                    class={[
                                        "group/review-tab flex min-h-8 cursor-pointer items-center gap-2 rounded-full border px-3.5 py-1 font-noto-sans text-xs font-black uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                        {"border-neutral-gray bg-neutral-gray text-blue-marinho": opinion.status === 'not_created'},
                                        {"border-blue-ocean bg-blue-ocean text-suspense-aurora": opinion.status === 'published'},
                                        {"border-green-forest bg-green-forest text-blue-marinho": opinion.status === 'draft'},
                                        {"border-orange-amber bg-orange-amber text-blue-marinho": opinion.status === 'revision'},
                                    ]}
                                    on:click={() => $form.review = normalizeReview(opinion)}
                                >
                                    <span
                                        class={[
                                            "flex size-5 shrink-0 items-center justify-center rounded-full text-[0.62rem] font-black not-italic transition duration-300",
                                            active ? "bg-blue-night text-orange-citric" : "bg-blue-night/45 text-suspense-aurora",
                                        ]}
                                    >
                                        {reviewerInitials(opinion)}
                                    </span>
                                    <span class={["min-w-0 max-w-40 truncate", active && "text-suspense-aurora"]}>{reviewerName(opinion)}</span>
                                    {#if active}
                                        <span class="ml-0.5 size-1.5 shrink-0 rounded-full bg-suspense-aurora" aria-hidden="true"></span>
                                    {/if}
                                </button>
                                <div slot="content">
                                    Review de {reviewerName(opinion)}
                                </div>
                            </Tooltip>
                        {/each}
                    </div>
                {/if}
                <Wysiwyg
                    id="review-content"
                    name="review[content]"
                    required
                    bind:value={$form.review.content}
                    error={reviewContentError}
                />
            </FormField>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
        <div class="mb-3">
            <FormField for="image" label="Imagem em destaque" labelVariant="editorial" spacing="sm" error={imageError}>
                <Preview
                    name="image"
                    src={$form.image}
                    onchange={(event) => ($form.image = event.target.files[0])}
                    required={!post}
                    error={imageError}
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
                            variant="pill"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                            value={null}
                        >
                            <option value={null}></option>
                        </SelectInput>
                    </FormField>
                    <FormField for="tag-1" label="Segunda Tag" labelVariant="metadata-indented" spacing="none">
                        <SelectInput
                            id="tag-1"
                            variant="pill"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                            value={null}
                        >
                            <option value={null}></option>
                        </SelectInput>
                    </FormField>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Tags escolhidas automaticamente para reviews
                    </div>
                </div>
                <div>
                    <div class="text-center text-orange-citric font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <FormField for="reference-0-name" label="Nome:" labelVariant="metadata-indented" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-0-name"
                                type="text"
                                variant="pillLeft"
                                class="disabled:cursor-not-allowed disabled:opacity-50"
                                disabled
                                bind:value={$form.references[0].name}
                            />
                        </FormField>
                        <FormField for="reference-0-url" label="Link:" labelVariant="metadata" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-0-url"
                                type="url"
                                variant="pillRight"
                                class="disabled:cursor-not-allowed disabled:opacity-50"
                                disabled
                                bind:value={$form.references[0].url}
                            />
                        </FormField>
                    </div>
                    <div class="w-full flex">
                        <FormField for="reference-1-name" label="Nome:" labelVariant="metadata-indented" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-1-name"
                                type="text"
                                variant="pillLeft"
                                class="disabled:cursor-not-allowed disabled:opacity-50"
                                disabled
                                bind:value={$form.references[1].name}
                            />
                        </FormField>
                        <FormField for="reference-1-url" label="Link:" labelVariant="metadata" spacing="none" class="flex-1">
                            <TextInput
                                id="reference-1-url"
                                type="url"
                                variant="pillRight"
                                class="disabled:cursor-not-allowed disabled:opacity-50"
                                disabled
                                bind:value={$form.references[1].url}
                            />
                        </FormField>
                    </div>
                </div>
            </div>
            <PostActions
                status={selectedReviewStatus}
                can={can}
                processing={$form.processing}
            />
        </div>
    </div>
</form>
