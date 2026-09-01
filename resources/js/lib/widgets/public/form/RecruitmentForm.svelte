<script>
    import { useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";
    import { Button, SelectInput, TextArea, TextInput } from "@/lib/components/public";

    const fieldClass = "grid gap-1.5";
    const inputClass = "public-recruitment-input border-0 focus:border-transparent";
    const labelClass = "public-recruitment-label font-noto-sans text-xs font-black italic uppercase text-suspense-aurora/70";
    const roleOptions = [
        "Locutor",
        "Redator",
        "Editor",
        "Podcaster",
        "Mídias Sociais / Marketing",
    ];

    $: form = useForm({
        form_type: "recruitment",
        name: "",
        contact: "",
        subject: "Inscrição para equipe",
        payload: {
            role: "",
            nickname: "",
            whatsapp: "",
            age: "",
            portfolio: "",
            interview_time: "",
            message: "",
        },
    });

    $: if ($form.payload.whatsapp) {
        $form.payload.whatsapp = $form.payload.whatsapp.replace(/\D/g, "").slice(0, 11);
    }

    const submit = () => {
        $form.contact = $form.payload.whatsapp;

        $form.post("/form-submissions", {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Inscrição enviada");
                $form.reset();
            },
        });
    };

</script>

<form class="recruitment-form grid gap-4" on:submit|preventDefault={submit}>
    <div class={fieldClass}>
        <label class={labelClass} for="role">Que cargo você quer exercer?</label>
        <SelectInput id="role" variant="dark" class={inputClass} bind:value={$form.payload.role} error={$form.errors["payload.role"]} required>
            <option value="" disabled>Selecione um cargo</option>
            {#each roleOptions as roleOption}
                <option value={roleOption}>{roleOption}</option>
            {/each}
        </SelectInput>
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="name">Qual o seu nome?</label>
        <TextInput id="name" variant="dark" class={inputClass} bind:value={$form.name} error={$form.errors.name} required />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="nickname">Qual o seu nick?</label>
        <TextInput id="nickname" variant="dark" class={inputClass} bind:value={$form.payload.nickname} error={$form.errors["payload.nickname"]} />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="whatsapp">Coloca um whatsapp ai pra gente</label>
        <TextInput
            id="whatsapp"
            variant="dark"
            class={inputClass}
            type="tel"
            inputmode="tel"
            autocomplete="tel"
            placeholder="00 00000-0000"
            pattern="\d{10,11}"
            maxlength="11"
            bind:value={$form.payload.whatsapp}
            error={$form.errors["payload.whatsapp"] ?? $form.errors.contact}
            required
        />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="age">Tem quantos anos?</label>
        <TextInput id="age" variant="dark" class={inputClass} type="number" min="16" max="120" bind:value={$form.payload.age} error={$form.errors["payload.age"]} />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="portfolio">Você tem trabalhos que a gente possa ver? Coloca os link aí pa nois!</label>
        <TextArea id="portfolio" variant="dark" class={`min-h-20 ${inputClass}`} bind:value={$form.payload.portfolio} error={$form.errors["payload.portfolio"]} resize="none" />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="message">Hora da pré-entrevista, por quê você quer entrar na Akiba?</label>
        <TextArea id="message" variant="dark" class={`min-h-36 ${inputClass}`} bind:value={$form.payload.message} error={$form.errors["payload.message"]} required resize="none" />
    </div>

    <div class="flex justify-end pt-1">
        <Button type="submit" loading={$form.processing} disabled={$form.processing}>
            <img
                src="/svg/telegram.svg"
                alt=""
                aria-hidden="true"
                class="w-6 filter-blue-marinho"
            />
            <span class="text-[#000036]">Completar inscrição</span>
        </Button>
    </div>
</form>
