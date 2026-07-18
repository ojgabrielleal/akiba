<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
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
        title: null,
        purpose: null,
        limit: null,
        hour: null,
        date: null,
        content: null,
    });

    if (identifier) {
        axios.get(`/panel/administration/activity/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.title = data.title;
                $form.purpose = data.allows_confirmations ? "activity" : "notice";
                $form.limit = data.limit;
                $form.hour = data.hour;
                $form.date = data.date;
                $form.content = data.content;
            })
            .catch(() => {
                console.error("Error when find activity");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/administration/activity/${identifier}`
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
    <FormField for="title" label={$form.purpose === "activity" ? "Título da atividade" : $form.purpose === "notice" ? "Título do aviso" : "Título"} error={$form.errors.title}>
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
    <FormField for="limit" label="Data limite" help={$form.purpose === "notice" ? "Data limite para exibição do aviso" : $form.purpose === "activity" ? "Data limite para confirmação da atividade" : null} error={$form.errors.limit}>
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
            <FormField for="hour" label="Hora" help="Hora da atividade" error={$form.errors.hour}>
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
            <FormField for="date" label="Data" help="Data da atividade" error={$form.errors.date}>
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
    <FormField for="content" label="Conteúdo" error={$form.errors.content}>
        <TextArea
            id="content"
            name="content"
            rows="3"
            bind:value={$form.content}
            error={$form.errors.content}
            required
        />
    </FormField>
    {#if can.create || can.update}
        <Button
            type="submit"
            size="lg"
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
