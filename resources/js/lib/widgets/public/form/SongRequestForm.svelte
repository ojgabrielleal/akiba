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
        $form.post("/song-request", {
            onSuccess: () => {
                toast.success($form.music ? "Pedido enviado!" : "Recado enviado!");
                close();
            },
        });
    };

    let activeSearchDropdown = false;

    let searchQuery = "";
    let searchResults = [];
    let searchMusicResults = [];

    let searchError = false;
    let hasSearched = false;

    let latestSearchId = 0;
    let isSearching = false;
    let requestMode = "music";

    const selectRequestMode = (mode) => {
        requestMode = mode;

        if (mode === "message") {
            searchQuery = "";
            searchResults = [];
            searchMusicResults = [];
            activeSearchDropdown = false;
            $form.anime = null;
            $form.music = null;
        }
    };

    const searchAnimeThemes = async (value, searchId) => {
        try {
            const response = await axios.get("/api/anime-themes/search", {
                params: { query: value },
            });

            if (searchId !== latestSearchId) return;

            const results = Array.isArray(response.data) ? response.data : [];
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

    const selectAnime = (item) => {
        activeSearchDropdown = false;

        searchQuery = item.title;
        $form.anime = item.title;

        searchMusicResults = item.musics.map((music) => ({
            production: item.title,
            image: item.image,
            type: music.type,
            name: music.title,
            artist: music.artists,
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
                <label for="address" class="text-md text-gray-700 font-noto-sans block mb-1">
                    Qual é a sua cidade e estado?
                </label>
                <input
                    id="address"
                    type="text"
                    name="address"
                    class="w-full h-10 bg-white font-noto-sans text-md text-black rounded-md outline-none pl-4 border border-gray-400"
                    placeholder="Ex: Salto - SP"
                    bind:value={$form.address}
                    required
                />
                <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                    Fora do Brasil? Informe cidade e país que tu está!.
                </span>
            </div>
            <div>
                <label for="birth-date" class="text-md text-gray-700 font-noto-sans block mb-1">
                    Qual é a sua data de nascimento?
                </label>
                <input
                    id="birth-date"
                    type="date"
                    name="birth_date"
                    class="w-full h-10 bg-white font-noto-sans text-md text-black rounded-md outline-none px-4 border border-gray-400"
                    bind:value={$form.birth_date}
                    required
                />
                <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                    É só pra mostrar sua idade caso você seja o Ouvinte do Mês.
                </span>
            </div>
        </div>
    {/if}
    <div class="mb-3 flex justify-center">
        <div class="inline-grid grid-cols-2 rounded-full border border-gray-300 bg-gray-100 p-0.5">
            <button
                type="button"
                class={["h-8 rounded-full px-3 font-noto-sans text-[0.7rem] font-extrabold uppercase italic transition sm:px-4",
                    requestMode === "music" ? "bg-orange-citric text-blue-marinho" : "cursor-pointer text-gray-600 hover:text-blue-ocean",
                ]}
                aria-pressed={requestMode === "music"}
                on:click={() => selectRequestMode("music")}
            >
                Música + recado
            </button>
            <button
                type="button"
                class={["h-8 rounded-full px-3 font-noto-sans text-[0.7rem] font-extrabold uppercase italic transition sm:px-4",
                    requestMode === "message" ? "bg-blue-ocean text-suspense-aurora" : "cursor-pointer text-gray-600 hover:text-blue-ocean",
                ]}
                aria-pressed={requestMode === "message"}
                on:click={() => selectRequestMode("message")}
            >
                Só recado
            </button>
        </div>
    </div>
    {#if requestMode === "music"}
        <div class="mb-3 relative">
            <label for="anime-theme-search" class="text-md text-gray-700 font-noto-sans block mb-1">
                Busque por anime ou música
            </label>
            <input
                id="anime-theme-search"
                type="text"
                name="anime_theme_search"
                class="w-full h-10 bg-white font-noto-sans text-md text-black rounded-md outline-none pl-4 border border-gray-400"
                placeholder="Ex: Naruto ou unravel"
                autocomplete="off"
                bind:value={searchQuery}
                on:input={(e) => handleSearchInput(e.target.value)}
                on:focus={() => (activeSearchDropdown = true)}
                on:blur={() => (activeSearchDropdown = false)}
            />
            <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                Diga o nome do anime ou da música e faremos o resto!
            </span>
            {#if activeSearchDropdown}
                <div class="absolute w-full bg-white border border-gray-200 rounded-2xl shadow-xl z-25 max-h-56 overflow-y-auto p-2">
                    {#if !searchQuery.trim()}
                        <div class="p-3 font-noto-sans text-center">
                            <div class="text-gray-700 text-sm font-semibold">
                                O que vai embalar seu pedido?
                            </div>
                            <div class="text-gray-500 text-xs mt-1">
                                Tente Naruto, unravel, Blue Bird...
                            </div>
                        </div>
                    {:else if isSearching}
                        <div class="p-3 font-noto-sans text-center flex flex-col items-center gap-2">
                            <svg class="w-5 h-5 text-gray-300 animate-spin fill-blue-ocean" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2v3a7 7 0 1 0 7 7h3c0 5.523-4.477 10-10 10Z"/>
                            </svg>
                            <div class="text-gray-700 text-sm font-semibold">
                                Buscando animes e músicas...
                            </div>
                        </div>
                    {:else if searchError}
                        <div class="p-3 font-noto-sans text-center">
                            <div class="text-red-600 text-sm font-semibold">
                                Não foi possível realizar a busca.
                            </div>
                            <div class="text-gray-500 text-xs mt-1">
                                Tente novamente em instantes.
                            </div>
                        </div>
                    {:else if hasSearched && searchResults.length === 0}
                        <div class="p-3 font-noto-sans text-center">
                            <div class="text-gray-700 text-sm font-semibold">
                                Nenhum anime ou música encontrado.
                            </div>
                            <div class="text-gray-500 text-xs mt-1">
                                Confira o termo pesquisado e tente novamente.
                            </div>
                        </div>
                    {:else}
                        {#each searchResults as item}
                            <button aria-label={`Selecionar anime ${item.title}`}
                                type="button"
                                class="cursor-pointer flex items-center gap-3 w-full p-2 rounded-xl"
                                on:mousedown={() => selectAnime(item)}
                            >
                                <img
                                    src={resolvePlaceholderImage(item.image, "placeholder")}
                                    alt={item.title}
                                    class="w-14 h-14 object-cover rounded-md border border-gray-100 shadow-sm shrink-0"
                                    loading="lazy"
                                />
                                <div class="flex flex-col items-start text-left">
                                    <div class="font-noto-sans font-semibold text-gray-900 text-sm line-clamp-1">
                                        {item.title}
                                    </div>
                                    {#each item.musics.slice(0, 2) as music}
                                        <div class="w-full text-gray-500 text-xs line-clamp-1">
                                            <span class="font-bold text-blue-ocean">{music.type}</span>
                                            {music.title}{music.artists ? ` — ${music.artists}` : ""}
                                        </div>
                                    {/each}
                                    {#if item.musics.length > 2}
                                        <div class="text-gray-400 text-[0.65rem]">
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
    {#if requestMode === "music" && searchMusicResults.length > 1}
        <div class="mb-5">
            <div class="text-md text-gray-700 font-noto-sans block mb-1">
                Escolha uma música:
            </div>
            <div class="song-request-music-list max-h-44 overflow-y-auto rounded-md border border-blue-ocean/20 bg-blue-ocean/[0.03] p-2">
                {#each ["OP", "ED"] as type}
                    {#if searchMusicResults.some((item) => item.type === type)}
                        <div class="px-2 py-2 font-noto-sans text-[0.62rem] font-extrabold uppercase tracking-[0.2em] text-orange-amber">
                            {type === "OP" ? "Aberturas" : "Encerramentos"}
                        </div>
                        {#each searchMusicResults.filter((item) => item.type === type) as item}
                            <label class={["mb-2 flex cursor-pointer items-center gap-3 rounded-md border p-3 font-noto-sans transition",
                                { "border-orange-citric bg-orange-citric text-blue-marinho shadow-sm": $form.music === item },
                                { "border-blue-ocean/10 bg-white text-blue-marinho hover:border-orange-citric/70 hover:bg-orange-citric/5": $form.music !== item },
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
                                    <span class={$form.music === item ? "block truncate text-xs text-blue-marinho/75" : "block truncate text-xs text-gray-500"}>
                                        {item.artist || "Artista não informado"}
                                    </span>
                                </span>
                            </label>
                        {/each}
                    {/if}
                {/each}
            </div>
        </div>
    {/if}
    <div class="mb-3">
        <label for="message" class="text-md text-gray-700 font-noto-sans block mb-1">
            Escreva uma mensagem
            {#if requestMode === "message"}
                <span class="text-red-600">*</span>
            {/if}
        </label>
        <textarea
            id="message"
            name="message"
            rows="3"
            class="w-full bg-white font-noto-sans text-md text-black rounded-md outline-none p-4 border border-gray-400 resize-none"
            placeholder={requestMode === "message" ? "Deixe uma mensagem amigável" : "(Opcional) Deixe uma mensagem amigável"}
            bind:value={$form.message}
            required={requestMode === "message"}
        ></textarea>
        <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
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
        class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
    >
        Enviar
    </button>
</form>

<style>
    .song-request-music-list {
        scrollbar-color: var(--color-orange-citric) transparent;
        scrollbar-width: thin;
    }

    .song-request-music-list::-webkit-scrollbar {
        width: 0.45rem;
    }

    .song-request-music-list::-webkit-scrollbar-thumb {
        background: var(--color-orange-citric);
        border-radius: 9999px;
    }

    .song-request-music-list::-webkit-scrollbar-track {
        background: transparent;
    }
</style>
