<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/private";
    import { musicPermissions } from "@/utils";

    $: ({ musicRanking } = $page.props);

    let can = musicPermissions();

    let actions = [
        {
            title: "Definir",
            icon: "/svg/edit.svg",
            permission: true,
            onClick: () => operation('post')
        },
    ];

    const submit = (event, uuid) => {
        const formData = new FormData();

        formData.append("_method", "PATCH");
        formData.append("image_ranking", event.target.files[0]);

        router.post(`/panel/radio/music-ranking/${uuid}`, formData, {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    const setRanking = () => {
        router.post("/panel/radio/music-ranking", {}, {
                preserveScroll: true,
            });
    };
</script>

{#if musicRanking}
    <Section {title} {actions}>
        <div class="grid grid-cols-2 gap-20 mt-18">
            <div class="col-span-2 flex justify-center">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora py-3 px-15">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        1º
                    </div>
                    <img 
                        src="/img/locution/characters/aladdin.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                    <h2 class="mb-2 w-[18rem] leading-[1.3rem] font-noto-sans font-extrabold text-blue-marinho text-lg uppercase italic">
                        Título de música foda que todo mundo conhece
                    </h2>
                    <div class="font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Cantor/Banda:
                        </span>
                        Teste de voz
                    </div>
                    <div class="-mt-[0.2rem] font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Anime:
                        </span>
                        Teste de voz
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora py-3 px-15">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        2º
                    </div>
                    <img 
                        src="/img/locution/characters/aladdin.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                    <h2 class="mb-2 w-[18rem] leading-[1.3rem] font-noto-sans font-extrabold text-blue-marinho text-lg uppercase italic">
                        Título de música foda que todo mundo conhece
                    </h2>
                    <div class="font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Cantor/Banda:
                        </span>
                        Teste de voz
                    </div>
                    <div class="-mt-[0.2rem] font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Anime:
                        </span>
                        Teste de voz
                    </div>
                </div>
            </div>
            <div class="flex justify-start">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora">
                    <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora py-3 px-15">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        3º
                    </div>
                    <img 
                        src="/img/locution/characters/aladdin.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                    <h2 class="mb-2 w-[18rem] leading-[1.3rem] font-noto-sans font-extrabold text-blue-marinho text-lg uppercase italic">
                        Título de música foda que todo mundo conhece
                    </h2>
                    <div class="font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Cantor/Banda:
                        </span>
                        Teste de voz
                    </div>
                    <div class="-mt-[0.2rem] font-noto-sans text-sm text-blue-marinho">
                        <span class="font-medium">
                            Anime:
                        </span>
                        Teste de voz
                    </div>
                </div>
                </div>
            </div> 
        </div>
    </Section>
{/if}
