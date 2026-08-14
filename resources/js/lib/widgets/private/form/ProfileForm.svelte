<script>
    import { useForm } from "@inertiajs/svelte";

    import {
        Badge,
        Button,
        FormField,
        IconButton,
        LoadingSpinner,
        Preview,
        RadioInput,
        Section,
        SelectInput,
        TextArea,
        TextInput,
    } from "@/lib/components/private/";
    import { profilePermissions, resolvePlaceholderImage } from "@/lib/utils";
    import { userPreferences } from "@/lib/constants";

    export let profile = null;

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

    const can = profilePermissions();

    const form = useForm({
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
        top_animes: normalizeTopAnimes(profile?.data.top_animes),
    });

    let topAnimeQuery = "";
    let topAnimeResults = [];
    let topAnimeSearching = false;
    let topAnimeSearchError = null;

    function normalizeSocials(socials = []) {
        return socialPlatforms.map((name) => {
            const social = socials.find((item) => item.name === name);

            return social ?? { uuid: null, name, url: null };
        });
    }

    function resolvePreferenceValue(content) {
        if (!content || content === "#") return "";

        const normalizedContent = content.toLocaleLowerCase("pt-BR");
        const preference = userPreferences.find((option) =>
            option.value === content || option.name.toLocaleLowerCase("pt-BR") === normalizedContent
        );

        return preference?.value ?? "";
    }

    function normalizePreferences(preferences = {}) {
        return {
            likes: (preferences.likes ?? []).map((preference) => ({
                ...preference,
                content: resolvePreferenceValue(preference.content),
            })),
            unlikes: (preferences.unlikes ?? []).map((preference) => ({
                ...preference,
                content: resolvePreferenceValue(preference.content),
            })),
        };
    }

    function normalizeTopAnimes(topAnimes = []) {
        return [1, 2, 3].map((position) => {
            const anime = topAnimes.find((item) => item.position === position);

            return anime ?? {
                position,
                anime_theme_list_id: null,
                slug: null,
                name: null,
                image: null,
                metadata: null,
            };
        });
    }

    async function searchTopAnime() {
        const query = topAnimeQuery.trim();

        topAnimeSearchError = null;
        topAnimeResults = [];

        if (query.length < 2) {
            topAnimeSearchError = "Digite pelo menos 2 caracteres.";
            return;
        }

        topAnimeSearching = true;

        try {
            const response = await fetch(`/api/anime-themes/anime/search?query=${encodeURIComponent(query)}`);

            if (!response.ok) {
                throw new Error("Não foi possível buscar animes agora.");
            }

            topAnimeResults = await response.json();
        } catch (error) {
            topAnimeSearchError = error.message;
        } finally {
            topAnimeSearching = false;
        }
    }

    function selectTopAnime(position, anime) {
        $form.top_animes = $form.top_animes.map((item) =>
            item.position === position
                ? {
                    position,
                    anime_theme_list_id: anime.anime_theme_list_id,
                    slug: anime.slug,
                    name: anime.name,
                    image: anime.image,
                    metadata: anime.metadata,
                }
                : item
        );
    }

    function removeTopAnime(position) {
        $form.top_animes = $form.top_animes.map((item) =>
            item.position === position
                ? {
                    position,
                    anime_theme_list_id: null,
                    slug: null,
                    name: null,
                    image: null,
                    metadata: null,
                }
                : item
        );
    }

    function submit() {
        $form.post(`/panel/profile/${profile.data.uuid}`, {
            preserveScroll: true,
            forceFormData: true,
        });
    }
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
                            src={resolvePlaceholderImage($form.avatar, "avatar", $form.gender)}
                            error={$form.errors.avatar}
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

                <div class="flex min-w-0 flex-col">
                    <div class="mb-6">
                        <p class="font-noto-sans text-xs font-extrabold uppercase tracking-[0.2em] text-orange-morning">
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
                            error={$form.errors[`preferences.likes.${index}.content`]}
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
                            error={$form.errors[`preferences.unlikes.${index}.content`]}
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

    <Section title="Meu Top 3">
        <div class="rounded-xl border border-suspense-aurora/10 bg-blue-ocean/25 p-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[22rem_1fr]">
                <div class="min-w-0">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <TextInput
                            id="top-anime-search"
                            type="search"
                            name="top-anime-search"
                            variant="profile"
                            placeholder="Busque um anime"
                            bind:value={topAnimeQuery}
                        />
                        <IconButton
                            type="button"
                            label="Buscar anime"
                            icon="/svg/search.svg"
                            tone="dark"
                            surface="transparent"
                            size="lg"
                            tooltipPosition="top"
                            disabled={topAnimeSearching}
                            class="size-12 self-start rounded-full !bg-orange-citric hover:!bg-orange-citric sm:self-auto"
                            on:click={searchTopAnime}
                        />
                    </div>
                    {#if topAnimeSearchError}
                        <div id="top-anime-search-error" class="mt-1 font-noto-sans text-sm text-red-crimson">
                            {topAnimeSearchError}
                        </div>
                    {/if}

                    {#if topAnimeSearching}
                        <div class="mt-4 flex h-77 items-center justify-center rounded-lg border border-dashed border-suspense-aurora/20 bg-blue-marinho/35 px-6 text-center">
                            <LoadingSpinner size="lg" tone="accent" label="Buscando animes..." />
                        </div>
                    {:else if topAnimeResults.length > 0}
                        <div class="mt-4 h-77 space-y-2 overflow-y-auto pr-1">
                            {#each topAnimeResults as anime}
                                <article class="rounded-md bg-blue-marinho/65 p-3">
                                    <div class="flex gap-3">
                                        <img
                                            src={resolvePlaceholderImage(anime.image, "placeholder")}
                                            alt={anime.name}
                                            class="h-20 w-14 shrink-0 rounded-sm object-cover"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate font-noto-sans text-sm font-black uppercase italic text-suspense-aurora">
                                                {anime.name}
                                            </h3>
                                            <p class="mt-1 font-noto-sans text-xs text-suspense-aurora/60">
                                                {[anime.metadata?.season, anime.metadata?.year, anime.metadata?.media_format].filter(Boolean).join(" • ") || "AnimeThemes"}
                                            </p>
                                            <div class="mt-3 flex gap-2">
                                                {#each [1, 2, 3] as position}
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        variant="secondary"
                                                        on:click={() => selectTopAnime(position, anime)}
                                                    >
                                                        Top {position}
                                                    </Button>
                                                {/each}
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            {/each}
                        </div>
                    {:else}
                        <div class="mt-4 flex h-77 items-center justify-center rounded-lg border border-dashed border-suspense-aurora/20 bg-blue-marinho/35 px-6 text-center">
                            <p class="font-noto-sans text-sm font-extrabold uppercase italic text-suspense-aurora/55">
                                Busque um anime para selecionar seu Top 3
                            </p>
                        </div>
                    {/if}
                </div>

                <div class="min-w-0">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {#each $form.top_animes as anime}
                            <article class="overflow-hidden rounded-lg border border-blue-skywave/25 bg-blue-marinho/65">
                                <div class="h-80 bg-blue-ocean/80">
                                    {#if anime.image}
                                        <img
                                            src={resolvePlaceholderImage(anime.image, "placeholder")}
                                            alt={anime.name}
                                            class="h-full w-full object-cover object-top"
                                        />
                                    {:else}
                                        <div class="flex h-full items-center justify-center px-4 text-center font-noto-sans text-sm font-black uppercase italic text-suspense-aurora/45">
                                            Top {anime.position}
                                        </div>
                                    {/if}
                                </div>
                                <div class="flex items-center justify-between gap-3 p-3">
                                    <h3 class="min-w-0 truncate font-noto-sans text-sm font-black uppercase italic text-suspense-aurora">
                                        {anime.name || `Anime ${anime.position}`}
                                    </h3>
                                    <IconButton
                                        variant="trash"
                                        label="Limpar anime"
                                        size="sm"
                                        surface="transparent"
                                        tone="accent"
                                        tooltipPosition="top"
                                        on:click={() => removeTopAnime(anime.position)}
                                    />
                                </div>
                            </article>
                        {/each}
                    </div>

                    {#if $form.errors.top_animes}
                        <p class="mt-3 font-noto-sans text-sm font-bold text-red-crimson">
                            {$form.errors.top_animes}
                        </p>
                    {/if}
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
