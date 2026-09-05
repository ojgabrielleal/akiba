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
    <div class="mb-4 flex justify-center">
        <img
            class="w-56 max-w-full"
            src="/img/brand/logo.webp"
            alt="Akiba"
        />
    </div>
    <p class="mb-5 text-center font-noto-sans text-md text-neutral-gray">
        Faça login para acessar o sistema
    </p>
    <form class="space-y-3" on:submit|preventDefault={submit}>
        <FormField for="username" error={$form.errors.username} spacing="compact">
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
                    placeholder="Digite seu usuário"
                    class="h-13 pl-12"
                    bind:value={$form.username}
                    error={$form.errors.username}
                    required
                />
            </div>
        </FormField>
        <FormField for="password" error={$form.errors.password} spacing="compact">
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
                    placeholder="Digite sua senha"
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
