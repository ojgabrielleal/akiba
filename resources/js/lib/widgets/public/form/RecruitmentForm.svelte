<script>
    import { useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";
    import { Button, TextArea, TextInput } from "@/lib/components/public";

    const fieldClass = "grid gap-1.5";
    const labelClass = "font-noto-sans text-xs font-black italic uppercase text-neutral-gray";

    $: form = useForm({
        form_type: "recruitment",
        name: "",
        contact: "",
        subject: "Inscrição para equipe",
        payload: {
            role: "",
            nickname: "",
            age: "",
            portfolio: "",
            interview_time: "",
            message: "",
        },
    });

    const submit = () => {
        $form.post("/form-submissions", {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Inscrição enviada");
                $form.reset();
            },
        });
    };
</script>

<form class="grid gap-4" on:submit|preventDefault={submit}>
    <div class={fieldClass}>
        <label class={labelClass} for="role">Que cargo você quer exercer?</label>
        <TextInput id="role" bind:value={$form.payload.role} error={$form.errors["payload.role"]} required />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="name">Qual o seu nome?</label>
        <TextInput id="name" bind:value={$form.name} error={$form.errors.name} required />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="nickname">Qual o seu nick?</label>
        <TextInput id="nickname" bind:value={$form.payload.nickname} error={$form.errors["payload.nickname"]} />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="age">Tem quantos anos?</label>
        <TextInput id="age" type="number" min="10" max="120" bind:value={$form.payload.age} error={$form.errors["payload.age"]} />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="portfolio">Você tem trabalhos que a gente possa ver? Coloca os link aí pa nois!</label>
        <TextArea id="portfolio" class="min-h-20" bind:value={$form.payload.portfolio} error={$form.errors["payload.portfolio"]} resize="none" />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="contact">Coloca um contato pra gente</label>
        <TextInput id="contact" bind:value={$form.contact} error={$form.errors.contact} required />
    </div>

    <div class={fieldClass}>
        <label class={labelClass} for="message">Hora da pré-entrevista, por quê você quer entrar na Akiba?</label>
        <TextArea id="message" class="min-h-36" bind:value={$form.payload.message} error={$form.errors["payload.message"]} required resize="none" />
    </div>

    <div class="flex justify-end pt-1">
        <Button type="submit" loading={$form.processing} disabled={$form.processing}>
            Completar inscrição
        </Button>
    </div>
</form>
