<script>
    export let close = () => {};
    export let pollSelected;

    import { useForm } from "@inertiajs/svelte";
    import { FormField, TextArea, TextInput } from "@/ui/components/private";
    import { pollPermissions } from "@/utils";
    import PollActions from "./actions/PollActions.svelte";

    let can = pollPermissions();

    const normalizeOptions = (options = []) => [
        { uuid: null, option: null, ...options[0] },
        { uuid: null, option: null, ...options[1] },
        { uuid: null, option: null, ...options[2] },
        { uuid: null, option: null, ...options[3] },
    ];

    $: form = useForm({
        status: pollSelected?.status,
        question: pollSelected?.question ?? null,
        expires_at: pollSelected?.expires_at ?? null,
        options: normalizeOptions(pollSelected?.options),
    });

    const submit = (event) => {
        const method = pollSelected ? "patch" : "post";

        const url = pollSelected
            ? `/panel/media/poll/${pollSelected.uuid}`
            : "/panel/media/poll";

        $form.status = event.submitter.value;
        $form[method](url, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="question" label="Título" error={$form.errors.question}>
        <TextArea
            id="question"
            name="question"
            rows="3"
            bind:value={$form.question}
            error={$form.errors.question}
        />
    </FormField>
    <hr class="mb-4 border-gray-300" />
    <FormField for="expires_at" label="A enquete ficará aberta até" error={$form.errors.expires_at}>
        <TextInput
            variant="offcanvas"
            id="expires_at"
            name="expires_at"
            type="datetime-local"
            bind:value={$form.expires_at}
            error={$form.errors.expires_at}
        />
    </FormField>
    <hr class="mb-4 border-gray-300" />
    <FormField for="options_0" label="1º Opção" error={$form.errors["options.0.option"]}>
        <TextInput
            variant="offcanvas"
            id="options_0"
            name="options[0]"
            type="text"
            placeholder="Digite a primeira opção"
            bind:value={$form.options[0].option}
            error={$form.errors["options.0.option"]}
            required
        />
    </FormField>
    <FormField for="options_1" label="2º Opção" error={$form.errors["options.1.option"]}>
        <TextInput
            variant="offcanvas"
            id="options_1"
            name="options[1]"
            type="text"
            placeholder="Digite a segunda opção"
            bind:value={$form.options[1].option}
            error={$form.errors["options.1.option"]}
            required
        />
    </FormField>
    <FormField for="options_2" label="3º Opção" error={$form.errors["options.2.option"]}>
        <TextInput
            variant="offcanvas"
            id="options_2"
            name="options[2]"
            type="text"
            placeholder="Digite a terceira opção"
            bind:value={$form.options[2].option}
            error={$form.errors["options.2.option"]}
            required
        />
    </FormField>
    <FormField for="options_3" label="4º Opção" error={$form.errors["options.3.option"]} spacing="section">
        <TextInput
            variant="offcanvas"
            id="options_3"
            name="options[3]"
            type="text"
            placeholder="Digite a quarta opção"
            bind:value={$form.options[3].option}
            error={$form.errors["options.3.option"]}
            required
        />
    </FormField>
    <PollActions
        status={$form.status}
        can={can}
    />
</form>
