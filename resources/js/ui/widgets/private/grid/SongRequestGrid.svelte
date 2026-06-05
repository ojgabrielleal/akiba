<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/private/";
    import { songRequestPermissions } from "@/utils";

    $: ({ onair, songRequests } = $page.props);

    let can = songRequestPermissions();

    const requestToggleSongRequest = () => {
        router.patch("/panel/locution/songrequest/toggle", {}, {
            preserveScroll: true,
        });
    };

    const markToReproduced = (songrequest) => {
        router.patch(`/panel/locution/songrequest/${songrequest}/played`, {}, {
            preserveScroll: true,
        });
    };

    const markToCanceled = (songrequest) => {
        router.patch(`/panel/locution/songrequest/${songrequest}/canceled`, {}, {
            preserveScroll: true,
        });
    };

    const requestFinishlocution = () => {
        router.patch(`/panel/locution/locution/finish`);
    };
</script>

{#if can.list}
    <Section {title}>
        <div id="requests" class="flex flex-col gap-5 lg:relative">
            {#if can.locution.finish}
                <button 
                    type="button" 
                    class="cursor-pointer block lg:absolute left-0 w-full lg:w-auto py-2 px-6 bg-red-crimson rounded-full text-blue-marinho font-noto-sans font-extrabold italic uppercase" 
                    on:click={() => requestFinishlocution()}
                >
                    Encerrar programa
                </button>
            {/if}
             {#if can.toggle}
                <div class="flex justify-center">
                    {#if onair.data.allows_song_requests}
                        <button 
                            type="button" 
                            class="cursor-pointer w-full lg:w-auto py-2 px-6 bg-suspense-honeycream rounded-full text-blue-marinho font-noto-sans font-extrabold italic uppercase" 
                            on:click={() => requestToggleSongRequest()}
                        >
                            Parar de receber
                        </button>
                    {:else}
                        <button 
                            type="button" 
                            class="cursor-pointer w-full lg:w-auto py-2 px-6 bg-green-mint rounded-full text-blue-marinho font-extrabold font-noto-sans italic uppercase" 
                            on:click={() => requestToggleSongRequest()}
                        >
                            Começar a receber
                        </button>
                    {/if}
                </div>
            {/if}
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mt-10">
            {#each songRequests.data as item}
                <article class={["w-full rounded-md p-3",
                    { "bg-green-mint": item.was_reproduced },
                    { "bg-red-crimson": item.was_canceled },
                    { "bg-blue-skywave": !item.was_reproduced && !item.was_canceled },
                ]}>
                    <div class="w-70 flex items-center gap-1.5 text-suspense-aurora text-[1.2rem] font-noto-sans font-extrabold italic">
                        <img
                            src="/svg/profile.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-suspense-aurora"
                            loading="lazy"
                        />
                        <span class="truncate">
                            {item.name}
                        </span>
                    </div>
                    <div class="w-full mt-1 flex gap-1.5 text-suspense-aurora text-[1rem] font-noto-sans">
                        <img
                            src="/svg/location.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-suspense-aurora"
                            loading="lazy"
                        />
                        <span class="truncate">
                            {item.address}
                        </span>
                    </div>
                    <div class="mt-1.5 flex gap-1.5 text-suspense-aurora text-[1rem] font-noto-sans">
                        <img
                            src="/svg/ip.svg"
                            alt=""
                            aria-hidden="true"
                            class="w-5 filter-suspense-aurora"
                            loading="lazy"
                        />
                        {item.ip_address}
                    </div>
                    <div class="flex items-center justify-center w-full mt-5 mb-5">
                        <div class="relative w-full">
                            <div class="absolute left-0 w-2/5 h-[0.1rem] bg-suspense-aurora rounded-full top-1/2 -translate-y-1/2"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img
                                    src="/svg/music.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-6 filter-suspense-aurora"
                                    loading="lazy"
                                />
                            </div>
                            <div class="absolute right-0 w-2/5 h-[0.1rem] bg-suspense-aurora rounded-full top-1/2 -translate-y-1/2"></div>
                        </div>
                    </div>
                    {#if item.music}
                        <div class="flex flex-wrap xl:flex-nowrap gap-3">
                            <img
                                src={item.music.image}
                                alt={`Capa do anime ${item.music.production}`}
                                class="w-15 h-15 rounded-md object-cover object-top shrink-0"
                                loading="lazy"
                            />
                            <div>
                                <div class="w-full lg:w-50 xl:w-full block text-suspense-aurora text-sm font-noto-sans">
                                    Anime:
                                    <span class="truncate">
                                        {item.music.production}
                                    </span>
                                </div>
                                <div class="w-full lg:w-50 xl:w-full block text-suspense-aurora text-sm font-noto-sans">
                                    Artista:
                                    <span class="truncate">
                                        {item.music.artist}
                                    </span>
                                </div>
                                <div class="w-full lg:w-50 xl:w-full block text-suspense-aurora text-sm font-noto-sans">
                                    Música:
                                    <span class="truncate">
                                        {item.music.name}
                                    </span>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <div class="text-suspense-aurora/60 text-sm font-noto-sans italic">
                            Música não disponível
                        </div>
                    {/if}
                    <div class="flex items-center justify-center w-full mt-5 mb-5">
                        <div class="relative w-full">
                            <div class="absolute left-0 w-2/5 h-[0.1rem] bg-suspense-aurora rounded-full top-1/2 -translate-y-1/2"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <img
                                    src="/svg/telegram.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-7 filter-suspense-aurora"
                                    loading="lazy"
                                />
                            </div>
                            <div class="absolute right-0 w-2/5 h-[0.1rem] bg-white rounded-full top-1/2 -translate-y-1/2"></div>
                        </div>
                    </div>
                    <div class="h-15 line-clamp-3 text-suspense-aurora text-sm font-noto-sans mb-7">
                        {item.message}
                    </div>
                    <div class="flex justify-between">
                        <div class="flex items-center gap-1 text-suspense-aurora text-sm font-noto-sans font-extrabold italic">
                            <img
                                src="/svg/clock.svg"
                                alt=""
                                aria-hidden="true"
                                class="w-4 filter-suspense-aurora"
                                loading="lazy"
                            />
                            {item.created_at}
                        </div>
                        <div class="flex gap-1">
                            {#if !item.was_reproduced && !item.was_canceled}
                                {#if can.cancel}
                                    <button type="button"
                                        aria-label="Marcar como cancelado"
                                        class="w-7 h-7 bg-suspense-aurora rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                                        on:click={() => markToCanceled(item.uuid)}
                                    >
                                        <img
                                            src="/svg/close.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-5 filter-blue-marinho"
                                            loading="lazy"
                                        />
                                    </button>
                                {/if}
                                {#if can.reproduce}
                                    <button type="button"
                                        aria-label="Marcar como atendido"
                                        class="w-7 h-7 bg-suspense-aurora rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                                        on:click={() => markToReproduced(item.uuid)}
                                    >
                                        <img
                                            src="/svg/verify.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="w-5 filter-blue-marinho"
                                            loading="lazy"
                                        />
                                    </button>
                                {/if}
                            {/if}
                        </div>
                    </div>
                </article>
            {/each}
        </div>
    </Section>
{/if}
