<script>
    export let close = () => {};
    export let gallerySelected;

    import { useForm } from "@inertiajs/svelte";
    import { Preview } from "@/ui/components/private";
    import { listenerGalleryPermissions } from "@/utils";

    let can = listenerGalleryPermissions();

    $: form = useForm({
        _method: gallerySelected ? "PATCH" : "POST",
        image: null,
        caption: gallerySelected?.caption ?? null,
        listener_name: gallerySelected?.listener_name ?? null,
    });

    const submit = () => {
        const url = gallerySelected
            ? `/panel/media/listener-gallery/${gallerySelected.uuid}`
            : "/panel/media/listener-gallery";

        $form.post(url, {
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
    <div class="mb-4">
        <Preview
            size="profile"
            tone="muted"
            color="muted"
            name="image"
            src={gallerySelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!gallerySelected}
        />
    </div>
    <div class="mb-4">
        <label for="listener_name" class="text-md text-gray-700 font-noto-sans block mb-1">
            Nome do ouvinte
        </label>
        <input
            id="listener_name"
            type="text"
            name="listener_name"
            maxlength="255"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none px-4 border border-gray-400"
            bind:value={$form.listener_name}
        />
    </div>
    <div class="mb-4">
        <label for="caption" class="text-md text-gray-700 font-noto-sans block mb-1">
            Legenda
        </label>
        <textarea
            id="caption"
            name="caption"
            rows="4"
            maxlength="255"
            class="w-full bg-white font-noto-sans text-md text-black rounded-md outline-none p-4 border border-gray-400 resize-none"
            bind:value={$form.caption}
        ></textarea>
    </div>
    {#if (gallerySelected && can.update) || (!gallerySelected && can.create)}
        <button
            type="submit"
            disabled={$form.processing}
            class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean disabled:cursor-not-allowed disabled:opacity-50"
        >
            {gallerySelected ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
