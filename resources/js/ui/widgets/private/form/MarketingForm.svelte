<script>
    export let close = () => {};
    export let fileSelected;
    export let fileType;

    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";
    import { repositoryPermissions } from "@/utils";

    let can = repositoryPermissions();

    $: form = useForm({
        _method: fileSelected ? 'PATCH' : 'POST',
        image: fileSelected.image,
        name: fileSelected.name,
        type: fileSelected.type ?? fileType,
        url: fileSelected.url,
    });

    const submit = () => {
        let url = fileSelected
            ? `/marketing/repository/${fileSelected.uuid}`
            : "/marketing/repository";

        $form.post(url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!fileSelected}
        />
    </div>
    <div class="mb-4">
        <label for="name" class="text-md text-gray-700 font-noto-sans block mb-1">
            Nome do arquivo
        </label>
        <input
            id="name"
            type="text"
            name="name"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.name}
            required={!fileSelected}
        />
    </div>
    <div class="mb-4">
        <label for="url" class="text-md text-gray-700 font-noto-sans block mb-1">
            Endereço de download
        </label>
        <input
            id="url"
            type="url"
            name="url"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.url}
            required={!fileSelected}
        />
    </div>
    <div class="mb-4">
        <label for="type" class="text-md text-gray-700 font-noto-sans block mb-1">
            Categoria do arquivo
        </label>
        <input
            id="type"
            type="text"
            name="type"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:opacity-50"
            bind:value={$form.type}
            disabled
        />
    </div>
    {#if can.create && can.update}
        <button
            type="submit"
            class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
        >
            {fileSelected ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
