<script>
    export let close = () => {};
    export let activitySelected;

    import { useForm } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        RadioInput,
        TextArea,
        TextInput,
    } from "@/ui/components/private";
    import { activityPermissions } from "@/utils";

    let can = activityPermissions();

    $: form = useForm({
        title: activitySelected?.title ?? null,
        purpose: activitySelected ? (activitySelected.allows_confirmations ? "activity" : "notice") : null,
        limit: activitySelected?.limit ?? null,
        hour: activitySelected?.hour ?? null,
        date: activitySelected?.date ?? null,
        content: activitySelected?.content ?? null,
    });

    const submit = () => {
        const method = activitySelected ? "patch" : "post";
        const url = activitySelected
            ? `/panel/administration/activity/${activitySelected.uuid}`
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
        <div class="mb-1">
            <RadioInput
                id="notice"
                name="purpose"
                value="notice"
                label="Aviso"
                bind:group={$form.purpose}
                error={$form.errors.purpose}
            />
        </div>
        <div>
            <RadioInput
                id="activity"
                name="purpose"
                value="activity"
                label="Atividade"
                bind:group={$form.purpose}
                error={$form.errors.purpose}
            />
        </div>
    </div>
    <FormField for="title" label={$form.purpose === "activity" ? "Título da atividade" : $form.purpose === "notice" ? "Título do aviso" : "Título"} error={$form.errors.title} spacing="compact">
        <TextInput
            variant="offcanvas"
            type="text"
            id="title"
            name="title"
            bind:value={$form.title}
            error={$form.errors.title}
            required
        />
    </FormField>
    <FormField for="limit" label="Data limite" help={$form.purpose === "notice" ? "Data limite para exibição do aviso" : $form.purpose === "activity" ? "Data limite para confirmação da atividade" : null} error={$form.errors.limit} spacing="compact">
        <TextInput
            variant="offcanvas"
            type="date"
            id="limit"
            name="limit"
            bind:value={$form.limit}
            error={$form.errors.limit}
            required
        />
    </FormField>
    {#if $form.purpose === "activity"}
        <div class="grid grid-cols-2 gap-3">
            <FormField for="hour" label="Hora" help="Hora da atividade" error={$form.errors.hour} spacing="compact">
                <TextInput
                    variant="offcanvas"
                    type="time"
                    id="hour"
                    name="hour"
                    bind:value={$form.hour}
                    error={$form.errors.hour}
                    required
                />
            </FormField>
            <FormField for="date" label="Data" help="Data da atividade" error={$form.errors.date} spacing="compact">
                <TextInput
                    variant="offcanvas"
                    type="date"
                    id="date"
                    name="date"
                    bind:value={$form.date}
                    error={$form.errors.date}
                    required
                />
            </FormField>
        </div>
    {/if}
    <FormField for="content" label="Conteúdo" error={$form.errors.content} spacing="compact">
        <TextArea
            variant="offcanvas"
            id="content"
            name="content"
            rows="3"
            bind:value={$form.content}
            error={$form.errors.content}
            required
        />
    </FormField>
    {#if activitySelected ? can.update : can.create}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            {activitySelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
