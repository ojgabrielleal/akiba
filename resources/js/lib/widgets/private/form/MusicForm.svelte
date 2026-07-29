<script>
    export let close = () => {};
    export let musicSelected;

    import { useForm } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        Preview,
        SelectInput,
        TextInput,
    } from "@/lib/components/private";
    import { musicPermissions } from "@/lib/utils";

    const can = musicPermissions();

    $: form = useForm({
        _method: "PATCH",
        image: null,
        image_ranking: null,
        type: musicSelected?.type ?? null,
        production: musicSelected?.production ?? null,
        artist: musicSelected?.artist ?? null,
        name: musicSelected?.name ?? null,
    });

    const submit = () => {
        $form.post(`/panel/radio/music/${musicSelected.uuid}`, {
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
    <div class="mb-4 grid grid-cols-1 gap-4 px-5 sm:grid-cols-2">
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image ?? musicSelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
        />
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image_ranking"
            src={$form.image_ranking ?? musicSelected?.ranking?.image}
            oninput={(event) => ($form.image_ranking = event.target.files[0])}
        />
    </div>
    <FormField for="music-name" label="Música" error={$form.errors.name}>
        <TextInput
            variant="offcanvas"
            id="music-name"
            type="text"
            name="name"
            bind:value={$form.name}
            error={$form.errors.name}
            required
        />
    </FormField>
    <FormField for="artist" label="Cantor/Banda" error={$form.errors.artist}>
        <TextInput
            variant="offcanvas"
            id="artist"
            type="text"
            name="artist"
            bind:value={$form.artist}
            error={$form.errors.artist}
            required
        />
    </FormField>
    <FormField for="production" label="Anime" error={$form.errors.production}>
        <TextInput
            variant="offcanvas"
            id="production"
            type="text"
            name="production"
            bind:value={$form.production}
            error={$form.errors.production}
            required
        />
    </FormField>
    <FormField for="type" label="Tipo" error={$form.errors.type} spacing="section">
        <SelectInput
            variant="offcanvas"
            id="type"
            name="type"
            bind:value={$form.type}
            error={$form.errors.type}
            required
        >
            <option value={null} disabled>
                Selecione uma opção
            </option>
            <option value="OP">
                OP
            </option>
            <option value="ED">
                ED
            </option>
        </SelectInput>
    </FormField>
    {#if can.update}
        <Button
            type="submit"
            variant="secondary"
            shape="pill"
            loading={$form.processing}
        >
            Atualizar
        </Button>
    {/if}
</form>
