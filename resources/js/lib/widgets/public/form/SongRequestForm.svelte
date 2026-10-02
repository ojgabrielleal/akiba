<script>
    export let close = () => {};
    export let oauth = {};

    import axios from "axios";
    import { useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";
    import { debounce, resolvePlaceholderImage } from "@/lib/utils";

    $: form = useForm({
        address: null,
        birth_date: null,
        anime: null,
        music: null,
        message: null,
    });

    const submit = () => {
        if ($form.processing) return;

        $form.post("/song-request", {
            preserveScroll: true,
            onSuccess: () => {
                toast.success($form.music ? "Pedido enviado!" : "Recado enviado!");
                close();
            },
            onError: () => toast.error("Revise o pedido e tente novamente."),
        });
    };

    let activeSearchDropdown = false;

    let searchQuery = "";
    let searchResults = [];
    let searchMusicResults = [];

    let searchError = false;
    let hasSearched = false;
    let apiAvailable = true;
    let manualAnime = "";
    let manualMusicName = "";

    let latestSearchId = 0;
    let isSearching = false;
    let requestMode = "music";
    const labelClass = "text-md text-gray-700 font-noto-sans block mb-1 [[data-public-theme=akiba]_&]:text-suspense-aurora/75 [[data-public-theme=night]_&]:text-suspense-aurora/75";
    const inputClass = "w-full h-10 bg-white font-noto-sans text-md text-black rounded-md outline-none border border-gray-400 [[data-public-theme=akiba]_&]:border-0 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=akiba]_&]:placeholder:text-suspense-aurora/35 [[data-public-theme=night]_&]:border-0 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:text-suspense-aurora [[data-public-theme=night]_&]:placeholder:text-suspense-aurora/35";
    const helpClass = "text-[0.8rem] text-gray-500 font-noto-sans mt-1 block [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60";
    const segmentedClass = "inline-grid grid-cols-2 rounded-full border border-gray-300 bg-gray-100 p-0.5 [[data-public-theme=akiba]_&]:border-0 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:border-0 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))]";
    const inactiveModeClass = "cursor-pointer text-gray-600 hover:text-blue-ocean [[data-public-theme=akiba]_&]:text-suspense-aurora/70 [[data-public-theme=akiba]_&]:hover:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora/70 [[data-public-theme=night]_&]:hover:text-suspense-aurora";
    const dropdownClass = "public-themed-scrollbar absolute z-50 w-full max-h-56 overflow-y-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-xl [[data-public-theme=akiba]_&]:border-blue-skywave/20 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_88%,var(--color-blue-night))] [[data-public-theme=night]_&]:border-blue-skywave/20 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_88%,var(--color-blue-night))]";
    const dropdownTitleClass = "text-gray-700 text-sm font-semibold [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora";
    const dropdownMutedClass = "text-gray-500 text-xs mt-1 [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60";

    $: showManualMusicFields = requestMode === "music"
        && hasSearched
        && searchResults.length === 0
        && !apiAvailable;

    const selectRequestMode = (mode) => {
        requestMode = mode;

        if (mode === "message") {
            searchQuery = "";
            searchResults = [];
            searchMusicResults = [];
            activeSearchDropdown = false;
            manualAnime = "";
            manualMusicName = "";
            $form.anime = null;
            $form.music = null;
        }
    };

    const syncManualMusic = () => {
        const anime = manualAnime.trim();
        const name = manualMusicName.trim();

        $form.anime = anime || null;
        $form.music = anime && name
            ? {
                production: anime,
                type: "OVA",
                artist: "Não informado",
                name,
                image: null,
                is_manual: true,
            }
            : null;
    };

    const resetManualMusic = () => {
        searchQuery = "";
        searchResults = [];
        searchMusicResults = [];
        searchError = false;
        hasSearched = false;
        apiAvailable = true;
        manualAnime = "";
        manualMusicName = "";
        activeSearchDropdown = false;
        $form.anime = null;
        $form.music = null;
    };

    const searchAnimeThemes = async (value, searchId) => {
        try {
            const response = await axios.get("/api/anime-themes/search", {
                params: { query: value },
            });

            if (searchId !== latestSearchId) return;

            const results = Array.isArray(response.data)
                ? response.data
                : response.data?.results ?? [];

            apiAvailable = response.data?.api_available ?? true;
            searchResults = results.map((item) => ({
                title: item.anime,
                image: item.banner,
                musics: item.musics ?? [],
            }));

            hasSearched = true;
        } catch (error) {
            if (searchId !== latestSearchId) return;
            console.error("AnimeThemes API: Error searching anime themes", error);
            searchError = true;
        }finally {
            if (searchId === latestSearchId) {
                isSearching = false;
            }
        }
    };

    const debouncedSearchAnimeThemes = debounce(searchAnimeThemes);

    const handleSearchInput = (value) => {
        searchQuery = value;

        searchResults = [];
        searchMusicResults = [];
        searchError = false;
        hasSearched = false;
        apiAvailable = true;
        manualAnime = "";
        manualMusicName = "";
        $form.anime = null;
        $form.music = null;

        const query = value.trim();
        const searchId = ++latestSearchId;

        if (!query) {
            isSearching = false;
            return;
        }

        isSearching = true;
        debouncedSearchAnimeThemes(query, searchId);
    };

    const normalizeArtists = (artists) => {
        if (Array.isArray(artists)) {
            return artists.filter(Boolean).join(", ");
        }

        return artists || "Artista não informado";
    };

    const selectAnime = (item) => {
        activeSearchDropdown = false;

        searchQuery = item.title;
        $form.anime = item.title;

        searchMusicResults = item.musics.map((music) => ({
            production: item.title,
            image: item.image,
            type: music.type,
            name: music.title,
            artist: normalizeArtists(music.artists),
            is_manual: false,
        }));

        if (searchMusicResults.length === 1) {
            searchQuery = item.title + "[" + searchMusicResults[0].type + "]" + " - " + searchMusicResults[0].name;
            $form.music = searchMusicResults[0];
            return;
        }
        
    };

</script>

<form novalidate on:submit|preventDefault={submit}>
    {#if oauth.is_oauth && !oauth.profile_completed}
        <div class="mb-3 grid grid-cols-1 gap-3">
            <div>
                <label for="address" class={labelClass}>
                    Qual é a sua cidade e estado?
                </label>
                <input
                    id="address"
                    type="text"
                    name="address"
                    class={[inputClass, "pl-4"]}
                    placeholder="Ex: Salto - SP"
                    bind:value={$form.address}
                    required
                />
                <span class={helpClass}>
                    Fora do Brasil? Informe cidade e país que tu está!.
                </span>
                {#if $form.errors.address}
                    <span class="mt-1 block font-noto-sans text-xs text-red-600">
                        {$form.errors.address}
                    </span>
                {/if}
            </div>
            <div>
                <label for="birth-date" class={labelClass}>
                    Qual é a sua data de nascimento?
                </label>
                <input
                    id="birth-date"
                    type="date"
                    name="birth_date"
                    class={[inputClass, "px-4"]}
                    bind:value={$form.birth_date}
                    required
                />
                <span class={helpClass}>
                    É só pra mostrar sua idade caso você seja o Ouvinte do Mês.
                </span>
                {#if $form.errors.birth_date}
                    <span class="mt-1 block font-noto-sans text-xs text-red-600">
                        {$form.errors.birth_date}
                    </span>
                {/if}
            </div>
        </div>
    {/if}
    <div class="mb-3 flex justify-center">
        <div class={segmentedClass}>
            <button
                type="button"
                class={["h-8 rounded-full px-3 font-noto-sans text-[0.7rem] font-extrabold uppercase italic transition sm:px-4",
                    requestMode === "music" ? "bg-orange-citric text-blue-marinho" : inactiveModeClass,
                ]}
                aria-pressed={requestMode === "music"}
                on:click={() => selectRequestMode("music")}
            >
                Música + recado
            </button>
            <button
                type="button"
                class={["h-8 rounded-full px-3 font-noto-sans text-[0.7rem] font-extrabold uppercase italic transition sm:px-4",
                    requestMode === "message" ? "bg-orange-citric text-blue-marinho" : inactiveModeClass,
                ]}
                aria-pressed={requestMode === "message"}
                on:click={() => selectRequestMode("message")}
            >
                Só recado
            </button>
        </div>
    </div>
    {#if requestMode === "music" && !showManualMusicFields}
        <div class="mb-3 relative">
            <label for="anime-theme-search" class={labelClass}>
                Busque por anime ou música
            </label>
            <input
                id="anime-theme-search"
                type="text"
                name="anime_theme_search"
                class={[inputClass, "pl-4"]}
                placeholder="Ex: Naruto ou unravel"
                autocomplete="off"
                bind:value={searchQuery}
                on:input={(e) => handleSearchInput(e.target.value)}
                on:focus={() => (activeSearchDropdown = true)}
                on:blur={() => (activeSearchDropdown = false)}
            />
            <span class={helpClass}>
                Diga o nome do anime ou da música e faremos o resto!
            </span>
            {#if activeSearchDropdown}
                <div class={dropdownClass}>
                    {#if !searchQuery.trim()}
                        <div class="p-3 font-noto-sans text-center">
                            <div class={dropdownTitleClass}>
                                O que vai embalar seu pedido?
                            </div>
                            <div class={dropdownMutedClass}>
                                Tente Naruto, unravel, Blue Bird...
                            </div>
                        </div>
                    {:else if isSearching}
                        <div class="p-3 font-noto-sans text-center flex flex-col items-center gap-2">
                            <svg class="w-5 h-5 text-gray-300 animate-spin fill-blue-ocean [[data-public-theme=akiba]_&]:text-suspense-aurora/35 [[data-public-theme=night]_&]:text-suspense-aurora/35" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2v3a7 7 0 1 0 7 7h3c0 5.523-4.477 10-10 10Z"/>
                            </svg>
                            <div class={dropdownTitleClass}>
                                Buscando animes e músicas...
                            </div>
                        </div>
                    {:else if searchError}
                        <div class="p-3 font-noto-sans text-center">
                            <div class="text-red-600 text-sm font-semibold">
                                Não foi possível realizar a busca.
                            </div>
                            <div class={dropdownMutedClass}>
                                Tente novamente em instantes.
                            </div>
                        </div>
                    {:else if hasSearched && searchResults.length === 0}
                        <div class="p-3 font-noto-sans text-center">
                            <div class={dropdownTitleClass}>
                                Nenhum anime ou música encontrado.
                            </div>
                            <div class={dropdownMutedClass}>
                                Confira o termo pesquisado e tente novamente.
                            </div>
                        </div>
                    {:else}
                        {#each searchResults as item}
                            <button aria-label={`Selecionar anime ${item.title}`}
                                type="button"
                                class="flex w-full cursor-pointer items-center gap-3 rounded-xl p-2 transition hover:bg-orange-citric/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric"
                                on:mousedown={() => selectAnime(item)}
                            >
                                <img
                                    src={resolvePlaceholderImage(item.image, "placeholder")}
                                    alt={item.title}
                                    class="w-14 h-14 object-cover rounded-md border border-gray-100 shadow-sm shrink-0 [[data-public-theme=akiba]_&]:border-blue-skywave/20 [[data-public-theme=night]_&]:border-blue-skywave/20"
                                    loading="lazy"
                                />
                                <div class="flex flex-col items-start text-left">
                                    <div class="font-noto-sans font-semibold text-gray-900 text-sm line-clamp-1 [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=night]_&]:text-suspense-aurora">
                                        {item.title}
                                    </div>
                                    {#each item.musics.slice(0, 2) as music}
                                        <div class="w-full text-gray-500 text-xs line-clamp-1 [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60">
                                            <span class="font-bold text-blue-ocean">{music.type}</span>
                                            {music.title}{music.artists ? ` - ${normalizeArtists(music.artists)}` : ""}
                                        </div>
                                    {/each}
                                    {#if item.musics.length > 2}
                                        <div class="text-gray-400 text-[0.65rem] [[data-public-theme=akiba]_&]:text-suspense-aurora/45 [[data-public-theme=night]_&]:text-suspense-aurora/45">
                                            +{item.musics.length - 2} músicas
                                        </div>
                                    {/if}
                                </div>
                            </button>
                        {/each}
                    {/if}
                </div>
            {/if}
        </div>
    {/if}
    {#if showManualMusicFields}
        <div class="mb-3 rounded-md border border-orange-citric/45 bg-orange-citric/10 px-4 py-3 font-noto-sans">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-extrabold text-blue-marinho">
                        Não achei essa música no catálogo
                    </p>
                    <p class="mt-0.5 text-xs text-blue-marinho/70">
                        Me diz o anime e o nome da música que eu mando do seu jeito.
                    </p>
                </div>
            </div>
        </div>
        <div class="mb-3 relative">
            <label for="manual-anime" class={labelClass}>
                Qual é o nome do anime? <span class="text-red-600">*</span>
            </label>
            <input
                id="manual-anime"
                type="text"
                name="manual_anime"
                class={[inputClass, "px-4"]}
                placeholder="Ex: Naruto"
                bind:value={manualAnime}
                on:input={syncManualMusic}
                required
            />
        </div>
        <div class="mb-3 relative">
            <label for="manual-music" class={labelClass}>
                Qual é a música desse anime? <span class="text-red-600">*</span>
            </label>
            <input
                id="manual-music"
                type="text"
                name="manual_music"
                class={[inputClass, "px-4"]}
                placeholder="Ex: Blue Bird"
                bind:value={manualMusicName}
                on:input={syncManualMusic}
                required
            />
        </div>
        {#if $form.errors.music}
            <span class="sm:col-span-2 mt-1 block font-noto-sans text-xs text-red-600">
                {$form.errors.music}
            </span>
        {/if}
    {/if}
    {#if requestMode === "music" && searchMusicResults.length > 1}
        <div class="mb-5">
            <div class={labelClass}>
                Escolha uma música:
            </div>
            <div class="public-themed-scrollbar song-request-music-list max-h-44 overflow-y-auto rounded-md border border-blue-ocean/20 bg-blue-ocean/[0.03] p-2 [[data-public-theme=akiba]_&]:border-0 [[data-public-theme=akiba]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))] [[data-public-theme=night]_&]:border-0 [[data-public-theme=night]_&]:bg-[color-mix(in_srgb,var(--color-blue-ocean)_76%,var(--color-blue-night))]">
                {#each ["OP", "ED", "OVA"] as type}
                    {#if searchMusicResults.some((item) => item.type === type)}
                        <div class="px-2 py-2 font-noto-sans text-[0.62rem] font-extrabold uppercase tracking-[0.2em] text-orange-amber">
                            {type === "OP" ? "Aberturas" : type === "ED" ? "Encerramentos" : "OVAs"}
                        </div>
                        {#each searchMusicResults.filter((item) => item.type === type) as item}
                            <label class={["mb-2 flex cursor-pointer items-center gap-3 rounded-md border p-3 font-noto-sans transition",
                                { "border-orange-citric bg-orange-citric text-blue-marinho shadow-sm": $form.music === item },
                                { "border-blue-ocean/10 bg-white text-blue-marinho hover:border-orange-citric/70 hover:bg-orange-citric/5 [[data-public-theme=akiba]_&]:border-blue-skywave/20 [[data-public-theme=akiba]_&]:bg-blue-night/15 [[data-public-theme=akiba]_&]:text-suspense-aurora [[data-public-theme=akiba]_&]:hover:border-orange-citric/70 [[data-public-theme=akiba]_&]:hover:bg-orange-citric/10 [[data-public-theme=night]_&]:border-blue-skywave/20 [[data-public-theme=night]_&]:bg-blue-night/15 [[data-public-theme=night]_&]:text-suspense-aurora [[data-public-theme=night]_&]:hover:border-orange-citric/70 [[data-public-theme=night]_&]:hover:bg-orange-citric/10": $form.music !== item },
                            ]}>
                                <input
                                    type="radio"
                                    name="music"
                                    value={item}
                                    bind:group={$form.music}
                                    class="peer sr-only"
                                />
                                <span class={["flex size-4 shrink-0 items-center justify-center rounded-full border-2",
                                    $form.music === item ? "border-blue-marinho" : "border-blue-ocean/40",
                                ]}>
                                    {#if $form.music === item}
                                        <span class="size-2 rounded-full bg-blue-marinho"></span>
                                    {/if}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-extrabold">
                                        {item.name}
                                    </span>
                                    <span class={$form.music === item ? "block truncate text-xs text-blue-marinho/75" : "block truncate text-xs text-gray-500 [[data-public-theme=akiba]_&]:text-suspense-aurora/60 [[data-public-theme=night]_&]:text-suspense-aurora/60"}>
                                        {item.artist || "Artista não informado"}
                                    </span>
                                </span>
                            </label>
                        {/each}
                    {/if}
                {/each}
            </div>
            {#if $form.errors.music}
                <span class="mt-1 block font-noto-sans text-xs text-red-600">
                    {$form.errors.music}
                </span>
            {/if}
        </div>
    {/if}
    <div class="mb-3">
        <label for="message" class={labelClass}>
            Escreva uma mensagem
            {#if requestMode === "message"}
                <span class="text-red-600">*</span>
            {/if}
        </label>
        <textarea
            id="message"
            name="message"
            rows="3"
            class={[inputClass, "min-h-28 p-4 resize-none"]}
            placeholder={requestMode === "message" ? "Deixe uma mensagem amigável" : "(Opcional) Deixe uma mensagem amigável"}
            bind:value={$form.message}
            required={requestMode === "message"}
        ></textarea>
        <span class={helpClass}>
            {requestMode === "music" ? "Opcional, mas capricha: o locutor vai ler." : "Escreva seu recado para o locutor."}
        </span>
        {#if $form.errors.message}
            <span class="mt-1 block font-noto-sans text-xs text-red-600">
                {$form.errors.message}
            </span>
        {/if}
    </div>
    <button
        type="submit"
        class="cursor-pointer rounded-full bg-orange-citric px-6 py-2 font-noto-sans font-extrabold uppercase italic text-blue-marinho transition hover:brightness-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-70"
        disabled={$form.processing}
    >
        {$form.processing ? "Enviando..." : "Enviar"}
    </button>
</form>
