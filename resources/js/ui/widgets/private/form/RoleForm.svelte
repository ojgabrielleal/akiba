<script>
    export let close = () => {};
    export let identifier;

    import { useForm, page } from "@inertiajs/svelte";
    import axios from "axios";
    import { Button, FormField, TextArea, TextInput } from "@/ui/components/private";
    import { rolePermissions } from "@/utils";

    $: ({ permissions } = $page.props);

    let can = rolePermissions();

    $: form = useForm({
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
    <FormField for="label" label="Nome" error={$form.errors.label} spacing="compact">
        <TextInput
            variant="offcanvas"
            type="text"
            name="label"
            id="label"
            bind:value={$form.label}
            error={$form.errors.label}
            required
        />
    </FormField>
    <FormField for="weight" label="Peso" help="Importância do cargo sobre demais existentes" error={$form.errors.weight} spacing="compact">
        <TextInput
            variant="offcanvas"
            type="number"
            name="weight"
            id="weight"
            bind:value={$form.weight}
            error={$form.errors.weight}
            required
        />
    </FormField>
    <FormField for="description" label="Descrição" error={$form.errors.description} spacing="compact">
        <TextArea
            name="description"
            id="description"
            rows="3"
            bind:value={$form.description}
            error={$form.errors.description}
        />
    </FormField>
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
        <Button
            type="submit"
            size="lg"
            loading={$form.processing}
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
