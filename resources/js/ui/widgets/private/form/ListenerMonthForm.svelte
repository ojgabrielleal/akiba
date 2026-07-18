<script>
    export let close = () => {};
    export let listenerMonthFound;

    import { useForm } from "@inertiajs/svelte";
    import { Button, FormField, Preview, TextInput } from "@/ui/components/private";

    $: form = useForm({
        avatar: null,
        birthday: listenerMonthFound?.birthday ?? null,
    });

    const submit = () => {
        $form.post("/panel/radio/listener-month", {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <Preview
            size="profile"
            tone="muted"
            color="muted"
            name="avatar"
            oninput={(event) => ($form.avatar = event.target.files[0])}
            required
        />
    </div>
    <FormField for="birthday" label="Aniversário" error={$form.errors.birthday}>
        <TextInput
            variant="offcanvas"
            id="birthday"
            type="date"
            name="birthday"
            bind:value={$form.birthday}
            error={$form.errors.birthday}
            required
        />
    </FormField>
    <FormField for="listener" label="Ouvinte">
        <TextInput
            variant="offcanvas"
            id="listener"
            type="text"
            name="listener"
            value={listenerMonthFound?.name}
            class="disabled:cursor-not-allowed disabled:bg-gray-200"
            disabled
        />
    </FormField>
    <FormField for="address" label="Endereço">
        <TextInput
            variant="offcanvas"
            id="address"
            type="text"
            name="address"
            value={listenerMonthFound?.address}
            class="disabled:cursor-not-allowed disabled:bg-gray-200"
            disabled
        />
    </FormField>
    <FormField for="favorite_show" label="Programa favorito">
        <TextInput
            variant="offcanvas"
            id="favorite_show"
            type="text"
            name="favorite_show"
            value={listenerMonthFound?.favorite_program?.name}
            class="disabled:cursor-not-allowed disabled:bg-gray-200"
            disabled
        />
    </FormField>
    <FormField for="favorite_anime" label="Anime favorito">
        <TextInput
            variant="offcanvas"
            id="favorite_anime"
            type="text"
            name="favorite_anime"
            value={listenerMonthFound?.favorite_music?.production}
            class="disabled:cursor-not-allowed disabled:bg-gray-200"
            disabled
        />
    </FormField>
    <FormField for="requests_total" label="Quantidade de pedidos feitos">
        <TextInput
            variant="offcanvas"
            id="requests_total"
            type="text"
            name="requests_total"
            value={listenerMonthFound?.requests_total}
            class="disabled:cursor-not-allowed disabled:bg-gray-200"
            disabled
        />
    </FormField>
    <Button
        type="submit"
        variant="secondary"
        size="lg"
        shape="pill"
        loading={$form.processing}
    >
        Atualizar
    </Button>
</form>
