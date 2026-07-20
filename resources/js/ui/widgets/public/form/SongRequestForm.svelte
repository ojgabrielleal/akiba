<script>
    export let close = () => {};

    import axios from "axios";
    import { page, useForm } from "@inertiajs/svelte";
    import toast from "svelte-hot-french-toast";
    import { debounce, resolvePlaceholderImage } from "@/utils";

    $:({ oauth } = $page.props);

    $: form = useForm({
        address: null,
        anime: null,
        music: null,
        message: null,
    });

    const submit = () => {
        $form.post("/song-request", {
            onSuccess: () => {
                toast.success("Pedido enviado");
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
        
        $form.anime = item.title;
    };

</script>

<form on:submit|preventDefault={submit}>
    {#if !oauth.profile_completed}
        <div class="mb-3">
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
                Não está no Brasil? Fala ai a cidade e país que está agora.
            </span>
        </div>
    {/if}
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
    {#if searchMusicResults.length > 1}
        <div class="mb-5">
            <div class="text-md text-gray-700 font-noto-sans block mb-1">
                Escolha uma música:
            </div>
            <div class="max-h-40 overflow-y-auto rounded-md border border-gray-400 bg-white p-2">
                {#each ["OP", "ED"] as type}
                    {#if searchMusicResults.some((item) => item.type === type)}
                        <div class="px-3 py-2 text-[0.6rem] font-extrabold text-gray-400 uppercase tracking-[0.2em]">
                            {type === "OP" ? "Aberturas" : "Encerramentos"}
                        </div>
                        {#each searchMusicResults.filter((item) => item.type === type) as item}
                            <label class={["mb-1 flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition-colors",
                                { "border-blue-ocean bg-blue-ocean/5": $form.music === item },
                                { "border-gray-100 hover:bg-gray-50": $form.music !== item },
                            ]}>
                                <input
                                    type="radio"
                                    name="music"
                                    value={item}
                                    bind:group={$form.music}
                                    class="size-4 shrink-0 accent-blue-ocean"
                                    required
                                />
                                <span class="min-w-0 font-noto-sans">
                                    <span class="block truncate text-sm font-extrabold text-gray-900">
                                        {item.name}
                                    </span>
                                    <span class="block truncate text-xs text-gray-500">
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
        </label>
        <textarea
            id="message"
            name="message"
            rows="3"
            class="w-full bg-white font-noto-sans text-md text-black rounded-md outline-none p-4 border border-gray-400 resize-none"
            placeholder="(Opcional) Deixe uma mensagem amigável"
            bind:value={$form.message}
        ></textarea>
        <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
            Vamos evitar ofensas! Seu pedido pode não tocar por isso.
        </span>
    </div>
    <button
        type="submit"
        class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
    >
        Enviar
    </button>
</form>
