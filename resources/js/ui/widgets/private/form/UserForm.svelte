<script>
    export let close = () => {};

    import { useForm, page } from "@inertiajs/svelte";
    import {
        Button,
        CheckboxInput,
        FormField,
        RadioInput,
        SelectInput,
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
        roles: null,
    });

    const submit = () => {
        $form.post("/panel/administration/user", {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-10">
        <div class="mb-4">
            <div class="text-md text-gray-700 font-noto-sans mb-2">
                Esse usuário é humano ou virtual?
            </div>
            <div class="mb-1">
                <RadioInput
                    id="human"
                    name="is_virtual"
                    value={false}
                    label="Humano"
                    bind:group={$form.is_virtual}
                />
            </div>
            <div>
                <RadioInput
                    id="virtual"
                    name="is_virtual"
                    value={true}
                    label="Virtual"
                    bind:group={$form.is_virtual}
                />
            </div>
        </div>
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
    </div>
    <div class="mb-10">
        <div class="mb-5 flex w-full items-center justify-center">
            <div class="relative w-full">
                <div class="bg-blue-skywave absolute top-1/2 left-0 h-[0.1rem] w-1/5 -translate-y-1/2 rounded-full"></div>
                <span class="text-blue-skywave font-noto-sans absolute inset-0 flex items-center justify-center font-extrabold uppercase italic">
                    Informações básicas
                </span>
                <div class="bg-blue-skywave absolute top-1/2 right-0 h-[0.1rem] w-1/5 -translate-y-1/2 rounded-full"></div>
            </div>
        </div>
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
        <FormField for="gender" label="Gênero" error={$form.errors.gender} spacing="none">
            <SelectInput
                variant="offcanvas"
                id="gender"
                name="gender"
                bind:value={$form.gender}
                error={$form.errors.gender}
                required
            >
                <option value="male">
                    Masculino
                </option>
                <option value="female">
                    Feminino
                </option>
            </SelectInput>
        </FormField>
    </div>
    <div class="mb-5">
        <div class="mb-5 flex w-full items-center justify-center">
            <div class="relative w-full">
                <div class="bg-blue-skywave absolute top-1/2 left-0 h-[0.1rem] w-1/3 -translate-y-1/2 rounded-full"></div>
                <span class="text-blue-skywave font-noto-sans absolute inset-0 flex items-center justify-center font-extrabold uppercase italic">
                    Cargos
                </span>
                <div class="bg-blue-skywave absolute top-1/2 right-0 h-[0.1rem] w-1/3 -translate-y-1/2 rounded-full"></div>
            </div>
        </div>
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
            size="lg"
        >
            Cadastrar
        </Button>
    {/if}
</form>
