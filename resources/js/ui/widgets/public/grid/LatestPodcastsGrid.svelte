<script>
    import { Link } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";
    import { resolvePlaceholderImage } from "@/utils";

    export let podcasts = [];

    $: podcastList = Array.isArray(podcasts) ? podcasts : podcasts?.data ?? [];
    $: visiblePodcasts = podcastList.slice(0, 3);
</script>

{#if podcastList.length > 0}
    <Section title="Últimos podcasts" styles="container-page mb-10 overflow-hidden pb-10 lg:pb-6">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-end lg:gap-0 xl:grid-cols-[minmax(0,1fr)_25rem]">
            <ul class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {#each visiblePodcasts as podcast (podcast.uuid)}
                    <li class="min-w-0">
                        <Link
                            href="#"
                            aria-label={`Ouvir podcast: ${podcast.title}`}
                            class="group block rounded-md focus-visible:outline-none"
                        >
                            <article class="overflow-hidden rounded-md bg-orange-amber transition duration-300 ease-out group-hover:-translate-y-0.5 group-focus-visible:-translate-y-0.5 group-focus-visible:ring-2 group-focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none">
                                <img
                                    src={resolvePlaceholderImage(podcast.image, "placeholder")}
                                    alt=""
                                    aria-hidden="true"
                                    class="aspect-[4/3] w-full bg-neutral-gray object-cover lg:aspect-[1/1.02]"
                                />
                                <h3 class="px-3 py-2.5 font-noto-sans text-base leading-tight font-black text-blue-night uppercase italic">
                                    <span
                                        class="block overflow-hidden lg:min-h-[4.5rem]"
                                        style="-webkit-line-clamp: 4; -webkit-box-orient: vertical; display: -webkit-box;"
                                    >
                                        {podcast.title}
                                    </span>
                                </h3>
                            </article>
                        </Link>
                    </li>
                {/each}
            </ul>
            <div class="pointer-events-none relative hidden min-h-90 overflow-visible lg:block">
                <img
                    src="/img/pages/home/characters/podcast-host.webp"
                    alt=""
                    aria-hidden="true"
                    class="podcast-host-character absolute right-0 -bottom-5 h-[126%] w-[126%] object-contain object-right-bottom"
                    loading="lazy"
                />
            </div>
        </div>
    </Section>
{/if}

<style>
    .podcast-host-character {
        filter: drop-shadow(-1rem 1rem 1.35rem rgb(0 0 0 / 0.32));
        -webkit-mask-image: linear-gradient(to bottom, #000 0%, #000 66%, rgb(0 0 0 / 0.85) 76%, rgb(0 0 0 / 0.45) 88%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 0%, #000 66%, rgb(0 0 0 / 0.85) 76%, rgb(0 0 0 / 0.45) 88%, transparent 100%);
    }
</style>
