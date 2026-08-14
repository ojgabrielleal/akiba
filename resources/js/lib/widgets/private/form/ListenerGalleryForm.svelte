<script>
    export let close = () => {};
    export let gallerySelected;

    import { useForm } from "@inertiajs/svelte";
    import { Button, FormField, Preview, TextArea, TextInput } from "@/lib/components/private";
    import { listenerGalleryPermissions } from "@/lib/utils";

    const can = listenerGalleryPermissions();

    $: form = useForm({
        _method: gallerySelected ? "PATCH" : "POST",
        image: null,
        caption: gallerySelected?.caption ?? null,
        listener_name: gallerySelected?.listener_name ?? null,
    });

    const submit = () => {
        const url = gallerySelected
            ? `/panel/media/listener-gallery/${gallerySelected.uuid}`
            : "/panel/media/listener-gallery";

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
            size="profile"
            tone="muted"
            color="muted"
            name="image"
            src={gallerySelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!gallerySelected}
            error={$form.errors.image}
        />
    </FormField>
    <FormField for="listener_name" label="Nome do ouvinte" error={$form.errors.listener_name}>
        <TextInput
            variant="offcanvas"
            id="listener_name"
            type="text"
            name="listener_name"
            maxlength="255"
            bind:value={$form.listener_name}
            error={$form.errors.listener_name}
        />
    </FormField>
    <FormField for="caption" label="Legenda" error={$form.errors.caption}>
        <TextArea
            id="caption"
            name="caption"
            rows="4"
            maxlength="255"
            bind:value={$form.caption}
            error={$form.errors.caption}
        />
    </FormField>
    {#if (gallerySelected && can.update) || (!gallerySelected && can.create)}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            {gallerySelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
