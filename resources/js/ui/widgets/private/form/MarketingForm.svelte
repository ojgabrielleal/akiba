<script>
    export let close = () => {};
    export let identifier;

    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";
    import { repositoryPermissions } from "@/utils";

    let can = repositoryPermissions();

    let form = useForm({
        _method: null,
        image: null,
        name: null,
        type: null,
        url: null,
    });

    if (identifier) {
        axios.get(`/panel/marketing/repository/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form._method = "PATCH";
                $form.image = data.image;
                $form.name = data.name;
                $form.type = data.type;
                $form.url = data.url;
            })
            .catch(() => {
                console.error("Error when find file selected");
                close();
            });
    }

    const submit = () => {
        let url = identifier
            ? `/marketing/repository/${identifier}`
            : "/marketing/repository";

        $form.post(url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <Preview
            name="image"
            size="compact"
            src={$form.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!identifier}
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
            required
        />
    </div>
    <div class="mb-4">
        <label for="type" class="text-md text-gray-700 font-noto-sans block mb-1">
            Categoria do arquivo
        </label>
        <select
            id="type"
            name="type"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.type}
            required
        >
            <option value="tutorial">
                Tutoriais
            </option>
            <option value="software">
                Programas
            </option>
            <option value="package">
                Pacotes
            </option>
        </select>
    </div>
    <div class="mb-4">
        <label for="url" class="text-md text-gray-700 font-noto-sans block mb-1">
            URL do conteúdo hospedado externamente
        </label>
        <input
            id="url"
            type="url"
            name="url"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.url}
            required
        />
    </div>
    {#if can.create && can.update}
        <button
            aria-label=""
            type="submit"
            class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase"
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
