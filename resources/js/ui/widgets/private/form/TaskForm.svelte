<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
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
        user: null,
        title: null,
        dead_line: null,
        description: null,
    });

    if (identifier) {
        axios.get(`/panel/administration/task/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.user = data.responsible.uuid;
                $form.title = data.title;
                $form.dead_line = data.dead_line;
                $form.description = data.description;
            })
            .catch((err) => {
                console.error("Error when find task selected", err);
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        let url = identifier
            ? `/panel/administration/task/${identifier}`
            : "/panel/administration/task";

        $form[method](url, {
            preserveScroll: true,
            onFinish: () => close(),
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
