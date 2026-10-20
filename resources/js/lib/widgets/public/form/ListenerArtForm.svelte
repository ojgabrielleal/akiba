<script>
    import { useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";

    import { publicAnimations } from "@/lib/constants/public/animation";

    export let close = () => {};

    const inputClass = "w-full bg-white font-noto-sans text-md text-black rounded-md outline-none border border-gray-400 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora [[data-public-theme=akiba]_&]:placeholder:text-suspense-aurora/45 [[data-public-theme=night]_&]:placeholder:text-suspense-aurora/45 [[data-public-theme=akiba]_&]:border-suspense-aurora/20 [[data-public-theme=night]_&]:border-suspense-aurora/20";
    const labelClass = "text-md text-gray-700 font-noto-sans block mb-1 [[data-public-theme=akiba]_&]:text-suspense-aurora/75 [[data-public-theme=night]_&]:text-suspense-aurora/75";
    const errorClass = "mt-1 block font-noto-sans text-xs text-red-600 [[data-public-theme=akiba]_&]:text-red-300 [[data-public-theme=night]_&]:text-red-300";
    const form = useForm({
        form_type: "contact",
        subject: "Arte para a galeria do ouvinte",
        name: "",
        contact: "",
        image: null,
        payload: { message: "" },
    });

    function submit() {
        if ($form.processing) return;
        $form.post("/form-submissions", {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                toast.success("Arte enviada para avaliação!");
                $form.reset();
                close();
            },
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-3">
        <label class={labelClass} for="listener-art-name">Seu nome ou nome artístico</label>
        <input id="listener-art-name" type="text" class={`${inputClass} h-10 px-4 focus-visible:border-gray-500`} bind:value={$form.name} maxlength="255" required />
        {#if $form.errors["name"]}<span class={errorClass}>{$form.errors["name"]}</span>{/if}
    </div>
    <div class="mb-3">
        <label class={labelClass} for="listener-art-contact">E-mail ou rede social</label>
        <input id="listener-art-contact" type="text" class={`${inputClass} h-10 px-4 focus-visible:border-gray-500`} bind:value={$form.contact} maxlength="255" required />
        {#if $form.errors["contact"]}<span class={errorClass}>{$form.errors["contact"]}</span>{/if}
    </div>
    <div class="mb-3">
        <label class={labelClass} for="listener-art-message">Sobre a arte (opcional)</label>
        <textarea id="listener-art-message" class={`${inputClass} min-h-28 resize-none px-4 py-3 focus-visible:border-gray-500`} rows="4" bind:value={$form.payload.message} maxlength="5000" placeholder="Nome do personagem, título da arte ou uma breve descrição"></textarea>
        {#if $form.errors["payload.message"]}<span class={errorClass}>{$form.errors["payload.message"]}</span>{/if}
    </div>
    <div class="mb-3">
        <label class={labelClass} for="listener-art-image">Imagem da sua arte</label>
        <input
            id="listener-art-image"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="w-full min-w-0 cursor-pointer font-noto-sans text-sm text-gray-700 outline-none file:mr-3 file:cursor-pointer file:rounded-full file:border-0 file:bg-orange-citric file:px-4 file:py-2 file:font-bold file:text-blue-night focus-visible:brightness-110 [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora"
            on:change={(event) => { $form.image = event.currentTarget.files?.[0] ?? null; }}
            aria-describedby="listener-art-image-help"
            required
        />
        <p id="listener-art-image-help" class={`${labelClass} mt-1 text-xs`}>JPG, PNG ou WebP de até 5 MB.</p>
        {#if $form.errors.image}<span class={errorClass}>{$form.errors.image}</span>{/if}
    </div>
    <div class="flex justify-end pt-1">
        <button type="submit" class={["inline-flex cursor-pointer items-center justify-center rounded-full bg-orange-citric px-7 py-2 font-noto-sans font-extrabold text-blue-night uppercase italic hover:brightness-105 focus-visible:outline-none focus-visible:brightness-110 disabled:cursor-not-allowed disabled:opacity-50", publicAnimations.buttonInteractive]} disabled={$form.processing} aria-busy={$form.processing}>
            {$form.processing ? "Enviando…" : "Enviar"}
        </button>
    </div>
</form>
