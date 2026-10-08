<script>
    import { EditorialTitle, Tooltip } from "@/lib/components/public";
    import { resolveAge, resolvePlaceholderImage, themeClass } from "@/lib/utils";

    export let listenerMonth = null;

    $: listener = listenerMonth?.current?.data ?? null;
    $: badges = listener?.badges ?? [];
    $: topAnime = listener?.top_anime?.name ? listener.top_anime : null;
    $: displayedAnime = topAnime ?? {
        name: listener?.favorite_music?.production,
        image: listener?.favorite_music?.image,
    };
</script>

<section class="bg-blue-night">
    <EditorialTitle title="Ouvinte do mês" compact topSpacing="" />

    <div class="container-page pt-12 pb-20">
        {#if listener}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[18rem_1fr]">
                <div class="h-72 w-full self-center overflow-hidden rounded-md bg-neutral-gray">
                    <img
                        src={resolvePlaceholderImage(listener.avatar, "avatar", listener.gender)}
                        class="h-full w-full border-0 object-cover object-center outline-none"
                        alt={listener.name}
                        on:error={(event) => event.currentTarget.remove()}
                    />
                </div>
                <div class="public-featured-card rounded-md bg-gradient-blue-cerulean-glow p-4">
                    <div class="mb-3 grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-[1fr_1fr_0.3fr] lg:gap-5">
                        <div class={["line-clamp-1 rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho", themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }), themeClass("text", "orange-citric", { theme: "light" })]}>
                            {listener.name}
                        </div>
                        <div class={["line-clamp-1 rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho", themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }), themeClass("text", "orange-citric", { theme: "light" })]}>
                            {listener.address}
                        </div>
                        <div class={["rounded-md bg-suspense-aurora px-4 text-center font-noto-sans font-extrabold italic uppercase text-blue-marinho", themeClass("bg", "suspense-aurora", { fixed: true, theme: "light" }), themeClass("text", "orange-citric", { theme: "light" })]}>
                            {resolveAge(listener.birth_date)} anos
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                        <div class="relative flex h-55 flex-col justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-citric">
                            <div class="text-7xl font-extrabold">{listener.requests_total}</div>
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                {listener.requests_total > 1 ? "Pedidos Feitos" : "Pedido Feito"}
                            </div>
                        </div>
                        <div class="relative flex h-55 flex-col items-center justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-citric">
                            <img
                                src={resolvePlaceholderImage(listener.favorite_program.image, "program")}
                                class="w-44 rounded-md object-contain"
                                alt={listener.favorite_program.name}
                            />
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                Programa favorito
                            </div>
                        </div>
                        <div class="relative flex h-55 flex-col items-center justify-center rounded-md bg-blue-marinho text-center font-noto-sans font-medium uppercase text-orange-citric">
                            <Tooltip position="top">
                                <img
                                    src={resolvePlaceholderImage(displayedAnime.image, "placeholder")}
                                    class="size-30 rounded-md object-cover"
                                    alt={displayedAnime.name}
                                />
                                <span slot="content">{displayedAnime.name}</span>
                            </Tooltip>
                            <div class="absolute bottom-1 left-1/2 w-full -translate-x-1/2">
                                Top 1 anime
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 rounded-md bg-blue-marinho p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="font-noto-sans text-sm font-black uppercase italic text-orange-citric">
                                Emblemas
                            </h2>
                            <span class="font-noto-sans text-xs font-extrabold uppercase italic text-suspense-aurora/65">
                                {badges.length}
                            </span>
                        </div>

                        {#if badges.length > 0}
                            <ul class="grid grid-cols-3 gap-x-3 gap-y-5 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6">
                                {#each badges as badge (badge.uuid)}
                                    <li class="min-w-0 text-center">
                                        <div class="mx-auto flex size-16 items-center justify-center overflow-hidden rounded-md bg-blue-night/55">
                                            {#if badge.image}
                                                <img
                                                    src={badge.image}
                                                    alt={badge.name}
                                                    class="h-full w-full object-contain"
                                                    loading="lazy"
                                                />
                                            {:else}
                                                <div class="flex h-full w-full items-center justify-center rounded-md bg-orange-amber font-noto-sans text-xl font-black italic text-blue-night">
                                                    ★
                                                </div>
                                            {/if}
                                        </div>
                                        <p class="mt-1 line-clamp-2 font-noto-sans text-[0.65rem] font-black leading-tight text-suspense-aurora" title={badge.name}>
                                            {badge.name}
                                        </p>
                                    </li>
                                {/each}
                            </ul>
                        {:else}
                            <div class="flex min-h-24 items-center justify-center rounded-md border border-dashed border-suspense-aurora/25 px-4 text-center">
                                <p class="font-noto-sans text-sm font-semibold text-suspense-aurora/60">
                                    Nenhum emblema conquistado ainda.
                                </p>
                            </div>
                        {/if}
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
