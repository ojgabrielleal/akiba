<script>
    import { useForm } from "@inertiajs/svelte";
    import { Button, FormField, TextInput } from "@/lib/components/private";

    const form = useForm({
        username: null,
        password: null,
    });

    const submit = () => {
        $form.post("/panel/auth");
    };
</script>

<div class="min-w-0 w-full">
    <div class="mb-7 flex justify-center">
        <img
            class="w-56 max-w-full"
            src="/img/brand/logo.webp"
            alt="Akiba"
        />
    </div>
    <div class="mb-6 text-center font-noto-sans">
        <p class="text-sm font-black uppercase tracking-[0.18em] text-orange-amber">
            Painel Akiba
        </p>
        <h2 class="mt-2 text-2xl font-black uppercase italic text-blue-marinho">
            Bem-vindo de volta
        </h2>
        <p class="mt-2 text-sm font-medium text-blue-ocean/70">
            Entre para acessar a administração da estação.
        </p>
    </div>
    <form class="space-y-4" on:submit|preventDefault={submit}>
        <FormField for="username" label="Usuário" error={$form.errors.username} spacing="compact">
            <div class="relative">
                <img
                    src="/svg/profile.svg"
                    alt=""
                    aria-hidden="true"
                    class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 opacity-70 filter-blue-marinho"
                />
                <TextInput
                    id="username"
                    type="text"
                    name="username"
                    autocomplete="username"
                    placeholder="Seu usuário"
                    class="h-13 pl-12"
                    bind:value={$form.username}
                    error={$form.errors.username}
                    required
                />
            </div>
        </FormField>
        <FormField for="password" label="Senha" error={$form.errors.password} spacing="compact">
            <div class="relative">
                <img
                    src="/svg/key.svg"
                    alt=""
                    aria-hidden="true"
                    class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 opacity-70 filter-blue-marinho"
                />
                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="Sua senha"
                    class="h-13 pl-12"
                    bind:value={$form.password}
                    error={$form.errors.password}
                    required
                />
            </div>
        </FormField>
        <Button
            type="submit"
            variant="accent"
            shape="pill"
            loading={$form.processing}
            class="h-13 w-full shadow-lg shadow-orange-amber/20"
        >
            Entrar
        </Button>
    </form>
</div>
