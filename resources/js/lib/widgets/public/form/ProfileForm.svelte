<script>
    import { useForm } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        TextArea,
        TextInput,
    } from "@/lib/components/public";
    import { OAuthAction, rememberOAuthAction } from "@/lib/utils";

    export let profile;
    export let close = () => {};

    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil";

    const syncDiscord = () => {
        rememberOAuthAction(OAuthAction.OPEN_PROFILE);
        window.location.assign("/oauth/discord/redirect");
    };

    const form = useForm({
        nickname: profile?.nickname ?? "",
        birth_date: profile?.birth_date ?? "",
        address: profile?.address ?? "",
        bio: profile?.bio ?? "",
    });

    const submit = () => {
        $form.patch("/site/profile", {
            preserveScroll: true,
            onSuccess: close,
        });
    };
</script>

<form class="space-y-4" on:submit|preventDefault={submit}>
    <div class="mb-5 flex items-center gap-4">
        <div class="size-16 shrink-0 overflow-hidden rounded-full border-2 border-blue-skywave/20 bg-neutral-white">
            <img
                src={avatar}
                alt={nickname}
                class="h-full w-full object-cover object-top"
            />
        </div>
        <div class="min-w-0">
            <p class="truncate font-noto-sans text-lg font-extrabold text-blue-night">
                {nickname}
            </p>
            <p class="truncate font-noto-sans text-xs text-blue-night/50">
                @{profile?.username}
            </p>
        </div>
        <Button
            type="button"
            variant="secondary"
            size="sm"
            shape="pill"
            class="ml-auto shrink-0"
            on:click={syncDiscord}
        >
            <img
                src="/svg/discord.svg"
                alt=""
                aria-hidden="true"
                class="size-4 filter-suspense-aurora"
            />
            Ressincronizar
        </Button>
    </div>

    <div class="grid gap-4">
        <FormField
            for="oauth-nickname"
            label="Apelido"
            labelVariant="dark"
            spacing="none"
            error={$form.errors.nickname}
            required
        >
            <TextInput
                id="oauth-nickname"
                name="nickname"
                variant="profile"
                bind:value={$form.nickname}
                error={$form.errors.nickname}
                required
            />
        </FormField>

        <FormField
            for="oauth-birth-date"
            label="Data de nascimento"
            labelVariant="dark"
            spacing="none"
            error={$form.errors.birth_date}
            required
        >
            <TextInput
                id="oauth-birth-date"
                name="birth_date"
                type="date"
                variant="profile"
                bind:value={$form.birth_date}
                error={$form.errors.birth_date}
                required
            />
        </FormField>

        <FormField
            for="oauth-address"
            label="Cidade e estado"
            labelVariant="dark"
            spacing="none"
            error={$form.errors.address}
            required
        >
            <TextInput
                id="oauth-address"
                name="address"
                variant="profile"
                bind:value={$form.address}
                error={$form.errors.address}
                placeholder="Ex.: Salto - SP"
                required
            />
        </FormField>

        <FormField
            for="oauth-bio"
            label="Sobre você"
            labelVariant="dark"
            spacing="none"
            error={$form.errors.bio}
        >
            <TextArea
                id="oauth-bio"
                name="bio"
                variant="profile"
                resize="none"
                bind:value={$form.bio}
                error={$form.errors.bio}
                maxlength="500"
                placeholder="Conte um pouco sobre você"
            />
        </FormField>
    </div>

    <div class="flex justify-end pt-2">
        <Button
            type="submit"
            shape="pill"
            loading={$form.processing}
            disabled={$form.processing}
        >
            Salvar perfil
        </Button>
    </div>
</form>
