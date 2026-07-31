<script>
    import { useForm } from "@inertiajs/svelte";

    import { Button, CheckboxInput, FormField, SectionDivider, TextInput } from "@/lib/components/private";
    import { userPermissions } from "@/lib/utils";

    export let close = () => {};
    export let userSelected;
    export let roles = null;

    const can = userPermissions().authority;

    $: form = useForm({
        password: null,
        roles: userSelected?.roles.map((role) => role.name) ?? [],
    });

    function submit() {
        $form.patch(`/panel/administration/user/${userSelected.uuid}`, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="password" label="Nova senha" help="Essa senha será criptografada para proteção" error={$form.errors.password} spacing="compact">
        <TextInput
            variant="offcanvas"
            id="password"
            type="password"
            name="password"
            placeholder="∗∗∗∗∗∗∗∗∗∗∗∗∗∗∗"
            bind:value={$form.password}
            error={$form.errors.password}
        />
    </FormField>
    <SectionDivider tone="ocean">Cargos</SectionDivider>
    <div class="pb-6">
        <div class="flex flex-col gap-2">
            {#if roles}
                {#each roles.data as item}
                    <CheckboxInput
                        label={item.label}
                        description={item.description}
                        name={item.name}
                        id={item.name}
                        value={item.name}
                        bind:group={$form.roles}
                    />
                {/each}
            {/if}
        </div>
    </div>
    {#if can.update}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            Atualizar
        </Button>
    {/if}
</form>
