<script>
    import { Link } from "@inertiajs/svelte";
    import { quickAccessPermissions } from "@/lib/utils";

    const can = quickAccessPermissions();

    let chosenPhrase;
    const generatePhrases = () => {
        let phrases = [
            `Okaeri.`,
            `Yokoso.`,
            `Konnichiwa.`,
            `Itadakimasu.`,
            `Oi oi oi...`,
            `Gankin desu ka?`,
            `Moshi moshi.`,
            `Ogenki desu ka?`,
            `Yoroshiku onegaishimasu.`,
            `Moshi moshi, konnichiwa!`,
            `Yare Yare...`,

        ];

        let index = Math.floor(Math.random() * phrases.length);
        return phrases[index];
    };

    chosenPhrase = generatePhrases();
</script>

<section class="mb-15 hidden w-full bg-[url('https://i.ibb.co/Tdhkkyg/3.png'),var(--gradient-blue-ocean-cerulean)] bg-contain bg-right bg-no-repeat lg:block">
    <div class="container-page relative flex h-30 min-w-0 items-center gap-4 xl:gap-5">
        <img
            src="/img/defaults/character.webp"
            alt="Avatar"
            class="w-40 h-40 absolute bottom-0"
        />
        <h2 class="ml-44 min-w-0 flex-1 truncate font-noto-sans text-2xl font-extrabold uppercase italic text-suspense-aurora xl:ml-50 xl:text-3xl">
            {chosenPhrase}
        </h2>
        <div class="flex shrink-0 gap-2">
            {#if can.post.create}
                <Link
                    class="flex w-24 flex-col items-center justify-center gap-2 rounded-md bg-blue-marinho p-2 text-center font-noto-sans text-[0.7rem] font-extrabold uppercase italic text-orange-citric xl:w-30"
                    href="/panel/post"
                >
                    <img
                        src="/svg/materials.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Criar matéria
                </Link>
            {/if}
            {#if can.locution.start}
                <Link
                    class="flex w-24 flex-col items-center justify-center gap-2 rounded-md bg-blue-marinho p-2 text-center font-noto-sans text-[0.7rem] font-extrabold uppercase italic text-orange-citric xl:w-30"
                    href="/panel/locution"
                >
                    <img
                        src="/svg/locution.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Locutar
                </Link>
            {/if}
            {#if can.activity.create}
                <Link
                    class="flex w-24 flex-col items-center justify-center gap-2 rounded-md bg-blue-marinho p-2 text-center font-noto-sans text-[0.7rem] font-extrabold uppercase italic text-orange-citric xl:w-30"
                    href="/panel/administration"
                >
                    <img
                        src="/svg/alerts.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Adicionar aviso
                </Link>
            {/if}
            {#if can.event.create}
                <Link
                    class="flex w-24 flex-col items-center justify-center gap-2 rounded-md bg-blue-marinho p-2 text-center font-noto-sans text-[0.7rem] font-extrabold uppercase italic text-orange-citric xl:w-30"
                    href="/panel/post"
                >
                    <img
                        src="/svg/events.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Criar evento
                </Link>
            {/if}
            {#if can.repository.create}
                <Link
                    class="flex w-24 flex-col items-center justify-center gap-2 rounded-md bg-blue-marinho p-2 text-center font-noto-sans text-[0.7rem] font-extrabold uppercase italic text-orange-citric xl:w-30"
                    href="/panel/marketing"
                >
                    <img
                        src="/svg/download.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Upar arquivo
                </Link>
            {/if}
        </div>
    </div>
</section>
