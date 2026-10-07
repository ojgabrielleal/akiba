<script>
    import { useForm } from "@inertiajs/svelte";

    import { Button, FormField, TextInput } from "@/lib/components/private";

    export let radioStation = null;
    export let close = () => {};

    $: isEditing = Boolean(radioStation?.uuid);
    $: endpoint = isEditing
        ? `/panel/reports/radio-station/${radioStation.uuid}`
        : "/panel/reports/radio-station";

    const form = useForm({
        name: radioStation?.name ?? "",
        logo: radioStation?.logo ?? "",
        website: radioStation?.website ?? "",
        endpoint: radioStation?.endpoint ?? "",
        listeners_path: radioStation?.listeners_path ?? "",
    });

    const submit = () => {
        const options = {
            preserveScroll: true,
            onSuccess: close,
        };

        if (isEditing) {
            $form.patch(endpoint, options);
            return;
        }

        $form.post(endpoint, options);
    };
</script>

<form class="space-y-4" on:submit|preventDefault={submit}>
    <FormField for="radio-station-name" label="Nome" error={$form.errors.name}>
        <TextInput
            id="radio-station-name"
            name="name"
            variant="offcanvas"
            bind:value={$form.name}
            error={$form.errors.name}
            required
        />
    </FormField>

    <FormField for="radio-station-logo" label="Logo" error={$form.errors.logo} help="URL da imagem usada no card.">
        <TextInput
            id="radio-station-logo"
            name="logo"
            type="url"
            variant="offcanvas"
            bind:value={$form.logo}
            error={$form.errors.logo}
            placeholder="https://..."
        />
    </FormField>

    <FormField for="radio-station-website" label="Site" error={$form.errors.website}>
        <TextInput
            id="radio-station-website"
            name="website"
            type="url"
            variant="offcanvas"
            bind:value={$form.website}
            error={$form.errors.website}
            placeholder="https://..."
        />
    </FormField>

    <FormField for="radio-station-endpoint" label="Endpoint de audiência" error={$form.errors.endpoint} help="URL que retorna os dados de ouvintes da rádio.">
        <TextInput
            id="radio-station-endpoint"
            name="endpoint"
            type="url"
            variant="offcanvas"
            bind:value={$form.endpoint}
            error={$form.errors.endpoint}
            placeholder="https://..."
            required
        />
    </FormField>

    <FormField for="radio-station-listeners-path" label="Caminho dos ouvintes" error={$form.errors.listeners_path} help="Ex.: listeners.current ou icestats.source.listeners">
        <TextInput
            id="radio-station-listeners-path"
            name="listeners_path"
            variant="offcanvas"
            bind:value={$form.listeners_path}
            error={$form.errors.listeners_path}
            required
        />
    </FormField>

    <div class="flex justify-end gap-2 pt-2">
        <Button type="button" variant="secondary" on:click={close}>
            Cancelar
        </Button>
        <Button type="submit" variant="accent" loading={$form.processing} disabled={$form.processing}>
            {isEditing ? "Salvar rádio" : "Adicionar rádio"}
        </Button>
    </div>
</form>
