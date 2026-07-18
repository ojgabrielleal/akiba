<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
    import { useForm, page } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        SelectInput,
        TextArea,
        TextInput,
    } from "@/ui/components/private";
    import { calendarPermissions } from "@/utils";

    let { users } = $page.props;

    let can = calendarPermissions();

    $: form = useForm({
        user: null,
        hour: null,
        date: null,
        content: null,
        type: null,
    });

    $: if (identifier) {
        axios.get(`/panel/administration/calendar/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.user = data.responsible.uuid;
                $form.hour = data.hour;
                $form.date = data.date;
                $form.content = data.content;
                $form.type = data.type;
            })
            .catch(() => {
                console.error("Error when find calendar");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/administration/calendar/${identifier}`
            : "/panel/administration/calendar";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="user" label="Membro designado" error={$form.errors.user}>
        <SelectInput
            variant="offcanvas"
            id="user"
            name="user"
            bind:value={$form.user}
            error={$form.errors.user}
            required
        >
            {#each users.data as item}
                <option value={item.uuid}>
                    {item.nickname}
                </option>
            {/each}
        </SelectInput>
    </FormField>
    <FormField for="type" label="Tipo do evento" error={$form.errors.type}>
        <SelectInput
            variant="offcanvas"
            id="type"
            name="type"
            bind:value={$form.type}
            error={$form.errors.type}
            required
        >
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
    <div class="grid grid-cols-2 gap-3">
        <FormField for="hour" label="Hora" help="Hora do evento" error={$form.errors.hour}>
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
        <FormField for="date" label="Data" help="Data do evento" error={$form.errors.date}>
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
