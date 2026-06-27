<script>
    export let close = () => {};
    export let musicSelected;

    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";
    import { musicPermissions } from "@/utils";

    let can = musicPermissions();

    $: form = useForm({
        _method: "PATCH",
        image: null,
        image_ranking: null,
        type: musicSelected?.type ?? null,
        production: musicSelected?.production ?? null,
        artist: musicSelected?.artist ?? null,
        name: musicSelected?.name ?? null,
    });

    const submit = () => {
        $form.post(`/panel/radio/music/${musicSelected.uuid}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4 grid grid-cols-1 gap-4 px-5 sm:grid-cols-2">
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image"
            src={$form.image ?? musicSelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
        />
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            name="image_ranking"
            src={$form.image_ranking ?? musicSelected?.ranking?.image}
            oninput={(event) => ($form.image_ranking = event.target.files[0])}
        />
    </div>
    <div class="mb-4">
        <label for="music-name" class="text-md text-gray-700 font-noto-sans block mb-1">
            Música
        </label>
        <input
            id="music-name"
            type="text"
            name="name"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.name}
            required
        />
    </div>
    <div class="mb-4">
        <label for="artist" class="text-md text-gray-700 font-noto-sans block mb-1">
            Cantor/Banda
        </label>
        <input
            id="artist"
            type="text"
            name="artist"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.artist}
            required
        />
    </div>
    <div class="mb-4">
        <label for="production" class="text-md text-gray-700 font-noto-sans block mb-1">
            Anime
        </label>
        <input
            id="production"
            type="text"
            name="production"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.production}
            required
        />
    </div>
    <div class="mb-6">
        <label for="type" class="text-md text-gray-700 font-noto-sans block mb-1">
            Tipo
        </label>
        <select
            id="type"
            name="type"
            class="w-full h-10 bg-white font-noto-sans rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.type}
            required
        >
            <option value={null} disabled>
                Selecione uma opção
            </option>
            <option value="OP">
                OP
            </option>
            <option value="ED">
                ED
            </option>
        </select>
    </div>
    {#if can.update}
        <button
            type="submit"
            class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
        >
            Atualizar
        </button>
    {/if}
</form>
