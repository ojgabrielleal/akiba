<script>
    import { useForm, page } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { PostActions, Preview, Wysiwyg } from "@/ui/components/private";
    import { postPermissions } from "@/utils";
    import { postTags } from "@/data";

    $: ({ post } = $page.props);

    let can = postPermissions();

    const normalizeTags = (tags = []) => [
        { uuid: null, name: null, ...tags[0] },
        { uuid: null, name: null, ...tags[1] },
    ];

    const normalizeReferences = (references = []) => [
        { uuid: null, name: null, url: null, ...references[0] },
        { uuid: null, name: null, url: null, ...references[1] },
    ];

    let form = useForm({
        _method: "POST",
        module: "post",
        status: null,
        image: null,
        title: null,
        cover: null,
        content: null,
        tags: normalizeTags(),
        references: normalizeReferences(),
    });

    onMount(() => {
        if(post){
            $form._method = "PATCH";
            $form.status = post.data.status;
            $form.image = post.data.image;
            $form.title = post.data.title;
            $form.cover = post.data.cover;
            $form.content = post.data.content;
            $form.tags = normalizeTags(post.data.tags);
            $form.references = normalizeReferences(post.data.references);
        }
    });

    const submit = (event) => {
        let url = post ? `/panel/post/${post.data.uuid}` : "/panel/post";

        $form.status = event.submitter.value;
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
            <div class="mb-8">
                <label for="title" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Título
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    class="w-full h-12 bg-blue-ocean border border-blue-skywave font-noto-sans text-suspense-aurora rounded-md outline-none pl-4"
                    required={!post}
                    bind:value={$form.title}
                />
            </div>
            <div class="mb-8">
                <label for="cover" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Capa da matéria
                </label>
                <Preview
                    name="cover"
                    src={$form.cover}
                    oninput={(event)=>($form.cover = event.target.files[0])}
                    required={!post}
                />
            </div>
            <div>
                <label for="content" class="text-orange-amber font-extrabold italic text-lg uppercase font-noto-sans block mb-1">
                    Escreva sua matéria
                </label>
                <Wysiwyg
                    name="content"
                    required
                    bind:value={$form.content}
                />
            </div>
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
                    <div class="mb-6">
                        <label for="tags" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                            Primeira Tag
                        </label>
                        <select
                            id="tags"
                            name="tags"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-full pl-4"
                            required={!post}
                            bind:value={$form.tags[0].name}
                        >
                            {#each Object.values(postTags) as item}
                                <option value={item.value}>
                                    {item.label}
                                </option>
                            {/each}
                        </select>
                    </div>
                    <div>
                        <label for="tags" class="text-blue-skywave font-extrabold italic text-md uppercase font-noto-sans block mb-1 ml-3">
                            Segunda Tag
                        </label>
                        <select
                            id="tags"
                            name="tags"
                            class="w-full h-12 bg-suspense-aurora font-noto-sans rounded-full pl-4"
                            required={!post}
                            bind:value={$form.tags[1].name}
                        >
                            {#each Object.values(postTags) as item}
                                <option value={item.value}>
                                    {item.label}
                                </option>
                            {/each}
                        </select>
                    </div>
                    <div class="text-center text-neutral-gray font-light italic text-md uppercase font-noto-sans mt-5">
                        Escolha até 2 tags para a sua matéria
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
                        Preencha até duas fontes de pesquisa usadas para montar a matéria
                    </div>
                </div>
            </div>
            <PostActions
                label="Matéria"
                post={post}
                status={$form.status}
                can={can}
            />
        </div>
    </div>
</form>
