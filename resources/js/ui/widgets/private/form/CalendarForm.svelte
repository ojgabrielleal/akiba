<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
    import { useForm, page } from "@inertiajs/svelte";
    import { calendarPermissions } from "@/utils";

    let { users } = $page.props;

    let can = calendarPermissions();

    let form = useForm({
        user: null,
        hour: null,
        date: null,
        content: null,
        type: null,
    });

    $: if (identifier) {
        axios.get(`/panel/administration/calendar/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.user = data.responsible.uuid;
                $form.hour = data.hour;
                $form.date = data.date;
                $form.content = data.content;
                $form.type = data.type;
            })
            .catch(() => {
                console.error("Error when find calendar");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/administration/calendar/${identifier}`
            : "/panel/administration/calendar";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <label for="user" class="text-md text-gray-700 font-noto-sans block mb-1">
            Membro designado
        </label>
        <select
            id="user"
            name="user"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.user}
            required
        >
            {#each users.data as item}
                <option value={item.uuid}>
                    {item.nickname}
                </option>
            {/each}
        </select>
    </div>
    <div class="mb-4">
        <label for="type" class="text-md text-gray-700 font-noto-sans block mb-1">
            Tipo do evento
        </label>
        <select
            id="type"
            name="type"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.type}
            required
        >
            <option value="show">
                Programa
            </option>
            <option value="live">
                Live (Twitch/Kick)
            </option>
            <option value="video">
                Vídeo (Youtube/Facebook/Instagram)
            </option>
            <option value="podcast">
                Podcast
            </option>
        </select>
    </div>
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
                Hora do evento
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
                Data do evento
            </div>
        </div>
    </div>
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
