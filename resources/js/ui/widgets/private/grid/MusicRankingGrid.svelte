<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Offcanvas, Section, Tooltip } from "@/ui/components/private";
    import { MusicForm } from "@/ui/widgets/private";
    import { musicPermissions } from "@/utils";

    $: ({ musicRanking } = $page.props);

    let can = musicPermissions();
    let offcanvasRef;
    let musicSelected = null;

    let actions = [
        {
            title: "Definir",
            icon: "/svg/edit.svg",
            permission: can.set,
            onClick: () => setRanking(),
        },
    ];

    const rankingCards = [
        {
            position: "1º",
            wrapper: "col-span-2 flex justify-center",
        },
        {
            position: "2º",
            wrapper: "flex justify-end",
        },
        {
            position: "3º",
            wrapper: "flex justify-start",
        },
    ];

    const setRanking = () => {
        router.post("/panel/radio/music-ranking", {}, {
            preserveScroll: true,
        });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={musicSelected?.name ?? "Editar musica"}>
    <div slot="content" let:close>
        <MusicForm {musicSelected} {close} />
    </div>
</Offcanvas>

{#if musicRanking}
    <Section {title} {actions}>
        <div class="grid grid-cols-2 gap-20 mt-18">
            {#each rankingCards as card, index}
                {#if musicRanking.data[index]}
                    <div class={card.wrapper}>
                        <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora py-3 px-15">
                            <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                                {card.position}
                            </div>
                            <img
                                src={musicRanking.data[index].ranking.image}
                                alt=""
                                aria-hidden="true"
                                class="w-40 h-40 absolute right-0 bottom-0"
                            />
                            {#if can.update}
                                <div class="absolute right-3 bottom-2 z-10">
                                    <Tooltip>
                                        <button
                                            type="button"
                                            aria-label={`Atualizar ${musicRanking.data[index].name}`}
                                            class="w-7 h-7 bg-blue-marinho rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                                            on:click={() => {
                                                musicSelected = musicRanking.data[index];
                                                offcanvasRef.open();
                                            }}
                                        >
                                            <img
                                                src="/svg/edit.svg"
                                                alt=""
                                                aria-hidden="true"
                                                class="w-4 filter-orange-citric"
                                                loading="lazy"
                                            />
                                        </button>
                                        <div slot="content">
                                            Atualizar
                                        </div>
                                    </Tooltip>
                                </div>
                            {/if}
                            <h2 class="mb-2 w-[18rem] leading-[1.3rem] font-noto-sans font-extrabold text-blue-marinho text-lg uppercase italic">
                                {musicRanking.data[index].name}
                            </h2>
                            <div class="font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Cantor/Banda:
                                </span>
                                {musicRanking.data[index].artist}
                            </div>
                            <div class="-mt-[0.2rem] font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Anime:
                                </span>
                                {musicRanking.data[index].production}
                            </div>
                        </div>
                    </div>
                {/if}
            {/each}
        </div>
    </Section>
{/if}
