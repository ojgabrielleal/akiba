<script>
    export let close = () => {};
    export let identifier;

    import { useForm, page } from "@inertiajs/svelte";
    import axios from "axios";
    import { rolePermissions } from "@/utils";

    $: ({ permissions } = $page.props);

    let can = rolePermissions();

    let form = useForm({
        label: null,
        weight: null,
        description: null,
        permissions: [],
    });

    if (identifier) {
        axios.get(`/panel/administration/role/${identifier}`)
            .then(function (response) {
                const data = response.data.data;

                $form.label = data.label;
                $form.weight = data.weight;
                $form.description = data.description;
                $form.permissions = data.permissions.map((item) => item.uuid);
            })
            .catch(() => {
                console.error("Error when find role");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/administration/role/${identifier}`
            : "/panel/administration/role";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-3">
        <label for="label" class="text-md text-gray-700 font-noto-sans block mb-1">
            Nome
        </label>
        <input
            type="text"
            name="label"
            id="label"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.label}
            required
        />
    </div>
    <div class="mb-3">
        <label for="weight" class="text-md text-gray-700 font-noto-sans block mb-1">
            Peso
        </label>
        <input
            type="number"
            name="weight"
            id="weight"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.weight}
            required
        />
        <div class="text-sm font-noto-sans text-gray-400 mt-1">
            Importância do cargo sobre demais existentes
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="text-md text-gray-700 font-noto-sans block mb-1">
            Descrição
        </label>
        <textarea
            type="text"
            name="description"
            id="description"
            rows="3"
            class="w-full bg-white font-noto-sans text-md rounded-md outline-none py-2 px-4 border border-gray-400"
            bind:value={$form.description}
        ></textarea>
    </div>
    <div class="mb-3">
        <label for="permissions" class="text-md text-gray-700 font-noto-sans block mb-1">
            Permissões
        </label>
        <select
            id="permissions"
            name="permissions"
            class="w-full h-60 bg-white font-noto-sans text-md rounded-md outline-none py-2 px-4 border border-gray-400"
            bind:value={$form.permissions}
            multiple
        >
            {#each permissions.data as permission}
                <option value={permission.uuid}>
                    {permission.label}
                </option>
            {/each}
        </select>
        <div class="text-sm font-noto-sans text-gray-400 mt-1">
            Pressione CTRL para manipular as permissões
        </div>
    </div>
    {#if can.create || can.update}
        <button
            aria-label=""
            type="submit"
            class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase"
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
