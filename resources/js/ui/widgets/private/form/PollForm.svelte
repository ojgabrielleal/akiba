<script>
    export let close = () => {};
    export let identifier;

    import axios from "axios";
    import { useForm } from "@inertiajs/svelte";
    import { pollPermissions } from "@/utils";

    let can = pollPermissions();

    let form = useForm({
        question: null,
        option_one: null,
        option_two: null,
        option_three: null,
        option_four: null,
    });

    if (identifier) {
        axios.get(`/panel/media/poll/${identifier}`)
            .then((response) => {
                const data = response.data.data;

                $form.question = data.question;
                $form.option_one = data.options[0]?.option || null;
                $form.option_two = data.options[1]?.option || null;
                $form.option_three = data.options[2]?.option || null;
                $form.option_four = data.options[3]?.option || null;
            })
            .catch(() => {
                console.error("Error when find poll selected");
                close();
            });
    }

    const submit = () => {
        const method = identifier ? "patch" : "post";
        const url = identifier
            ? `/panel/media/poll/${identifier}`
            : "/panel/media/poll";

        $form[method](url, {
            preserveScroll: true,
            onSuccess: () => close(),
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <div class="mb-4">
        <label for="question" class="text-md text-gray-700 font-noto-sans block mb-1">
            Pergunta
        </label>
        <input
            id="question"
            type="text"
            name="question"
            class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
            bind:value={$form.question}
        />
    </div>
    <div class="px-4 mb-4 rounded-md border border-gray-400">
        <div class="mt-5 mb-4">
            <label for="option_one" class="text-md text-gray-700 font-noto-sans block mb-1">
                1º Opção
            </label>
            <input
                id="option_one"
                name="option_one"
                type="text"
                placeholder="Digite a primeira opção"
                class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                bind:value={$form.option_one}
                required
            />
        </div>
        <div class="mb-4">
            <label for="option_two" class="text-md text-gray-700 font-noto-sans block mb-1">
                2º Opção
            </label>
            <input
                id="option_two"
                name="option_two"
                type="text"
                placeholder="Digite a segunda opção"
                class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                bind:value={$form.option_two}
                required
            />
        </div>
        <div class="mb-4">
            <label for="option_three" class="text-md text-gray-700 font-noto-sans block mb-1">
                3º Opção
            </label>
            <input
                id="option_three"
                name="option_three"
                type="text"
                placeholder="Digite a terceira opção"
                class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                bind:value={$form.option_three}
                required
            />
        </div>
        <div class="mb-6">
            <label for="option_four" class="text-md text-gray-700 font-noto-sans block mb-1">
                4º Opção
            </label>
            <input
                id="option_four"
                name="option_four"
                type="text"
                placeholder="Digite a quarta opção"
                class="w-full h-10 bg-white font-noto-sans text-md rounded-md outline-none pl-4 border border-gray-400"
                bind:value={$form.option_four}
                required
            />
        </div>
    </div>
    {#if can.create || can.update}
        <button
            aria-label=""
            type="submit"
            class="cursor-pointer bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase"
        >
            {identifier ? "Atualizar" : "Cadastrar"}
        </button>
    {/if}
</form>
