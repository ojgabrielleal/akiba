<script>
    export let close = () => {};

    import { useForm, page } from "@inertiajs/svelte";
    import {
        Button,
        CheckboxInput,
        FormField,
        RadioInput,
        SectionDivider,
        TextInput,
    } from "@/ui/components/private";
    import { userPermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = userPermissions();

    $: form = useForm({
        is_virtual: false,
        username: null,
        password: null,
        name: null,
        nickname: null,
        gender: null,
        roles: [],
    });

    const submit = () => {
        $form.post("/panel/administration/user", {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        {#if !$form.is_virtual}
            <FormField for="username" label="Login" error={$form.errors.username}>
                <TextInput
                    variant="offcanvas"
                    id="username"
                    type="text"
                    name="username"
                    bind:value={$form.username}
                    error={$form.errors.username}
                    required
                />
            </FormField>
            <FormField for="password" label="Senha" help="Essa senha será criptografada para proteção" error={$form.errors.password} spacing="none">
                <TextInput
                    variant="offcanvas"
                    id="password"
                    type="password"
                    name="password"
                    bind:value={$form.password}
                    error={$form.errors.password}
                    required
                />
            </FormField>
        {/if}
        <div class="mt-4">
            <div class="mb-2 font-noto-sans text-md text-gray-700">
                Tipo de usuário
            </div>
            <div class="mb-1">
                <RadioInput
                    id="human"
                    name="is_virtual"
                    value={false}
                    label="Membro da equipe"
                    bind:group={$form.is_virtual}
                />
            </div>
            <div>
                <RadioInput
                    id="virtual"
                    name="is_virtual"
                    value={true}
                    label="Bot"
                    bind:group={$form.is_virtual}
                />
            </div>
        </div>
    </div>
    <div class="mb-4">
        <SectionDivider tone="ocean" spacing="sm">Informações básicas</SectionDivider>
        <FormField for="name" label="Nome" error={$form.errors.name}>
            <TextInput
                variant="offcanvas"
                id="name"
                type="text"
                name="name"
                bind:value={$form.name}
                error={$form.errors.name}
                required
            />
        </FormField>
        <FormField for="nickname" label="Apelido" error={$form.errors.nickname}>
            <TextInput
                variant="offcanvas"
                id="nickname"
                type="text"
                name="nickname"
                bind:value={$form.nickname}
                error={$form.errors.nickname}
                required
            />
        </FormField>
        <FormField for="gender-male" label="Gênero" error={$form.errors.gender} spacing="none">
            <div class="flex flex-col gap-1">
                <RadioInput
                    id="gender-male"
                    name="gender"
                    value="male"
                    label="Masculino"
                    bind:group={$form.gender}
                    error={$form.errors.gender}
                    required
                />
                <RadioInput
                    id="gender-female"
                    name="gender"
                    value="female"
                    label="Feminino"
                    bind:group={$form.gender}
                    error={$form.errors.gender}
                    required
                />
            </div>
        </FormField>
    </div>
    <div class="mb-5">
        <SectionDivider tone="ocean" spacing="sm">Cargos</SectionDivider>
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
    {#if can.create}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            Cadastrar
        </Button>
    {/if}
</form>
