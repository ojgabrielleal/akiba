<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Section, Preview } from "@/ui/components/private";
    import { musicPermissions } from "@/utils";

    $: ({ musicRanking } = $page.props);

    let can = musicPermissions();

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
    <Section {title}>
        <div class="grid grid-cols-2 gap-20">
            <div class="col-span-2 flex justify-center">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        1º
                    </div>
                    <img 
                        src="/img/locution/characters/yui.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                    <h2 class="font-noto-sans font-extrabold">
                        Título de música foda que todo mundo conhece
                    </h2>
                </div>
            </div>
            <div class="flex justify-end">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        2º
                    </div>
                    <img 
                        src="/img/locution/characters/yui.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                </div>
            </div>
            <div class="flex justify-start">
                <div class="relative w-130 h-30 rounded-md bg-gradient-orange-morning-aurora">
                    <div class="absolute -left-12 top-1/2 -translate-y-1/2 pl-3 w-23 h-23 font-noto-sans font-extrabold text-[2.6rem] text-suspense-aurora italic uppercase flex items-center justify-center bg-no-repeat bg-cover" style="background-image: url('/svg/star.svg')">
                        3º
                    </div>
                    <img 
                        src="/img/locution/characters/yui.webp"
                        alt=""
                        aria-hidden="true"
                        class="w-40 h-40 absolute right-0 bottom-0"
                    />
                </div>
            </div> 
        </div>
    </Section>
{/if}
