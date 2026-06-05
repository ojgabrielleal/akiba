<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
    import { useForm } from "@inertiajs/svelte";
    import { activityPermissions } from "@/utils";

    let can = activityPermissions();

    let form = useForm({
        title: null,
        purpose: null,
        limit: null,
        hour: null,
        date: null,
        content: null,
    });

    if (identifier) {
        axios.get(`/panel/administration/activity/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.title = data.title;
                $form.purpose = data.allows_confirmations ? "activity" : "notice";
                $form.limit = data.limit;
                $form.hour = data.hour;
                $form.date = data.date;
                $form.content = data.content;
            })
            .catch(() => {
                console.error("Error when find activity");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/administration/activity/${identifier}`
            : "/panel/administration/activity";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <div class="text-md text-gray-700 font-noto-sans mb-2">
            Qual a finalidade desta criação?
        </div>
        <div class="flex items-center gap-2 mb-1">
            <input
                id="notice"
                type="radio"
                name="purpose"
                value="notice"
                class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                bind:group={$form.purpose}
            />
            <label for="notice" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                Aviso
            </label>
        </div>
        <div class="flex items-center gap-2">
            <input
                id="activity"
                type="radio"
                name="purpose"
                value="activity"
                class="cursor-pointer w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                bind:group={$form.purpose}
            />
            <label for="activity" class="cursor-pointer text-md text-gray-700 font-noto-sans">
                Atividade
            </label>
        </div>
    </div>
    <div class="mb-4">
        <label for="title" class="text-md text-gray-700 font-noto-sans block mb-1">
            {#if $form.purpose === "activity"}
                Título da atividade
            {:else if $form.purpose === "notice"}
                Título do aviso
            {:else}
                Título
            {/if}
        </label>
        <input
            type="text"
            id="title"
            name="title"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.title}
            required
        />
    </div>
    <div class="mb-4">
        <label for="limit" class="text-md text-gray-700 font-noto-sans block mb-1">
            Data limite
        </label>
        <input
            type="date"
            id="limit"
            name="limit"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.limit}
            required
        />
        <div class="text-sm font-noto-sans text-gray-400 mt-1">
            {#if $form.purpose === "notice"}
                Data limite para exibição do aviso
            {:else if $form.purpose === "activity"}
                Data limite para confirmação da atividade
            {/if}
        </div>
    </div>
    {#if $form.purpose === "activity"}
        <div class="grid grid-cols-2 gap-3">
            <div class="mb-4">
                <label for="hour" class="text-md text-gray-700 font-noto-sans block mb-1">
                    Hora
                </label>
                <input
                    type="time"
                    id="hour"
                    name="hour"
                    class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                    bind:value={$form.hour}
                    required
                />
                <div class="text-sm font-noto-sans text-gray-400 mt-1">
                    Hora da atividade
                </div>
            </div>
            <div class="mb-4">
                <label for="date" class="text-md text-gray-700 font-noto-sans block mb-1">
                    Data
                </label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                    bind:value={$form.date}
                    required
                />
                <div class="text-sm font-noto-sans text-gray-400 mt-1">
                    Data da atividade
                </div>
            </div>
        </div>
    {/if}
    <div class="mb-4">
        <label for="content" class="text-md text-gray-700 font-noto-sans block mb-1">
            Conteúdo
        </label>
        <textarea
            id="content"
            name="content"
            rows="3"
            class="w-full bg-white font-noto-sans text-md rounded-md outline-none py-2 px-4 border border-gray-400"
            bind:value={$form.content}
            required
        ></textarea>
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
