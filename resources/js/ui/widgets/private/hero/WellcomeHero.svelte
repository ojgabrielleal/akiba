<script>
    import { page, Link } from "@inertiajs/svelte";
    import { quickAccessPermissions } from "@/utils";

    let can = quickAccessPermissions();

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

<section class="w-full hidden lg:block mb-15 bg-[url('https://i.ibb.co/Tdhkkyg/3.png'),var(--gradient-blue-ocean-cerulean)] bg-contain bg-right bg-no-repeat">
    <div class="h-30 container-page flex items-center gap-5 relative">
        <img
            src="/img/defaults/avatar.webp"
            alt="Avatar"
            class="w-40 h-40 absolute bottom-0"
        />
        <div class="font-noto-sans font-extrabold uppercase italic text-3xl text-suspense-aurora ml-50">
            {chosenPhrase}
        </div>
        <div class="flex gap-2 absolute right-0">
            {#if can.event.create}
                <Link
                    class="w-30 bg-blue-marinho p-2 rounded-md flex justify-center items-center flex-col gap-2 font-noto-sans font-extrabold text-[0.7rem] text-orange-citric text-center uppercase italic"
                    href="/panel/event"
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
            {#if can.activity.create}
                <Link
                    class="w-30 bg-blue-marinho p-2 rounded-md flex justify-center items-center flex-col gap-2 font-noto-sans font-extrabold text-[0.7rem] text-orange-citric text-center uppercase italic"
                    href="/panel/administration"
                >
                    <img
                        src="/svg/alerts.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Adicionar avisos
                </Link>
            {/if}
            {#if can.repository.create}
                <Link
                    class="w-30 bg-blue-marinho p-2 rounded-md flex justify-center items-center flex-col gap-2 font-noto-sans font-extrabold text-[0.7rem] text-orange-citric text-center uppercase italic"
                    href="/panel/repository"
                >
                    <img
                        src="/svg/download.svg"
                        alt=""
                        aria-hidden="true"
                        class="w-6 filter-orange-citric"
                    />
                    Upar arquivos
                </Link>
            {/if}
            {#if can.locution.start}
                <Link
                    class="w-30 bg-blue-marinho p-2 rounded-md flex justify-center items-center flex-col gap-2 font-noto-sans font-extrabold text-[0.7rem] text-orange-citric text-center uppercase italic"
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
            {#if can.post.create}
                <Link
                    class="w-30 bg-blue-marinho p-2 rounded-md flex justify-center items-center flex-col gap-2 font-noto-sans font-extrabold text-[0.7rem] text-orange-citric text-center uppercase italic"
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
        </div>
    </div>
</section>
