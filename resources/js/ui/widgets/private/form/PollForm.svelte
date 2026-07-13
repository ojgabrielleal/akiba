<script>
    export let close = () => {};
    export let pollSelected;

    import { useForm } from "@inertiajs/svelte";
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
    <div class="mb-4">
        <label for="question" class="text-md text-gray-700 font-noto-sans block mb-1">
            Título
        </label>
        <textarea
            id="question"
            name="question"
            rows="3"
            class="w-full bg-white font-noto-sans text-md text-black rounded-md outline-none p-4 border border-gray-400 resize-none"
            bind:value={$form.question}
        ></textarea>
    </div>
    <hr class="mb-4 border-gray-300" />
    <div class="mb-4">
        <label for="expires_at" class="text-md text-gray-700 font-noto-sans block mb-1">
            A enquete ficará aberta até
        </label>
        <input
            id="expires_at"
            name="expires_at"
            type="datetime-local"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.expires_at}
        />
    </div>
    <hr class="mb-4 border-gray-300" />
    <div class="mb-4">
        <label for="options_0" class="text-md text-gray-700 font-noto-sans block mb-1">
            1º Opção
        </label>
        <input
            id="options_0"
            name="options[0]"
            type="text"
            placeholder="Digite a primeira opção"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.options[0].option}
            required
        />
    </div>
    <div class="mb-4">
        <label for="options_1" class="text-md text-gray-700 font-noto-sans block mb-1">
            2º Opção
        </label>
        <input
            id="options_1"
            name="options[1]"
            type="text"
            placeholder="Digite a segunda opção"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.options[1].option}
            required
        />
    </div>
    <div class="mb-4">
        <label for="options_2" class="text-md text-gray-700 font-noto-sans block mb-1">
            3º Opção
        </label>
        <input
            id="options_2"
            name="options[2]"
            type="text"
            placeholder="Digite a terceira opção"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.options[2].option}
            required
        />
    </div>
    <div class="mb-6">
        <label for="options_3" class="text-md text-gray-700 font-noto-sans block mb-1">
            4º Opção
        </label>
        <input
            id="options_3"
            name="options[3]"
            type="text"
            placeholder="Digite a quarta opção"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.options[3].option}
            required
        />
    </div>
    <PollActions
        status={$form.status}
        can={can}
    />
</form>
