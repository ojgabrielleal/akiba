<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
    import { useForm, page } from "@inertiajs/svelte";
    import { taskPermissions } from "@/utils";

    $: ({ users } = $page.props);

    let can = taskPermissions();

    let form = useForm({
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
    <div class="mb-4">
        <label for="user" class="text-md text-gray-700 font-noto-sans block mb-1">
            Membro responsável
        </label>
        <select
            id="user"
            name="user"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.user}
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
        </select>
    </div>
    <div class="mb-4">
        <label for="title" class="text-md text-gray-700 font-noto-sans block mb-1">
            Título
        </label>
        <input
            id="title"
            type="text"
            name="title"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.title}
            required
        />
    </div>
    <div class="mb-4">
        <label for="dead_line" class="text-md text-gray-700 font-noto-sans block mb-1">
            Data de vencimento
        </label>
        <input
            id="dead_line"
            type="date"
            name="dead_line"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.dead_line}
            required
        />
    </div>
    <div class="mb-4">
        <label for="description" class="text-md text-gray-700 font-noto-sans block mb-1">
            Descrição
        </label>
        <input
            id="description"
            type="text"
            name="description"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.description}
            required
        />
    </div>
    {#if can.create || can.update}
        <button
            aria-label=""
            type="submit"
            class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase"
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
