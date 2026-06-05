<script>
    import axios from "axios";
    import { useForm, page } from "@inertiajs/svelte";
    import { debounce } from "@/utils";

    $: ({ onair } = $page.props);

    $: air = onair.data[0];
    $: success = false;

    let form = useForm({
        name: null,
        address: null,
        anime: null,
        music: null,
        message: null,
    });

    const submit = () => {
        $form.post("/song-request", {
            onSuccess: () => {
                success = true;
            },
        });
    };

    let activeAnimeDropdown = false;
    let activeMusicDropdown = false;

    let animesList = [];
    let animeThemesList = [];

    const getAnimeJikanApi = (value) => {
        if (!value) {
            animesList = [];
            return;
        }

        animesList = [];
        animeThemesList = [];

        $form.anime = null;
        $form.music = null;

        axios.get(`https://api.jikan.moe/v4/anime?q=${value}`)
            .then((response) => {
                const filtered = response.data.data.filter(
                    (item) => item.type === "TV",
                );

                animesList = filtered.map((item) => ({
                    title: item.title,
                    mal_id: item.mal_id,
                    image: item.images.jpg.image_url,
                    year: item.aired.from
                        ? new Date(item.aired.from).getFullYear()
                        : "N/A",
                }));
            })
            .catch(() => {
                console.error("Jikan API: Error to fetch anime");
            });
    };

    const getAnimeThemesJikanApi = (animeId) => {
        axios.get(`https://api.jikan.moe/v4/anime/${animeId}/themes`)
            .then((response) => {
                const { openings, endings } = response.data.data;

                const parseTheme = (themeStr, type) => {
                    const match = themeStr.match(/"([^"]+)"\s*by\s*([^(\n]+)/);

                    let name = match ? match[1] : themeStr;
                    let artist = match ? match[2].trim() : "Desconhecido";

                    const cleanBrackets = /\s*\([^)]*\)/g;

                    return {
                        type: type,
                        name: name.replace(cleanBrackets, "").trim(),
                        artist: artist.replace(cleanBrackets, "").trim(),
                    };
                };

                animeThemesList = [
                    ...openings.map((t) => parseTheme(t, "OP")),
                    ...endings.map((t) => parseTheme(t, "ED")),
                ];
            })
            .catch(() => {
                console.error("Jikan API: Error to fetch anime themes");
            });
    };

    const debouncedGetAnimeJikanApi = debounce(getAnimeJikanApi);
</script>

{#if success}
    <div class="h-100 py-3">
        <div class="mb-4 text-sm font-noto-sans text-gray-500">
            💌 Yay! Pedido enviado!
        </div>
        <div class="text-sm font-noto-sans text-gray-500">
            Seu pedido já tá a caminho! {air.program.host.gender === "male" ? "O" : "A"}
            {air.program.host.nickname}
            vai ver rapidinho. Fica por aqui e curte a vibe da programação! ✨🔥
        </div>
    </div>
{:else if air.allows_song_requests}
    <form on:submit|preventDefault={submit}>
        <div class="mb-3">
            <label for="name" class="text-md text-gray-700 font-noto-sans block mb-1">
                Como gostaria de ser chamado?
            </label>
            <input
                id="name"
                type="text"
                name="name"
                class="w-full h-10 bg-white font-noto-sans text-black text-md rounded-md outline-none pl-4 border border-gray-400"
                placeholder="Ex: Ayasumi"
                bind:value={$form.name}
                required
            />
            <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                Vale apelido, nome social.. Só pra falar que o pedido é seu!
            </span>
        </div>
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
        <div class="mb-3 relative">
            <label for="anime" class="text-md text-gray-700 font-noto-sans block mb-1">
                Escolha um anime para ouvir a música
            </label>
            <input
                id="anime"
                type="text"
                name="anime"
                class="w-full h-10 bg-white font-noto-sans text-md text-black rounded-md outline-none pl-4 border border-gray-400"
                placeholder="Ex: Naruto"
                on:input={(e) => debouncedGetAnimeJikanApi(e.target.value)}
                on:focus={() => (activeAnimeDropdown = true)}
                on:blur={() => (activeAnimeDropdown = false)}
            />
            <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                Selecione o anime para que possamos buscar as músicas.
            </span>
            {#if activeAnimeDropdown && animesList.length > 0}
                <div class="absolute w-full bg-white border border-gray-200 rounded-2xl shadow-xl z-25 max-h-56 overflow-y-auto p-2">
                    {#each animesList as item}
                        <button aria-label=""
                            type="button"
                            class="cursor-pointer flex items-center gap-3 w-full p-2 rounded-xl"
                            on:mousedown={() => { $form.anime = item; activeAnimeDropdown = false; getAnimeThemesJikanApi(item.mal_id); }}
                        >
                            <img
                                src={item.image}
                                alt={item.title}
                                class="w-14 h-14 object-cover object-top rounded-md border border-gray-100 shadow-sm shrink-0"
                                loading="lazy"
                            />
                            <div class="flex flex-col items-start text-left">
                                <div class="font-noto-sans font-semibold text-gray-900 text-sm line-clamp-1">
                                    {item.title}
                                </div>
                                <div class="font-noto-sans text-gray-500 text-xs">
                                    {item.year}
                                </div>
                            </div>
                        </button>
                    {/each}
                </div>
            {/if}
        </div>
        {#if $form.anime}
            <div class="mb-5 relative">
                <div class="text-md text-gray-700 font-noto-sans block mb-1">
                    Escolha uma música do anime escolhido
                </div>
                <button
                    type="button"
                    class="w-full h-11 flex items-center justify-between bg-white font-noto-sans text-md text-black rounded-md outline-none px-4 border border-gray-400"
                    on:click={() => (activeMusicDropdown = true)}
                    on:blur={() => (activeMusicDropdown = false)}
                >
                    {#if $form.music}
                        <div class="flex flex-col items-start overflow-hidden flex-1 min-w-0">
                            <span class="text-sm text-gray-900 font-normal truncate w-full text-left">
                                {$form.music.name} - {$form.music.artist}
                            </span>
                        </div>
                    {:else}
                        <div class="flex-1 text-left">
                            <span class="text-gray-400 italic text-sm">
                                Selecione uma música
                            </span>
                        </div>
                    {/if}
                    <img
                        src="/svg/chevron-down.svg"
                        alt=""
                        class="w-5 h-5 text-gray-400 shrink-0 ml-2"
                        aria-hidden="true"
                    />
                </button>
                {#if activeMusicDropdown && animeThemesList.length > 0}
                    <div class="absolute w-full mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl z-30 max-h-56 overflow-y-auto">
                        {#each ["OP", "ED"] as type}
                            <div class="px-3 py-2 text-[0.6rem] font-extrabold text-gray-400 uppercase tracking-[0.2em]">
                                {type === "OP" ? "Aberturas" : "Encerramentos"}
                            </div>
                            {#each animeThemesList.filter((item) => item.type === type) as item}
                                <button aria-label=""
                                    type="button"
                                    class="w-full flex flex-col items-start gap-0.5 p-3 rounded-xl hover:bg-gray-50 active:bg-pink-50 transition-colors border-b last:border-0 border-gray-50 mb-1"
                                    on:mousedown={() => { $form.music = item; activeMusicDropdown = false; }}
                                >
                                    <div class="font-noto-sans font-extrabold text-gray-900 text-sm line-clamp-1 w-full text-left leading-tight">
                                        {item.name}
                                    </div>
                                    <div class="font-noto-sans text-gray-500 text-xs truncate w-full text-left">
                                        {item.artist}
                                    </div>
                                </button>
                            {/each}
                        {/each}
                    </div>
                {/if}
            </div>
        {/if}
        <div class="mb-3">
            <label for="message" class="text-md text-gray-700 font-noto-sans block mb-1">
                Escreva uma mensagem
            </label>
            <textarea
                id="message"
                name="message"
                rows="4"
                class="w-full bg-white font-noto-sans text-md text-black rounded-md outline-none p-4 border border-gray-400 resize-none"
                placeholder="Deixe uma mensagem amigavel"
                bind:value={$form.message}
                required
            ></textarea>
            <span class="text-[0.8rem] text-gray-500 font-noto-sans mt-1 block">
                Vamos evitar ofensas! Se pedido pode não tocar por isso.
            </span>
        </div>
        <button type="submit" class="cursor-pointer w-full bg-blue-skywave px-8 py-2 rounded-md text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
            Enviar
        </button>
    </form>
{:else}
    <div class="h-100 py-3">
        <div class="mb-4 text-sm font-noto-sans text-gray-500">
            😭 Ai… não dá pra mandar pedido agora!
        </div>
        <div class="text-sm font-noto-sans text-gray-500">
            O programa não tá rolando ou {air.program.host.gender === "male" ? "o" : "a"}
            {air.program.host.nickname.toLowerCase()} tá dando uma pausa, tá? Mas
            relaxa, daqui a pouco você consegue mandar sua música! 💬🎶
        </div>
    </div>
{/if}
