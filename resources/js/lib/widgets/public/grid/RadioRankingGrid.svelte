<script>
    import { EditorialTitle } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let ranking = null;

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

    $: songs = ranking?.data ?? ranking ?? [];
    $: remaining = songs.slice(3, 10);
</script>

<section
    class="radio-ranking-background bg-blue-marinho"
    style="--ranking-background: url('/img/pages/radio/backgrounds/music-ranking.webp');"
>
    <EditorialTitle title="Akiba Ranking" compact />

    <div class="container-page py-12 lg:py-24">
        <ol class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-15">
            {#each rankingCards as card, index}
                {#if songs[index]}
                    <li class={card.wrapper}>
                        <div class="relative min-h-36 w-full max-w-130 rounded-md bg-orange-amber py-4 pl-4 pr-28 sm:h-33 sm:min-h-0 sm:py-3 sm:pl-28 sm:pr-36 lg:px-15">
                            <div class="absolute top-1/2 hidden -translate-y-1/2 items-center justify-center bg-contain bg-no-repeat font-noto-sans font-extrabold uppercase italic text-suspense-aurora sm:left-4 sm:flex sm:h-18 sm:w-18 sm:pl-2 sm:pt-1 sm:text-[2rem] lg:-left-12 lg:h-23 lg:w-23 lg:pl-3 lg:text-[2.6rem]" style="background-image: url('/svg/star.svg')" aria-label={`${card.position} lugar`}>
                                {card.position}
                            </div>
                            <img
                                src={resolvePlaceholderImage(songs[index].ranking.image, "avatar")}
                                alt={`Imagem do ranking da música ${songs[index].name}`}
                                class="absolute bottom-0 right-0 h-28 w-28 object-contain sm:h-40 sm:w-40"
                                loading="lazy"
                            />
                            <h2 class="mb-2 h-10 max-w-full font-noto-sans text-base font-extrabold uppercase italic leading-5 text-blue-marinho sm:w-[18rem] sm:text-xl sm:leading-[1.3rem]">
                                {songs[index].name}
                            </h2>
                            <div class="max-w-full font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Cantor/Banda:
                                </span>
                                {songs[index].artist}
                            </div>
                            <div class="mt-[-0.2rem] max-w-full font-noto-sans text-sm text-blue-marinho">
                                <span class="font-medium">
                                    Anime:
                                </span>
                                {songs[index].production}
                            </div>
                        </div>
                    </li>
                {/if}
            {/each}
        </ol>

        {#if remaining.length > 0}
            <ol class="mt-10 grid grid-cols-1 gap-x-15 gap-y-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-y-15" start="4">
                {#each remaining as music, index}
                    <li class={["flex justify-center", index === 6 ? "lg:col-start-2" : ""]}>
                        <div class="relative w-full rounded-md bg-gradient-blue-cerulean-glow py-4 pl-12 pr-4 font-noto-sans text-suspense-aurora">
                            <span class="absolute left-0 top-1/2 flex size-14 -translate-x-1/2 -translate-y-1/2 flex-col items-center justify-center rounded-full border-4 border-blue-marinho bg-orange-amber text-center font-noto-sans font-black italic leading-none text-blue-marinho">
                                <span class="text-2xl">{index + 4}º</span>
                                <span class="text-[0.5rem] uppercase not-italic">Lugar</span>
                            </span>
                            <h3 class="line-clamp-2 min-h-9 text-base font-black italic uppercase leading-5">
                                {music.name}
                            </h3>
                            <p class="mt-3 text-xs font-normal uppercase">
                                Cantor / Banda:
                                {music.artist}
                            </p>
                            <p class="mt-1 text-xs font-normal uppercase">
                                Anime:
                                {music.production}
                            </p>
                        </div>
                    </li>
                {/each}
            </ol>
        {/if}
    </div>
</section>

<style>
    .radio-ranking-background {
        background-image: none;
    }

    @media (min-width: 1024px) {
        .radio-ranking-background {
            background-image: var(--ranking-background);
            background-position: center top;
            background-repeat: repeat-y;
            background-size: 100% auto;
        }
    }
</style>
