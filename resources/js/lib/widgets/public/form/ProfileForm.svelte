<script>
    import { router, useForm } from "@inertiajs/svelte";
    import {
        Button,
        FormField,
        TextArea,
        TextInput,
        Tooltip,
    } from "@/lib/components/public";
    import { publicAnimations } from "@/lib/constants/public/animation";
    import {
        consumePendingOAuthAction,
        dispatchOAuthAction,
        OAuthAction,
        rememberOAuthAction,
        resolvePlaceholderImage,
        themeClass,
    } from "@/lib/utils";

    export let profile;
    export let close = () => {};
    export let internal = false;

    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.name || profile?.username || "Perfil";
    $: provider = profile?.provider || "google";
    $: providerLabel = provider === "discord" ? "Discord" : provider === "google" ? "Google" : provider;

    $: badges = profile?.badges ?? [];
    $: providerIcon = provider === "discord" ? "/svg/discord.svg" : "/svg/google.svg";
    $: providerIconClass = "filter-suspense-aurora";
    $: endpoint = internal ? "/member-profile" : "/profile";
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

    const emptyTopAnime = (position = null) => ({
        position,
        anime_theme_list_id: null,
        slug: null,
        name: "",
        image: null,
        metadata: null,
    });

    const normalizeTopAnimes = (topAnimes = []) => [1, 2, 3].map((position) => {
        const anime = topAnimes.find((item) => item.position === position) ?? null;

        return anime?.name ? { ...emptyTopAnime(position), ...anime, position } : emptyTopAnime(position);
    });

    const form = useForm({
        _method: "PATCH",
        avatar: null,
        nickname: profile?.nickname || profile?.name || profile?.username || "",
        birth_date: profile?.birth_date ?? "",
        address: profile?.address ?? "",
        city: profile?.city ?? "",
        state: profile?.state ?? "",
        country: profile?.country ?? "",
        bio: internal ? profile?.bio ?? "" : "",
        top_anime: profile?.top_anime?.name ? profile.top_anime : emptyTopAnime(),
        top_animes: normalizeTopAnimes(profile?.top_animes ?? []),
    });

    let avatarPreview = avatar;
    $: if (!$form.avatar) avatarPreview = avatar;

    let topAnimeQuery = "";
    let topAnimeResults = [];
    let topAnimeSearching = false;
    let topAnimeSearchError = null;
    let editingFavoriteAnime = !profile?.top_anime?.name;

    const searchTopAnime = async () => {
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
    };

    const selectedAnimePayload = (anime, position = null) => ({
        position,
        anime_theme_list_id: anime.anime_theme_list_id,
        slug: anime.slug,
        name: anime.name,
        image: anime.image,
        metadata: anime.metadata,
    });

    const clearAnimeSearch = () => {
        topAnimeQuery = "";
        topAnimeResults = [];
        topAnimeSearchError = null;
    };

    const selectTopAnime = (anime) => {
        $form.top_anime = selectedAnimePayload(anime);
        clearAnimeSearch();
        editingFavoriteAnime = false;
    };

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
        router.post(internal ? "/member-logout" : "/oauth/logout", {}, {
            preserveScroll: false,
        });
    };
</script>

<form class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(13rem,0.9fr)]" on:submit|preventDefault={submit}>
    <div class="space-y-4 lg:border-r lg:border-blue-night/25 lg:pr-6">
    <div class="mb-5 flex items-center gap-3 sm:gap-4">
        {#if internal}
            <label
                for="member-avatar"
                class={[
                    "group/avatar relative shrink-0 cursor-pointer overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow focus-within:ring-2 focus-within:ring-orange-citric",
                    themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
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
                themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }),
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
            <p class={[
                "truncate font-noto-sans text-lg font-extrabold",
                themeClass("text", "blue-night", { fixed: true }),
                "[[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora",
            ]}>
                {nickname}
            </p>
            <p class="truncate font-noto-sans text-xs text-[color-mix(in_srgb,#000014_50%,transparent)] [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60">
                {internal ? "Membro interno" : providerLabel}
            </p>
        </div>
        <Tooltip position="left">
            <button
                type="button"
                class="group/logout flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-transparent transition hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                aria-label="Sair da conta"
                on:click={logout}
            >
                <img
                    src="/svg/logout.svg"
                    alt=""
                    aria-hidden="true"
                    class="mr-[0.1rem] size-4 filter-neutral-gray transition group-hover/logout:filter-orange-amber group-focus-visible/logout:filter-orange-amber [[data-public-theme=akiba]_&]:filter-suspense-aurora [[data-public-theme=night]_&]:filter-suspense-aurora"
                />
            </button>
            <span slot="content">Sair da conta</span>
        </Tooltip>
    </div>

    {#if !internal}
        <div class="-mt-2 mb-4">
            <Button
                type="button"
                variant="secondary"
                size="sm"
                shape="pill"
                class="w-full justify-center"
                on:click={syncProvider}
            >
                <img
                    src={providerIcon}
                    alt=""
                    aria-hidden="true"
                    class={["size-4", providerIconClass]}
                />
                Ressincronizar...
            </Button>
        </div>
    {/if}

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
    </div>

    <aside class="min-h-64 lg:pl-2">
        <h3 class={[
            "mb-4 text-center font-noto-sans text-[1.1875rem] font-black uppercase italic",
            themeClass("text", "blue-night", { fixed: true }),
            "[[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora",
        ]}>
            Meus Emblemas
        </h3>

        <div class="rounded-md border border-blue-night/15 bg-blue-night/[0.04] p-3.5 [[data-public-theme=akiba]_&]:border-suspense-aurora/20 [[data-public-theme=akiba]_&]:bg-blue-night/20 [[data-public-theme=night]_&]:border-suspense-aurora/20 [[data-public-theme=night]_&]:bg-blue-night/20">
            {#if badges.length > 0}
            <div class="public-themed-scrollbar overflow-x-auto overflow-y-hidden pb-2">
                <ul class="flex min-w-max gap-3">
                {#each badges as badge (badge.uuid)}
                    <li class="w-18 shrink-0 text-center">
                        <div class="mx-auto flex size-14 items-center justify-center overflow-hidden rounded-md sm:size-15">
                            {#if badge.image}
                                <img
                                    src={badge.image}
                                    alt={badge.name}
                                    class="h-full w-full object-contain"
                                    loading="lazy"
                                />
                            {:else}
                                <div class="flex h-full w-full items-center justify-center rounded-md bg-orange-amber font-noto-sans text-xl font-black italic text-blue-night">
                                    ★
                                </div>
                            {/if}
                        </div>
                        <p class={[
                            "mt-1.5 line-clamp-2 font-noto-sans text-[0.62rem] font-black leading-tight",
                            themeClass("text", "blue-night", { fixed: true }),
                            "[[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora",
                        ]} title={badge.name}>
                            {badge.name}
                        </p>
                    </li>
                {/each}
                </ul>
            </div>
        {:else}
            <div class="flex min-h-40 items-center justify-center rounded-md border border-dashed border-blue-night/20 px-4 text-center [[data-public-theme=akiba]_&]:border-suspense-aurora/25 [[data-public-theme=night]_&]:border-suspense-aurora/25">
                <p class="font-noto-sans text-sm font-semibold text-[color-mix(in_srgb,#000014_55%,transparent)] [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60">
                    Nenhum emblema conquistado ainda.
                </p>
            </div>
            {/if}
        </div>

        <section class="mt-5">
            <h3 class={[
                "mb-4 text-center font-noto-sans text-[1.1875rem] font-black uppercase italic",
                themeClass("text", "blue-night", { fixed: true }),
                "[[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora",
            ]}>
                {internal ? "Meu top 3 de animes" : "Meu anime favorito"}
            </h3>

            <div class="rounded-md border border-blue-night/15 bg-blue-night/[0.04] p-3.5 [[data-public-theme=akiba]_&]:border-suspense-aurora/20 [[data-public-theme=akiba]_&]:bg-blue-night/20 [[data-public-theme=night]_&]:border-suspense-aurora/20 [[data-public-theme=night]_&]:bg-blue-night/20">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <p class="font-noto-sans text-xs font-semibold text-[color-mix(in_srgb,#000014_55%,transparent)] [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60">
                        {internal ? "Animes favoritos cadastrados no seu perfil dentro do painel." : "Sua escolha fica no seu perfil e aparece quando você for Ouvinte do mês."}
                    </p>
                    {#if $form.top_anime?.image && editingFavoriteAnime}
                        <img
                            src={resolvePlaceholderImage($form.top_anime.image, "placeholder")}
                            alt={$form.top_anime.name}
                            class="size-14 shrink-0 rounded-md object-cover object-top"
                        />
                    {/if}
                </div>

                {#if internal}
                    {@const selectedTopAnimes = $form.top_animes.filter((anime) => anime.name)}
                    {#if selectedTopAnimes.length > 0}
                        <div class="grid gap-2.5">
                            {#each selectedTopAnimes as anime (anime.position)}
                                <div class="grid grid-cols-[3.25rem_minmax(0,1fr)] items-center gap-2.5 rounded-md bg-blue-marinho/85 p-2 shadow-inner shadow-blue-night/25">
                                    <div class="relative">
                                        <img
                                            src={resolvePlaceholderImage(anime.image, "placeholder")}
                                            alt={anime.name}
                                            class="h-13 w-11 rounded-md object-cover object-top shadow-md shadow-blue-night/35"
                                        />
                                        <span class="absolute -left-1 -top-1 flex size-6 items-center justify-center rounded-sm bg-orange-amber font-noto-sans text-[0.65rem] font-black italic text-blue-night shadow-sm">
                                            {anime.position}
                                        </span>
                                    </div>
                                    <p class="line-clamp-2 min-w-0 font-noto-sans text-[0.72rem] font-black uppercase italic leading-tight text-suspense-aurora">
                                        {anime.name}
                                    </p>
                                </div>
                            {/each}
                        </div>
                    {:else}
                        <a
                            href={`/panel/profile/${profile.uuid}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            class={["flex min-h-28 cursor-pointer items-center justify-center rounded-md border border-dashed border-suspense-aurora/25 px-4 text-center font-noto-sans text-sm font-semibold text-suspense-aurora/60 transition hover:border-orange-citric/70 hover:text-orange-citric focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric", publicAnimations.cardInteractive]}
                        >
                            Nenhum anime encontrado, clique pra cadastrar.
                        </a>
                    {/if}
                {:else if $form.top_anime?.name && !editingFavoriteAnime}
                    <div class="grid grid-cols-[6rem_minmax(0,1fr)] items-center gap-4 rounded-md bg-blue-marinho/85 p-3 shadow-inner shadow-blue-night/25">
                        <img
                            src={resolvePlaceholderImage($form.top_anime.image, "placeholder")}
                            alt={$form.top_anime.name}
                            class="h-28 w-24 rounded-md object-cover object-top shadow-md shadow-blue-night/35"
                        />
                        <div class="min-w-0">
                            <p class="line-clamp-3 font-noto-sans text-base font-black uppercase italic leading-tight text-suspense-aurora">
                                {$form.top_anime.name}
                            </p>
                            <button
                                type="button"
                                class="mt-3 cursor-pointer rounded-sm border border-orange-citric/35 px-3 py-1 font-noto-sans text-xs font-black uppercase italic text-orange-citric transition hover:border-orange-citric hover:bg-orange-citric hover:text-blue-night focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric"
                                on:click={() => editingFavoriteAnime = true}
                            >
                                Trocar
                            </button>
                        </div>
                    </div>
                {/if}

                {#if !internal && (!$form.top_anime?.name || editingFavoriteAnime)}
                    <div class="mt-3 grid gap-2">
                        <label for="profile-top-anime" class="sr-only">Buscar anime favorito</label>
                        <input
                            id="profile-top-anime"
                            type="search"
                            bind:value={topAnimeQuery}
                            placeholder="Busque seu anime favorito"
                            class="h-11 rounded-md border border-suspense-aurora/20 bg-suspense-aurora/12 px-3 font-noto-sans text-sm font-bold text-suspense-aurora outline-none placeholder:text-suspense-aurora/55 focus:border-suspense-aurora/45 focus:ring-2 focus:ring-suspense-aurora/25 [[data-public-theme=light]_&]:bg-suspense-aurora [[data-public-theme=light]_&]:text-blue-night [[data-public-theme=light]_&]:placeholder:text-blue-night/55"
                        />
                        <Button
                            type="button"
                            size="sm"
                            loading={topAnimeSearching}
                            on:click={searchTopAnime}
                        >
                            Buscar anime
                        </Button>
                    </div>

                    {#if topAnimeSearchError}
                        <p class="mt-2 font-noto-sans text-xs font-bold text-red-crimson">{topAnimeSearchError}</p>
                    {/if}

                    {#if topAnimeResults.length}
                        <div class="mt-3 grid max-h-60 gap-2 overflow-y-auto pr-1">
                            {#each topAnimeResults as anime}
                                <button
                                    type="button"
                                    class={["grid min-h-18 cursor-pointer grid-cols-[3.75rem_minmax(0,1fr)] items-center gap-3 rounded-md bg-blue-marinho/80 p-2 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric", publicAnimations.cardInteractive]}
                                    on:click={() => selectTopAnime(anime)}
                                >
                                    <img
                                        src={resolvePlaceholderImage(anime.image, "placeholder")}
                                        alt={anime.name}
                                        class="size-14 rounded-md object-cover object-top"
                                    />
                                    <span class="line-clamp-2 font-noto-sans text-sm font-black uppercase italic leading-tight text-suspense-aurora">
                                        {anime.name}
                                    </span>
                                </button>
                            {/each}
                        </div>
                    {/if}
                {/if}
            </div>
        </section>

    </aside>
</form>
