<script>
    export let close = () => {};

    import { useForm, page } from "@inertiajs/svelte";
    import { userPermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = userPermissions();

    let form = useForm({
        is_virtual: false,
        username: null,
        password: null,
        name: null,
        nickname: null,
        gender: null,
        roles: null,
    });

    const submit = () => {
        $form.post("/administration/user", {
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
            <div class="flex items-center gap-2 mb-1">
                <input
                    id="human"
                    type="radio"
                    name="is_virtual"
                    value={false}
                    class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    bind:group={$form.is_virtual}
                />
                <label for="human" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                    Humano
                </label>
            </div>
            <div class="flex items-center gap-2">
                <input
                    id="virtual"
                    type="radio"
                    name="is_virtual"
                    value={true}
                    class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    bind:group={$form.is_virtual}
                />
                <label for="virtual" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                    Virtual
                </label>
            </div>
        </div>
        {#if !$form.is_virtual}
            <div class="mb-4">
                <label for="username" class="text-md font-noto-sans mb-1 block text-gray-700">
                    Login
                </label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    class="font-noto-sans text-md h-10 w-full rounded-md border border-gray-400 bg-white pl-4 outline-none"
                    bind:value={$form.username}
                    required
                />
            </div>
            <div>
                <label for="password" class="text-md font-noto-sans mb-1 block text-gray-700">
                    Senha
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="font-noto-sans text-md h-10 w-full rounded-md border border-gray-400 bg-white pl-4 outline-none"
                    bind:value={$form.password}
                    required
                />
                <div class="font-noto-sans mt-1 text-sm text-gray-400">
                    Essa senha será criptografada para proteção
                </div>
            </div>
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
        <div class="mb-4">
            <label for="name" class="text-md font-noto-sans mb-1 block text-gray-700">
                Nome
            </label>
            <input
                id="name"
                type="text"
                name="name"
                class="font-noto-sans text-md h-10 w-full rounded-md border border-gray-400 bg-white pl-4 outline-none"
                bind:value={$form.name}
                required
            />
        </div>
        <div class="mb-4">
            <label for="nickname" class="text-md font-noto-sans mb-1 block text-gray-700">
                Apelido
            </label>
            <input
                id="nickname"
                type="text"
                name="nickname"
                class="font-noto-sans text-md h-10 w-full rounded-md border border-gray-400 bg-white pl-4 outline-none"
                bind:value={$form.nickname}
                required
            />
        </div>
        <div>
            <label for="gender" class="text-md font-noto-sans mb-1 block text-gray-700">
                Gênero
            </label>
            <select
                id="gender"
                name="gender"
                class="font-noto-sans text-md h-10 w-full rounded-md border border-gray-400 bg-white pl-4 outline-none"
                bind:value={$form.gender}
                required
            >
                <option value="male">
                    Masculino
                </option>
                <option value="female">
                    Feminino
                </option>
            </select>
        </div>
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
                    <div class="flex gap-2">
                        <input
                            type="checkbox"
                            name={item.name}
                            id={item.name}
                            value={item.name}
                            bind:group={$form.roles}
                        />
                        <label for={item.name}>
                            <div class="font-noto-sans text-sm font-semibold">
                                {item.label}
                            </div>
                            <div class="font-noto-sans line-clamp-2 text-xs text-gray-400">
                                {item.description}
                            </div>
                        </label>
                    </div>
                {/each}
            {/if}
        </div>
    </div>
    {#if can.create}
        <button type="submit" class="bg-blue-skywave text-suspense-aurora font-noto-sans cursor-pointer rounded-md px-8 py-2 font-extrabold uppercase italic">
            Cadastrar
        </button>
    {/if}
</form>
