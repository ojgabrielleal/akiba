<script>
    import { router, useForm } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        TextArea,
        TextInput,
        Tooltip,
    } from "@/lib/components/public";
    import {
        consumePendingOAuthAction,
        dispatchOAuthAction,
        OAuthAction,
        rememberOAuthAction,
    } from "@/lib/utils";

    export let profile;
    export let close = () => {};
    export let internal = false;

    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil";

    $: provider = profile?.provider || "google";
    $: providerIcon = provider === "discord" ? "/svg/discord.svg" : "/svg/google.svg";
    $: providerIconClass = "filter-suspense-aurora";
    $: endpoint = internal ? "/site/member-profile" : "/site/profile";
    const avatarSizeClass = "size-18";

    const syncProvider = () => {
        rememberOAuthAction(OAuthAction.OPEN_PROFILE);
        window.location.assign(`/oauth/${provider}/redirect`);
    };

    const closeAndResumePendingAction = () => {
        close();

        const pendingAction = consumePendingOAuthAction();

        if (pendingAction && pendingAction !== OAuthAction.OPEN_PROFILE) {
            setTimeout(() => dispatchOAuthAction(pendingAction));
        }
    };

    const form = useForm({
        _method: "PATCH",
        avatar: null,
        nickname: profile?.nickname ?? "",
        birth_date: profile?.birth_date ?? "",
        address: profile?.address ?? "",
        city: profile?.city ?? "",
        state: profile?.state ?? "",
        country: profile?.country ?? "",
        bio: internal ? profile?.bio ?? "" : "",
    });

    let avatarPreview = avatar;
    $: if (!$form.avatar) avatarPreview = avatar;

    const submit = () => {
        if (internal) {
            $form.post(endpoint, {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: closeAndResumePendingAction,
            });

            return;
        }

        $form.patch(endpoint, {
            preserveScroll: true,
            onSuccess: closeAndResumePendingAction,
        });
    };

    const changeAvatar = (event) => {
        if (!internal) return;

        $form.avatar = event.target.files?.[0] ?? null;
        avatarPreview = $form.avatar instanceof File
            ? URL.createObjectURL($form.avatar)
            : avatar;
    };

    const logout = () => {
        router.post(internal ? "/site/member-logout" : "/oauth/logout", {}, {
            preserveScroll: false,
        });
    };
</script>

<form class="space-y-4" on:submit|preventDefault={submit}>
    <div class="mb-5 flex items-center gap-3 sm:gap-4">
        {#if internal}
            <label
                for="member-avatar"
                class={[
                    "group/avatar relative shrink-0 cursor-pointer overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow focus-within:ring-2 focus-within:ring-orange-amber",
                    avatarSizeClass,
                ]}
            >
                <img
                    src={avatarPreview}
                    alt={nickname}
                    class="h-full w-full object-cover object-top scale-125"
                />
                <span class="absolute inset-0 grid place-items-center bg-blue-night/55 text-[0.6rem] font-black uppercase italic text-suspense-aurora opacity-0 transition group-hover/avatar:opacity-100 group-focus-within/avatar:opacity-100">
                    Alterar
                </span>
                <input
                    id="member-avatar"
                    name="avatar"
                    type="file"
                    accept="image/*"
                    class="sr-only"
                    on:change={changeAvatar}
                />
            </label>
        {:else}
            <div class={[
                "shrink-0 overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow",
                avatarSizeClass,
            ]}>
                <img
                    src={avatar}
                    alt={nickname}
                    class="h-full w-full object-cover object-top scale-125"
                />
            </div>
        {/if}
        <div class="min-w-0 flex-1">
            <p class="truncate font-noto-sans text-lg font-extrabold text-blue-night">
                {nickname}
            </p>
            <p class="truncate font-noto-sans text-xs text-blue-night/50">
                {internal ? "Membro interno" : `@${profile?.username}`}
            </p>
        </div>
        {#if !internal}
            <Button
                type="button"
                variant="secondary"
                size="sm"
                shape="pill"
                class="shrink-0"
                on:click={syncProvider}
            >
                <img
                    src={providerIcon}
                    alt=""
                    aria-hidden="true"
                    class={["size-4", providerIconClass]}
                />
                Ressincronizar
            </Button>
        {/if}
        <Tooltip position="left">
            <button
                type="button"
                class="group/logout flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-transparent transition hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                aria-label="Sair da conta"
                on:click={logout}
            >
                <img
                    src="/svg/logout.svg"
                    alt=""
                    aria-hidden="true"
                    class="mr-[0.1rem] size-4 filter-neutral-gray transition group-hover/logout:filter-orange-amber group-focus-visible/logout:filter-orange-amber"
                />
            </button>
            <span slot="content">Sair da conta</span>
        </Tooltip>
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

        {#if internal}
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField
                    for="member-city"
                    label="Cidade"
                    labelVariant="dark"
                    spacing="none"
                    error={$form.errors.city}
                    required
                >
                    <TextInput
                        id="member-city"
                        name="city"
                        variant="profile"
                        bind:value={$form.city}
                        error={$form.errors.city}
                        required
                    />
                </FormField>

                <FormField
                    for="member-state"
                    label="Estado"
                    labelVariant="dark"
                    spacing="none"
                    error={$form.errors.state}
                    required
                >
                    <TextInput
                        id="member-state"
                        name="state"
                        variant="profile"
                        bind:value={$form.state}
                        error={$form.errors.state}
                        required
                    />
                </FormField>
            </div>

            <FormField
                for="member-country"
                label="País"
                labelVariant="dark"
                spacing="none"
                error={$form.errors.country}
                required
            >
                <TextInput
                    id="member-country"
                    name="country"
                    variant="profile"
                    bind:value={$form.country}
                    error={$form.errors.country}
                    required
                />
            </FormField>
        {:else}
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
        {/if}

        {#if internal}
            <FormField
                for="member-bio"
                label="Sobre você"
                labelVariant="dark"
                spacing="none"
                error={$form.errors.bio}
            >
                <TextArea
                    id="member-bio"
                    name="bio"
                    variant="profile"
                    resize="none"
                    bind:value={$form.bio}
                    error={$form.errors.bio}
                    maxlength="500"
                    placeholder="Conte um pouco sobre você"
                />
            </FormField>
        {/if}
    </div>

    <div class="flex flex-wrap justify-end gap-2 pt-2">
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
