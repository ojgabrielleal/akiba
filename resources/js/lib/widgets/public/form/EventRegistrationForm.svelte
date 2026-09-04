<script>
    import { useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";

    export let event = {};
    export let close = () => {};

    const fieldClass = "mb-3";
    const inputClass = "w-full bg-white font-noto-sans text-md text-black rounded-md outline-none border border-gray-400";
    const labelClass = "text-md text-gray-700 font-noto-sans block mb-1";

    $: form = useForm({
        form_type: "event_registration",
        subject: "Informações de evento",
        payload: {
            event_uuid: event?.uuid ?? "",
            event_title: event?.title ?? "",
            event_name: event?.title ?? "",
            city: "",
            state: "",
            social_links: "",
        },
    });

    $: if ($form.payload.state) {
        $form.payload.state = $form.payload.state.replace(/[^a-zA-Z]/g, "").slice(0, 2).toUpperCase();
    }

    $: if ($form.payload.event_name && $form.payload.event_title !== $form.payload.event_name) {
        $form.payload.event_title = $form.payload.event_name;
    }

    const submit = () => {
        $form.post("/form-submissions", {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Cadastro enviado");
                $form.reset();
                close();
            },
        });
    };
</script>

<form novalidate on:submit|preventDefault={submit}>
    <div class={fieldClass}>
        <label class={labelClass} for="event-registration-event-name">Nome do evento</label>
        <input
            id="event-registration-event-name"
            type="text"
            name="event_name"
            class={`${inputClass} h-10 pl-4`}
            bind:value={$form.payload.event_name}
            required
        />
        {#if $form.errors["payload.event_name"]}
            <span class="mt-1 block font-noto-sans text-xs text-red-600">{$form.errors["payload.event_name"]}</span>
        {/if}
    </div>

    <div class="mb-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_6rem]">
        <div class={fieldClass}>
            <label class={labelClass} for="event-registration-city">Cidade do evento</label>
            <input
                id="event-registration-city"
                type="text"
                name="city"
                class={`${inputClass} h-10 pl-4`}
                autocomplete="address-level2"
                bind:value={$form.payload.city}
                required
            />
            {#if $form.errors["payload.city"]}
                <span class="mt-1 block font-noto-sans text-xs text-red-600">{$form.errors["payload.city"]}</span>
            {/if}
        </div>

        <div class={fieldClass}>
            <label class={labelClass} for="event-registration-state">UF</label>
            <input
                id="event-registration-state"
                type="text"
                name="state"
                class={`${inputClass} h-10 px-4 uppercase`}
                autocomplete="address-level1"
                maxlength="2"
                placeholder="SP"
                bind:value={$form.payload.state}
                required
            />
            {#if $form.errors["payload.state"]}
                <span class="mt-1 block font-noto-sans text-xs text-red-600">{$form.errors["payload.state"]}</span>
            {/if}
        </div>
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="event-registration-social-links">Links do evento</label>
        <textarea
            id="event-registration-social-links"
            name="social_links"
            rows="4"
            class={`${inputClass} min-h-28 resize-none px-4 py-3`}
            placeholder="Instagram, TikTok, X, site oficial..."
            bind:value={$form.payload.social_links}
            required
        ></textarea>
        {#if $form.errors["payload.social_links"]}
            <span class="mt-1 block font-noto-sans text-xs text-red-600">{$form.errors["payload.social_links"]}</span>
        {/if}
    </div>

    <div class="flex justify-end pt-1">
        <button
            type="submit"
            class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full bg-blue-ocean px-7 py-2 font-noto-sans font-extrabold uppercase italic text-suspense-aurora transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50"
            disabled={$form.processing}
            aria-busy={$form.processing}
        >
            {#if $form.processing}
                <span class="size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
            {/if}
            Enviar
        </button>
    </div>
</form>
