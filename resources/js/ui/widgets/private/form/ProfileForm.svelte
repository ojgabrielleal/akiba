<script>
    import { useForm, page } from "@inertiajs/svelte";
    import {
        Badge,
        Button,
        FormField,
        Preview,
        RadioInput,
        Section,
        SelectInput,
        TextArea,
        TextInput,
    } from "@/ui/components/private/";
    import { profilePermissions } from "@/utils";
    import { userPreferences } from "@/data";

    $: ({ profile } = $page.props);

    let can = profilePermissions();

    const socialPlatforms = [
        "Facebook",
        "Instagram",
        "Threads",
        "Twitter",
        "Bluesky",
        "Discord",
        "YouTube",
        "MyAnimeList",
    ];

    const normalizeSocials = (socials = []) => socialPlatforms.map((name) => {
        const social = socials.find((item) => item.name === name);

        return social ?? { uuid: null, name, url: null };
    });

    const resolvePreferenceValue = (content) => {
        if (!content || content === "#") return "";

        const normalizedContent = content.toLocaleLowerCase("pt-BR");
        const preference = userPreferences.find((option) =>
            option.value === content || option.name.toLocaleLowerCase("pt-BR") === normalizedContent
        );

        return preference?.value ?? "";
    };

    const normalizePreferences = (preferences = {}) => ({
        likes: (preferences.likes ?? []).map((preference) => ({
            ...preference,
            content: resolvePreferenceValue(preference.content),
        })),
        unlikes: (preferences.unlikes ?? []).map((preference) => ({
            ...preference,
            content: resolvePreferenceValue(preference.content),
        })),
    });

    $: form = useForm({
        _method: "PATCH",
        name: profile?.data.name ?? null,
        nickname: profile?.data.nickname ?? null,
        gender: profile?.data.gender ?? null,
        avatar: profile?.data.avatar ?? null,
        birth_date: profile?.data.birth_date ?? null,
        city: profile?.data.city ?? null,
        state: profile?.data.state ?? null,
        country: profile?.data.country ?? null,
        bibliography: profile?.data.bibliography ?? null,
        socials: normalizeSocials(profile?.data.socials),
        preferences: normalizePreferences(profile?.data.preferences),
    });

    const submit = () => {
        $form.post(`/panel/profile/${profile.data.uuid}`, {
            preserveScroll: true,
            forceFormData: true,
        });
    };
</script>

<form on:submit|preventDefault={submit}>
    <Section title="Quem sou eu?">
        <div class="overflow-hidden rounded-xl border border-blue-skywave/30 bg-gradient-blue-ocean-skywave p-4 sm:p-6">
            <div class="grid grid-cols-1 gap-7 xl:grid-cols-[17rem_1fr] xl:items-center">
                <div class="mx-auto w-full max-w-68 xl:mx-0">
                    <div class="relative rounded-xl bg-blue-marinho/45 p-3 shadow-xl">
                        <Preview
                            name="avatar"
                            size="profile"
                            tone="muted"
                            color="muted"
                            src={$form.avatar}
                            oninput={(event) => ($form.avatar = event.target.files[0])}
                        />
                        <Badge variant="accent" size="sm" class="absolute bottom-5 left-5">
                            304 × 400
                        </Badge>
                    </div>
                    <p class="mt-2 text-center font-noto-sans text-xs text-suspense-aurora/65">
                        Clique na imagem para escolher um novo avatar
                    </p>
                </div>

                <div class="min-w-0">
                    <div class="mb-6">
                        <p class="font-noto-sans text-xs font-extrabold uppercase tracking-[0.2em] text-orange-citric">
                            Perfil da equipe
                        </p>
                        <h2 class="truncate font-noto-sans text-3xl font-black uppercase italic text-suspense-aurora sm:text-4xl">
                            {$form.nickname || "Seu nome no ar"}
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <FormField for="name" label="Nome completo" labelVariant="editorial" spacing="none" error={$form.errors.name}>
                            <TextInput
                                id="name"
                                type="text"
                                name="name"
                                variant="profile"
                                bind:value={$form.name}
                                error={$form.errors.name}
                                required
                            />
                        </FormField>
                        <FormField for="nickname" label="Apelido" labelVariant="editorial" spacing="none" error={$form.errors.nickname}>
                            <TextInput
                                id="nickname"
                                type="text"
                                name="nickname"
                                variant="profile"
                                bind:value={$form.nickname}
                                error={$form.errors.nickname}
                                required
                            />
                        </FormField>
                        <FormField for="birth_date" label="Nascimento" labelVariant="editorial" spacing="none" error={$form.errors.birth_date}>
                            <TextInput
                                id="birth_date"
                                type="date"
                                name="birth_date"
                                variant="profile"
                                bind:value={$form.birth_date}
                                error={$form.errors.birth_date}
                                required
                            />
                        </FormField>
                        <FormField for="gender-male" label="Gênero" labelVariant="editorial" spacing="none" error={$form.errors.gender}>
                            <div class="flex h-12 items-center gap-6 rounded-md bg-suspense-aurora px-4">
                                <RadioInput
                                    id="gender-male"
                                    name="gender"
                                    value="male"
                                    label="Masculino"
                                    bind:group={$form.gender}
                                    error={$form.errors.gender}
                                    required
                                />
                                <RadioInput
                                    id="gender-female"
                                    name="gender"
                                    value="female"
                                    label="Feminino"
                                    bind:group={$form.gender}
                                    error={$form.errors.gender}
                                    required
                                />
                            </div>
                        </FormField>
                    </div>
                </div>
            </div>
        </div>
    </Section>

    <Section title="Onde é que eu tô?">
        <div class="rounded-xl border border-suspense-aurora/10 bg-blue-ocean/25 p-5 sm:p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <FormField for="city" label="Cidade" labelVariant="editorial" spacing="none" error={$form.errors.city}>
                    <TextInput
                        id="city"
                        type="text"
                        name="city"
                        variant="profile"
                        bind:value={$form.city}
                        error={$form.errors.city}
                        required
                    />
                </FormField>
                <FormField for="state" label="Estado" labelVariant="editorial" spacing="none" error={$form.errors.state}>
                    <TextInput
                        id="state"
                        type="text"
                        name="state"
                        variant="profile"
                        bind:value={$form.state}
                        error={$form.errors.state}
                        required
                    />
                </FormField>
                <FormField for="country" label="País" labelVariant="editorial" spacing="none" error={$form.errors.country}>
                    <TextInput
                        id="country"
                        type="text"
                        name="country"
                        variant="profile"
                        bind:value={$form.country}
                        error={$form.errors.country}
                        required
                    />
                </FormField>
            </div>
        </div>
    </Section>

    <Section title="Sobre mim">
        <div class="rounded-xl border border-suspense-aurora/10 bg-blue-ocean/25 p-5 sm:p-6">
            <FormField for="bibliography" label="Minha história com a Akiba" labelVariant="editorial" spacing="none" error={$form.errors.bibliography}>
                <TextArea
                    id="bibliography"
                    name="bibliography"
                    rows="7"
                    variant="profile"
                    bind:value={$form.bibliography}
                    error={$form.errors.bibliography}
                    required
                />
            </FormField>
        </div>
    </Section>

    <Section title="Onde me encontrar">
        <div class="rounded-xl border border-suspense-aurora/10 bg-blue-ocean/25 p-5 sm:p-6">
            {#if $form.socials.length > 0}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    {#each $form.socials as social, index}
                        <FormField for={`social-${index}`} label={social.name} labelVariant="editorial" spacing="none" error={$form.errors[`socials.${index}.url`]}>
                            <TextInput
                                id={`social-${index}`}
                                type="url"
                                name={`socials[${index}][url]`}
                                variant="profile"
                                placeholder="https://"
                                bind:value={social.url}
                                error={$form.errors[`socials.${index}.url`]}
                            />
                        </FormField>
                    {/each}
                </div>
            {:else}
                <p class="font-noto-sans text-sm text-suspense-aurora/60">
                    Nenhuma rede social configurada para este perfil.
                </p>
            {/if}
        </div>
    </Section>

    <Section title="Meu radar otaku">
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <div class="rounded-xl border border-green-mint/35 bg-green-pine/45 p-5 sm:p-6">
                <div class="mb-5 flex items-center gap-3">
                    <Badge variant="success">Curto</Badge>
                    <p class="font-noto-sans text-sm text-suspense-aurora/75">
                        Três gêneros que sempre entram na sua lista
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {#each $form.preferences.likes as preference, index}
                        <SelectInput
                            id={`likes-${index}`}
                            name={`preferences[likes][${index}][content]`}
                            variant="profile"
                            bind:value={preference.content}
                        >
                            <option value="">Escolha</option>
                            {#each userPreferences as option}
                                <option value={option.value}>{option.name}</option>
                            {/each}
                        </SelectInput>
                    {/each}
                </div>
            </div>

            <div class="rounded-xl border border-red-crimson/35 bg-red-blood/45 p-5 sm:p-6">
                <div class="mb-5 flex items-center gap-3">
                    <Badge variant="danger">Passo</Badge>
                    <p class="font-noto-sans text-sm text-suspense-aurora/75">
                        Três gêneros que não combinam tanto com você
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {#each $form.preferences.unlikes as preference, index}
                        <SelectInput
                            id={`unlikes-${index}`}
                            name={`preferences[unlikes][${index}][content]`}
                            variant="profile"
                            bind:value={preference.content}
                        >
                            <option value="">Escolha</option>
                            {#each userPreferences as option}
                                <option value={option.value}>{option.name}</option>
                            {/each}
                        </SelectInput>
                    {/each}
                </div>
            </div>
        </div>
    </Section>

    {#if can.update}
        <div class="container-page mb-10 flex justify-end">
            <Button
                type="submit"
                variant="accent"
                shape="pill"
                size="lg"
                loading={$form.processing}
                class="w-full sm:w-auto"
            >
                Atualizar perfil
            </Button>
        </div>
    {/if}
</form>
