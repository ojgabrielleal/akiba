<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { ListenerMonthForm } from "@/ui/widgets/private";
    import { listenerMonthPermissions, resolveAge, resolvePlaceholderImage } from "@/utils";

    $: ({ listenerMonth } = $page.props);

    let can = listenerMonthPermissions();
    let offcanvasRef;

    let actions = [
        {
            title: "Atualizar",
            icon: "/svg/edit.svg",
            permission: can.set,
            onClick: () => {
                offcanvasRef.open();
            },
        },
    ];
</script>

<Offcanvas bind:this={offcanvasRef} title={listenerMonth.found.name}>
    <div slot="content" let:close>
        <ListenerMonthForm {close} listenerMonthFound={listenerMonth.found} />
    </div>
</Offcanvas>

{#if listenerMonth}
    <Section {title} {actions}>
        <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-5">
            <img 
                src={resolvePlaceholderImage(listenerMonth.current.data.avatar, "avatar")}
                class="object-contain"
                alt={listenerMonth.current.data.name}
            />
            <div class="bg-gradient-blue-cerulean-glow rounded-md p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-[1fr_1fr_0.3fr] gap-2 lg:gap-5 mb-3">
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase line-clamp-1 px-4 bg-suspense-aurora rounded-md">
                        {listenerMonth.current.data.name}
                    </div>
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase line-clamp-1 px-4 bg-suspense-aurora rounded-md">
                        {listenerMonth.current.data.address}
                    </div>
                    <div class="font-noto-sans font-extrabold text-blue-marinho text-center italic uppercase bg-suspense-aurora px-4 rounded-md">
                        {resolveAge(listenerMonth.current.data.birthday)} anos
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="relative h-55 flex flex-col justify-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <div class="text-7xl font-extrabold">
                            {listenerMonth.current.data.requests_total}
                        </div>
                        <div class="absolute w-full bottom-1 left-1/2 -translate-1/2">
                            {listenerMonth.current.data.requests_total > 1 ? "Pedidos Feitos" : "Pedido Feito"}
                        </div>
                    </div>
                    <div class="relative h-55 flex flex-col justify-center items-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <img 
                            src={resolvePlaceholderImage(listenerMonth.current.data.favorite_program.image, "program")}
                            class="w-44 object-contain rounded-md"
                            alt={listenerMonth.current.data.favorite_program.name}
                        />
                        <div class="absolute w-full bottom-1 left-1/2 -translate-1/2">
                            Programa favorito
                        </div>
                    </div>
                    <div class="relative h-55 flex flex-col justify-center items-center bg-blue-marinho rounded-md font-noto-sans font-medium text-orange-amber text-center uppercase">
                        <img 
                            src={resolvePlaceholderImage(listenerMonth.current.data.favorite_music.image, "placeholder")}
                            class="w-30 h-30 object-cover rounded-md"
                            alt={listenerMonth.current.data.favorite_music.production}
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
