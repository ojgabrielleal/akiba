<script>
    export let close = () => {};
    export let fileSelected;
    export let fileType;

    import { useForm } from "@inertiajs/svelte";
    import { Button, FormField, Preview, TextInput } from "@/lib/components/private";
    import { repositoryPermissions } from "@/lib/utils";

    const can = repositoryPermissions();

    $: form = useForm({
        _method: fileSelected ? 'PATCH' : 'POST',
        image: null,
        name: fileSelected?.name,
        type: fileSelected?.type ?? fileType,
        url: fileSelected?.url,
    });

    const submit = () => {
        let url = fileSelected
            ? `/panel/marketing/repository/${fileSelected.uuid}`
            : "/panel/marketing/repository";

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
    <FormField for="image" label="" error={$form.errors.image}>
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image ?? fileSelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!fileSelected}
            error={$form.errors.image}
        />
    </FormField>
    <FormField for="name" label="Nome do arquivo" error={$form.errors.name}>
        <TextInput
            variant="offcanvas"
            id="name"
            type="text"
            name="name"
            bind:value={$form.name}
            error={$form.errors.name}
            required={!fileSelected}
        />
    </FormField>
    <FormField for="url" label="Endereço de download" error={$form.errors.url}>
        <TextInput
            variant="offcanvas"
            id="url"
            type="url"
            name="url"
            bind:value={$form.url}
            error={$form.errors.url}
            required={!fileSelected}
        />
    </FormField>
    <FormField for="type" label="Categoria do arquivo" error={$form.errors.type}>
        <TextInput
            variant="offcanvas"
            id="type"
            type="text"
            name="type"
            bind:value={$form.type}
            error={$form.errors.type}
            class="disabled:opacity-50"
            disabled
        />
    </FormField>
    {#if can.create && can.update}
        <Button
            type="submit"
            variant="secondary"
            shape="pill"
            loading={$form.processing}
        >
            {fileSelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
