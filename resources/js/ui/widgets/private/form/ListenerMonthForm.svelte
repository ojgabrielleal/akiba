<script>
    export let close = () => {};
    export let listenerMonthFound;

    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";

    let form = useForm({
        avatar: null,
        birthday: listenerMonthFound?.birthday ?? null,
    });

    const submit = () => {
        $form.post("/panel/radio/listener-month", {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <Preview
            size="profile"
            tone="muted"
            color="muted"
            name="avatar"
            oninput={(event) => ($form.avatar = event.target.files[0])}
            required
        />
    </div>
    <div class="mb-4">
        <label for="address" class="text-md text-gray-700 font-noto-sans block mb-1">
            Aniversário
        </label>
        <input
            id="birthday"
            type="date"
            name="birthday"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            bind:value={$form.birthday}
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
        value={listenerMonthFound?.name}
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
            value={listenerMonthFound?.address}
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
            value={listenerMonthFound?.favorite_program?.name}
            disabled
        />
    </div>
    <div class="mb-4">
        <label for="favorite_anime" class="text-md text-gray-700 font-noto-sans block mb-1">
            Anime favorito
        </label>
        <input
            id="favorite_anime"
            type="text"
            name="favorite_anime"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400 disabled:bg-gray-200 disabled:cursor-not-allowed"
            value={listenerMonthFound?.favorite_music?.production}
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
            value={listenerMonthFound?.requests_total}
            disabled
        />
    </div>
    <button type="submit" class="cursor-pointer bg-blue-marinho px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
        Atualizar
    </button>
</form>
