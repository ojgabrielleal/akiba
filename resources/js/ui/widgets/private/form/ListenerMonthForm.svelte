<script>
    export let close = () => {};

    import axios from "axios";
    import { onMount } from "svelte";
    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";

    let form = useForm({
        avatar: null,
        name: null,
        address: null,
        favorite_program: null,
        favorite_anime: null,
        requests_total: null,
    });

    onMount(() => {
        axios.get("/panel/radio/listener-month/found")
            .then((response) => {
                const listenerMonth = response.data.data;

                $form.name = listenerMonth.name;
                $form.address = listenerMonth.address;
                $form.favorite_anime = listenerMonth.favorite_anime;
                $form.favorite_program = listenerMonth.favorite_program;
                $form.requests_total = listenerMonth.requests_total;
            })
            .catch(() => {
                console.error("Error when find listener month");
                close();
            });
    });

    const submit = () => {
        $form.post("/panel/radio/listener-month", {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <Preview
            size="compact"
            name="avatar"
            oninput={(event) => ($form.avatar = event.target.files[0])}
            required
        />
    </div>
    <div class="mb-4">
        <label for="listener" class="text-md text-gray-700 font-noto-sans block mb-1">
            Ouvinte
        </label>
        <input
            id="listener"
            type="text"
            name="listener"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.name}
            disabled
        />
    </div>
    <div class="mb-4">
        <label for="address" class="text-md text-gray-700 font-noto-sans block mb-1">
            Endereço
        </label>
        <input
            id="address"
            type="text"
            name="address"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.address}
            disabled
        />
    </div>
    <div class="mb-4">
        <label for="favorite_show" class="text-md text-gray-700 font-noto-sans block mb-1">
            Programa favorito
        </label>
        <input
            id="favorite_show"
            type="text"
            name="favorite_show"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.favorite_program}
            disabled
        />
    </div>
    // TODO: Carregar uma busca igual na caixa de pedidos a API da Jikan para que seja escolhido um anime favorito
    <div class="mb-4">
        <label for="favorite_anime" class="text-md text-gray-700 font-noto-sans block mb-1">
            Anime favorito
        </label>
        <input
            id="favorite_anime"
            type="text"
            name="favorite_anime"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.favorite_anime}
            disabled
        />
    </div>
    <div class="mb-4">
        <label for="requests_total" class="text-md text-gray-700 font-noto-sans block mb-1">
            Quantidade de pedidos feitos
        </label>
        <input
            id="requests_total"
            type="text"
            name="requests_total"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.requests_total}
            disabled
        />
    </div>

    <button type="submit" class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
        Atualizar
    </button>
</form>
