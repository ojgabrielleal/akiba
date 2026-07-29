<script>
    import { useForm } from "@inertiajs/svelte";
    import { Button } from "@/lib/components/private";

    $: form = useForm({
        username: null,
        password: null,
    });

    const submit = () => {
        $form.post("/panel/auth");
    };
</script>

<div class="min-w-0 w-full">
    <div class="flex justify-center mb-4">
        <img
            class="w-60 max-w-full"
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
        <Button
            type="submit"
            variant="secondary"
            shape="pill"
            loading={$form.processing}
            class="h-14 w-full"
        >
            Entrar
        </Button>
    </form>
</div>
