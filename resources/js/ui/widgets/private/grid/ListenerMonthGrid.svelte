<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Section, Offcanvas, Preview } from "@/ui/components/private";
    import { ListenerMonthForm } from "@/ui/widgets/private";
    import { listenerMonthPermissions } from "@/utils";

    $: ({ listenerMonth } = $page.props);

    let can = listenerMonthPermissions();

    let actions = [
        {
            title: "Definir",
            icon: "/svg/edit.svg",
            permission: can.set,
            onClick: () => setRanking(),
        },
    ];

    let offcanvasRef;
</script>

<Offcanvas bind:this={offcanvasRef} title="Atualizar ouvinte do mês">
    <div slot="content" let:close>
        <ListenerMonthForm {close} />
    </div>
</Offcanvas>

{#if listenerMonth}
    <Section {title} {actions}>
        <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
            <Preview
                name="cover"
            />
            <div class="bg-gradient-blue-cerulean-glow rounded-md p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-[2fr_1fr_1fr] gap-2 lg:gap-5 mb-3">
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase bg-suspense-aurora rounded-md">
                        Fernandinho chan
                    </div>
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase bg-suspense-aurora rounded-md">
                        Pelotas - RS
                    </div>
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase bg-suspense-aurora rounded-md">
                        25 ANUS
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="relative h-55 flex flex-col justify-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <div class="text-7xl font-extrabold">
                            25
                        </div>
                        <div class="absolute w-full bottom-1 left-1/2 -translate-1/2">
                            Pedidos Feitos
                        </div>
                    </div>
                    <div class="relative h-55 flex flex-col justify-center items-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <img 
                            src="/img/locution/program.webp"
                            class="w-44 object-contain"
                            alt=""
                        />
                        <div class="absolute w-full bottom-1 left-1/2 -translate-1/2">
                            Programa favorito
                        </div>
                    </div>
                    <div class="relative h-55 flex flex-col justify-center items-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <img 
                            src="https://m.media-amazon.com/images/I/71r8Wf5h0+L._AC_UF1000,1000_QL80_.jpg"
                            class="w-30 h-30 object-cover rounded-md"
                            alt=""
                        />
                        <div class="absolute w-full bottom-1 left-1/2 -translate-1/2">
                            Anime favorito
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Section>
{/if}
