<script>
    import { useForm } from "@inertiajs/svelte";

    $: form = useForm({
        username: null,
        password: null,
    });

    const submit = () => {
        $form.post("/panel/auth");
    };
</script>

<div>
    <div class="flex justify-center mb-4">
        <img
            class="w-60"
            src="/img/brand/logo.webp"
            alt="logo"
        />
    </div>
    <strong class="w-full block mb-4 font-noto-sans font-light text-center text-gray-400">
        Realize o login para acessar o sistema
    </strong>
    <form on:submit|preventDefault={submit}>
        <input
            type="text"
            name="username"
            placeholder="👤 Usuário"
            class="w-full h-14 bg-white font-noto-sans text-md rounded-md outline-none mb-2 pl-4 border border-gray-400"
            bind:value={$form.username}
            required
        />
        <input
            type="password"
            name="password"
            placeholder="🔑 Senha"
            class="w-full h-14 bg-white font-noto-sans text-md rounded-md outline-none mb-4 pl-4 border border-gray-400"
            bind:value={$form.password}
            required
        />
        <button type="submit" class="flex h-14 w-full cursor-pointer items-center justify-center gap-2 rounded-md bg-blue-marinho px-4 py-2 font-noto-sans text-md font-extrabold uppercase italic text-suspense-aurora transition hover:brightness-95">
            Entrar
        </button>
    </form>
</div>
