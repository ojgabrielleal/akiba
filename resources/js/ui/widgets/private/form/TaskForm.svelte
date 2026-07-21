<script>
    export let close = () => {};
    export let taskSelected;

    import { useForm, page } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        SelectInput,
        TextInput,
    } from "@/ui/components/private";
    import { taskPermissions } from "@/utils";

    $: ({ users } = $page.props);

    let can = taskPermissions();

    $: form = useForm({
        user: taskSelected?.responsible.uuid ?? null,
        title: taskSelected?.title ?? null,
        dead_line: taskSelected?.dead_line ?? null,
        description: taskSelected?.description ?? null,
    });

    const submit = () => {
        const method = taskSelected ? "patch" : "post";
        let url = taskSelected
            ? `/panel/administration/task/${taskSelected.uuid}`
            : "/panel/administration/task";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="user" label="Membro responsável" error={$form.errors.user}>
        <SelectInput
            variant="offcanvas"
            id="user"
            name="user"
            bind:value={$form.user}
            error={$form.errors.user}
            required
        >
            <option value="">
                Selecione um membro
            </option>
            {#each users.data as user}
                <option value={user.uuid}>
                    {user.nickname}
                </option>
            {/each}
        </SelectInput>
    </FormField>
    <FormField for="title" label="Título" error={$form.errors.title}>
        <TextInput
            variant="offcanvas"
            id="title"
            type="text"
            name="title"
            bind:value={$form.title}
            error={$form.errors.title}
            required
        />
    </FormField>
    <FormField for="dead_line" label="Data de vencimento" error={$form.errors.dead_line}>
        <TextInput
            variant="offcanvas"
            id="dead_line"
            type="date"
            name="dead_line"
            bind:value={$form.dead_line}
            error={$form.errors.dead_line}
            required
        />
    </FormField>
    <FormField for="description" label="Descrição" error={$form.errors.description}>
        <TextInput
            variant="offcanvas"
            id="description"
            type="text"
            name="description"
            bind:value={$form.description}
            error={$form.errors.description}
            required
        />
    </FormField>
    {#if taskSelected ? can.update : can.create}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            {taskSelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
