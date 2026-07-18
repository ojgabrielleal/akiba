<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { IconButton, Offcanvas, Section } from "@/ui/components/private";
    import { MusicForm } from "@/ui/widgets/private";
    import { musicPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ ranking } = $page.props);

    let can = musicPermissions();
    let offcanvasRef;
    let musicSelected = null;

    let actions = [
        {
            title: "Salvar",
            icon: "/svg/save.svg",
            permission: can.updateRanking,
            onClick: () => setRanking(),
        },
    ];

    const rankingCards = [
        {
            position: "1º",
            wrapper: "flex justify-center lg:col-span-2",
        },
        {
            position: "2º",
            wrapper: "flex justify-center lg:justify-end",
        },
        {
            position: "3º",
            wrapper: "flex justify-center lg:justify-start",
        },
    ];

    const setRanking = () => {
        router.post("/panel/radio/music/ranking/refresh", {}, {
            preserveScroll: true,
        });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={musicSelected?.name ?? "Editar musica"}>
    <div slot="content" let:close>
        <MusicForm {musicSelected} {close} />
    </div>
</Offcanvas>

{#if ranking}
    <Section {title} {actions}>
        <div class="mt-7 lg:mt-10 grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-15">
            {#each rankingCards as card, index}
                {#if ranking.data[index]}
                    <div class={card.wrapper}>
                        <div class="relative min-h-36 w-full max-w-130 rounded-md bg-gradient-orange-morning-aurora py-4 pl-4 pr-28 sm:h-33 sm:min-h-0 sm:py-3 sm:pl-28 sm:pr-36 lg:px-15">
                            <div class="absolute top-1/2 hidden -translate-y-1/2 items-center justify-center bg-contain bg-no-repeat font-noto-sans font-extrabold uppercase italic text-suspense-aurora sm:left-4 sm:flex sm:h-18 sm:w-18 sm:pl-2 sm:pt-1 sm:text-[2rem] lg:-left-12 lg:h-23 lg:w-23 lg:pl-3 lg:text-[2.6rem]" style="background-image: url('/svg/star.svg')" aria-label={`${card.position} lugar`}>
                                {card.position}
                            </div>
                            <img
                                src={resolvePlaceholderImage(ranking.data[index].ranking.image, "placeholder")}
                                alt={`Imagem do ranking da música ${ranking.data[index].name}`}
                                class="absolute bottom-0 right-0 h-28 w-28 object-contain sm:h-40 sm:w-40"
                            />
                            {#if can.update}
                                <div class="absolute right-3 bottom-2 z-10">
                                    <IconButton
                                        variant="edit"
                                        label="Atualizar"
                                        size="sm"
                                        on:click={() => {
                                            musicSelected = ranking.data[index];
                                            offcanvasRef.open();
                                        }}
                                    />
                                </div>
                            {/if}
                            <h2 class="mb-2 h-10 max-w-full sm:w-[18rem] font-noto-sans text-base font-extrabold uppercase italic leading-5 text-blue-marinho sm:text-xl sm:leading-[1.3rem]">
                                {ranking.data[index].name}
                            </h2>
                            <div class="max-w-full font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Cantor/Banda:
                                </span>
                                {ranking.data[index].artist}
                            </div>
                            <div class="mt-[-0.2rem] max-w-full font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Anime:
                                </span>
                                {ranking.data[index].production}
                            </div>
                        </div>
                    </div>
                {/if}
            {/each}
        </div>
    </Section>
{/if}
