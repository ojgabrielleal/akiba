<script>
    import { EditorialTitle, Tooltip } from "@/lib/components/public";
    import { resolveAge, resolvePlaceholderImage } from "@/lib/utils";

    export let listenerMonth = null;

    $: listener = listenerMonth?.current?.data ?? null;
</script>

<section class="bg-blue-night">
    <EditorialTitle title="Ouvinte do mês" compact />

    <div class="container-page pt-12 pb-20">
        {#if listener}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[18rem_1fr]">
                <img
                    src={resolvePlaceholderImage(listener.avatar, "avatar", listener.gender)}
                    class="h-72 w-full self-center rounded-md border-2 border-suspense-aurora object-cover object-center"
                    alt={listener.name}
                />
                <div class="rounded-md bg-gradient-blue-cerulean-glow p-4">
                    <div class="mb-3 grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-[1fr_1fr_0.3fr] lg:gap-5">
                        <div class="line-clamp-1 rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho">
                            {listener.name}
                        </div>
                        <div class="line-clamp-1 rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho">
                            {listener.address}
                        </div>
                        <div class="rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho">
                            {resolveAge(listener.birth_date)} anos
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                        <div class="relative flex h-55 flex-col justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-amber">
                            <div class="text-7xl font-extrabold">{listener.requests_total}</div>
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                {listener.requests_total > 1 ? "Pedidos Feitos" : "Pedido Feito"}
                            </div>
                        </div>
                        <div class="relative flex h-55 flex-col items-center justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-amber">
                            <img
                                src={resolvePlaceholderImage(listener.favorite_program.image, "program")}
                                class="w-44 rounded-md object-contain"
                                alt={listener.favorite_program.name}
                            />
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                Programa favorito
                            </div>
                        </div>
                        <div class="relative flex h-55 flex-col items-center justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-amber">
                            <Tooltip position="top">
                                <img
                                    src={resolvePlaceholderImage(listener.favorite_music.image, "placeholder")}
                                    class="size-30 rounded-md object-cover"
                                    alt={listener.favorite_music.production}
                                />
                                <span slot="content">{listener.favorite_music.production}</span>
                            </Tooltip>
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                Anime favorito
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {:else}
            <p class="text-center font-noto-sans text-lg font-extrabold italic uppercase text-neutral-gray">
                Nenhum ouvinte definido.
            </p>
        {/if}
    </div>
</section>
