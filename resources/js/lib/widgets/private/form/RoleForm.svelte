<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        Button,
        CheckboxInput,
        FormField,
        Preview,
        SectionDivider,
        TextArea,
        TextInput,
    } from "@/lib/components/private";
    import { rolePermissions } from "@/lib/utils";

    export let close = () => {};
    export let roleSelected = null;
    export let permissions = null;

    const can = rolePermissions();

    function permissionGroup(permission) {
        if (permission.name?.endsWith(".module.view")) {
            return "Acesso às páginas";
        }

        const match = permission.label?.match(/^\[([^\]]+)\]/);

        return match?.[1] ?? "Outras";
    }

    function permissionLabel(permission) {
        const match = permission.label?.match(/^\[([^\]]+)\]/);

        if (permission.name?.endsWith(".module.view")) {
            return match?.[1] ?? permission.label;
        }

        return permission.label?.replace(/^\[[^\]]+\]\s*/, "")
            ?? permission.name;
    }

    $: permissionGroups = Object.entries(
        (permissions?.data ?? []).reduce((groups, permission) => {
            const group = permissionGroup(permission);

            groups[group] = [...(groups[group] ?? []), permission];

            return groups;
        }, {})
    );

    $: form = useForm({
        _method: roleSelected ? "PATCH" : "POST",
        label: roleSelected?.label ?? null,
        public_label: roleSelected?.public_label ?? null,
        weight: roleSelected?.weight ?? null,
        description: roleSelected?.description ?? null,
        icon: null,
        permissions: roleSelected?.permissions?.map((item) => item.uuid) ?? [],
    });

    function submit() {
        const url = roleSelected
            ? `/panel/administration/role/${roleSelected.uuid}`
            : "/panel/administration/role";

        $form.post(url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => close(),
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField
        for="icon"
        label="Ícone"
        help="Envie um PNG, JPG ou WebP de até 1 MB."
        error={$form.errors.icon}
        spacing="compact"
    >
        <Preview
            name="icon"
            size="icon"
            tone="muted"
            color="muted"
            src={roleSelected?.icon}
            oninput={(event) => ($form.icon = event.target.files[0])}
            required={!roleSelected}
            error={$form.errors.icon}
        />
    </FormField>

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

    <FormField
        for="public_label"
        label="Nome público"
        help="Texto exibido nos filtros da página de equipe. Se ficar vazio, usa o nome."
        error={$form.errors.public_label}
        spacing="compact"
    >
        <TextInput
            variant="offcanvas"
            type="text"
            name="public_label"
            id="public_label"
            bind:value={$form.public_label}
            error={$form.errors.public_label}
        />
    </FormField>

    <FormField
        for="weight"
        label="Peso"
        help="Maior peso, maior prioridade."
        error={$form.errors.weight}
        spacing="compact"
    >
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
            variant="offcanvas"
            name="description"
            id="description"
            rows="3"
            bind:value={$form.description}
            error={$form.errors.description}
            required
        />
    </FormField>

    <SectionDivider tone="ocean" spacing="sm">Permissões</SectionDivider>

    <div class="mb-5 space-y-4 pr-2">
        {#each permissionGroups as [group, groupPermissions] (group)}
            <fieldset class="rounded-md border border-blue-ocean/15 bg-blue-ocean/5 p-3">
                <legend class="px-1 font-noto-sans text-sm font-extrabold uppercase italic text-blue-ocean">
                    {group}
                </legend>

                <div class="space-y-2">
                    {#each groupPermissions as permission (permission.uuid)}
                        <CheckboxInput
                            id={`permission-${permission.uuid}`}
                            value={permission.uuid}
                            label={permissionLabel(permission)}
                            bind:group={$form.permissions}
                        />
                    {/each}
                </div>
            </fieldset>
        {/each}
    </div>

    {#if roleSelected ? can.update : can.create}
        <div class="mt-5 border-t border-blue-ocean/10 pt-4">
            <Button
                type="submit"
                loading={$form.processing}
                variant="secondary"
                shape="pill"
            >
                {roleSelected ? "Atualizar" : "Cadastrar"}
            </Button>
        </div>
    {/if}
</form>
