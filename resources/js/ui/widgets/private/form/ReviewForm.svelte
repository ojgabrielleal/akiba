<script>
    import { page, useForm } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { PostActions, Preview, Wysiwyg, Tooltip } from "@/ui/components/private";
    import { postPermissions } from "@/utils";

    $: ({ post } = $page.props);
    
    let can = postPermissions();

    const normalizeTags = (tags = []) => [
        { uuid: null, name: "reviews", ...tags[0] },
        { uuid: null, name: "anime", ...tags[1] },
    ];

    const normalizeReferences = (references = []) => [
        { uuid: null, name: null, url: null, ...references[0] },
        { uuid: null, name: null, url: null, ...references[1] },
    ];

    let form = useForm({
        _method: "POST",
        module: "review",
        image: null,
        title: null,
        sinopse: null,
        cover: null,
        year_of_release: null,
        review: { uuid: null, content: null, status: null, author: null },
        tags: normalizeTags(),
        references: normalizeReferences(),
    });

    onMount(() => {
        if(post){
            $form._method = "PATCH";
            $form.image = post.data.image;
            $form.title = post.data.title;
            $form.sinopse = post.data.sinopse;
            $form.cover = post.data.cover;
            $form.year_of_release = post.data.year_of_release;
            $form.review = post.data.review;
            $form.tags = normalizeTags(post.data.tags);
            $form.references = normalizeReferences(post.data.references);
        }
    });

    const submit = (event) => {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.review.status = event.submitter.value;
        $form.post(url, {
            preserveState: false,
            onSuccess: () => {
                post ? null : $form.reset();
            },
        });
    };
</script>

<form class="container-page mb-20" on:submit|preventDefault={submit}>
    <div class="lg:px-40">
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_13rem] lg:gap-5">
                <div class="mb-8 lg:mb-0">
                    <label for="title" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                        Nome do anime
                    </label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4 disabled:opacity-50 disabled:cursor-not-allowed"
                        required={!post}
                        bind:value={$form.title}
                    />
                </div>
                <div class="mb-8">
                    <label for="year_of_release" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                        Ano de lançamento
                    </label>
                    <input
                        id="year_of_release"
                        type="number"
                        name="year_of_release"
                        class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4 disabled:opacity-50 disabled:cursor-not-allowed"
                        required={!post}
                        bind:value={$form.year_of_release}
                    />
                </div>
            </div>
            <div class="mb-8">
                <label for="sinopse" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Sinopse do anime
                </label>
                <Wysiwyg
                    height="13rem"
                    name="sinopse"   
                    required={!post}
                    bind:value={$form.sinopse}
                />
            </div>
            <div class="mb-8">
                <label for="cover" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Capa do anime
                </label>
                <Preview
                    name="cover"
                    src={$form.cover}
                    oninput={(event) => ($form.cover = event.target.files[0])}
                    required={!post}
                />
            </div>
            <div>
                <label for="content" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Escreva sobre o anime
                </label>
                {#if post?.data.opinions?.length}
                    <div class="mb-3 flex flex-wrap gap-2">
                        {#each post.data.opinions as opinion}
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
            </div>
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
                    <div class="mb-6">
                        <label for="tags" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                            Primeira Tag
                        </label>
                        <select
                            id="tags"
                            name="tags"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-full pl-4 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                            bind:value={$form.tags[0].name}
                        >
                            <option value="reviews">
                                Reviews
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="tags" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                            Segunda Tag
                        </label>
                        <select
                            id="tags"
                            name="tags"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-full pl-4 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                            bind:value={$form.tags[1].name}
                        >
                            <option value="anime">
                                Anime
                            </option>
                        </select>
                    </div>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Tags escolhidas automaticamente para reviews
                    </div>
                </div>
                <div>
                    <div class="text-center text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans mb-5">
                        Fontes
                    </div>
                    <div class="w-full flex mb-6">
                        <div class="flex-1">
                            <label for="references" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                                Nome:
                            </label>
                            <input
                                id="references"
                                type="text"
                                name="references"
                                class="w-full h-12 bg-suspense-aurora border-r border-blue-marinho font-noto-sans rounded-l-full pl-4"
                                required={!post}
                                bind:value={$form.references[0].name}
                            />
                        </div>
                        <div class="flex-1">
                            <label for="references" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1">
                                Link:
                            </label>
                            <input
                                id="references"
                                type="url"
                                name="references"
                                class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-r-full pl-4"
                                required={!post}
                                bind:value={$form.references[0].url}
                            />
                        </div>
                    </div>
                    <div class="w-full flex">
                        <div class="flex-1">
                            <label for="references" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                                Nome:
                            </label>
                            <input
                                id="references"
                                type="text"
                                name="references"
                                class="w-full h-12 bg-suspense-aurora border-r border-blue-marinho font-noto-sans rounded-l-full pl-4"
                                required={!post}
                                bind:value={$form.references[1].name}
                            />
                        </div>
                        <div class="flex-1">
                            <label for="references" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1">
                                Link:
                            </label>
                            <input
                                id="references"
                                type="url"
                                name="references"
                                class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-r-full pl-4"
                                required={!post}
                                bind:value={$form.references[1].url}
                            />
                        </div>
                    </div>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Preencha até duas fontes de pesquisa usadas para obter informações sobre o anime
                    </div>
                </div>
            </div>
            <PostActions
                label="review"
                status={$form.review?.status}
                can={can}
            />
        </div>
    </div>
</form>
