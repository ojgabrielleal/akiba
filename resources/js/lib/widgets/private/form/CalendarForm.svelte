<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        Button,
        FormField,
        SelectInput,
        TextArea,
        TextInput,
    } from "@/lib/components/private";
    import { calendarPermissions } from "@/lib/utils";

    export let close = () => {};
    export let eventSelected;
    export let users = null;

    const can = calendarPermissions();

    $: form = useForm({
        user: eventSelected?.responsible.uuid ?? null,
        hour: eventSelected?.hour ?? null,
        date: eventSelected?.date ?? null,
        content: eventSelected?.content ?? null,
        type: eventSelected?.type ?? null,
    });

    function submit() {
        const method = eventSelected ? "patch" : "post";
        const url = eventSelected
            ? `/panel/administration/calendar/${eventSelected.uuid}`
            : "/panel/administration/calendar";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="user" label="Membro designado" error={$form.errors.user} spacing="compact">
        <SelectInput
            variant="offcanvas"
            id="user"
            name="user"
            bind:value={$form.user}
            error={$form.errors.user}
            required
        >
            <option value="">
                Selecione um membro
            </option>
            {#each users.data as item}
                <option value={item.uuid}>
                    {item.nickname}
                </option>
            {/each}
        </SelectInput>
    </FormField>
    <FormField for="type" label="Tipo do evento" error={$form.errors.type} spacing="compact">
        <SelectInput
            variant="offcanvas"
            id="type"
            name="type"
            bind:value={$form.type}
            error={$form.errors.type}
            required
        >
            <option value="">
                Selecione um tipo
            </option>
            <option value="show">
                Programa
            </option>
            <option value="live">
                Live (Twitch/Kick)
            </option>
            <option value="video">
                Vídeo (Youtube/Facebook/Instagram)
            </option>
            <option value="podcast">
                Podcast
            </option>
        </SelectInput>
    </FormField>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <FormField for="hour" label="Hora" help="Hora do evento" error={$form.errors.hour} spacing="compact">
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
        <FormField for="date" label="Data" help="Data do evento" error={$form.errors.date} spacing="compact">
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
    {#if eventSelected ? can.update : can.create}
        <Button
            type="submit"
            loading={$form.processing}
            variant="secondary"
            shape="pill"
        >
            {eventSelected ? "Atualizar" : "Cadastrar"}
        </Button>
    {/if}
</form>
