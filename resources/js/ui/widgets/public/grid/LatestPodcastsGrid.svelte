<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";
    import { resolvePlaceholderImage } from "@/utils";

    $: podcasts = $page.props.podcasts?.data ?? [];
</script>

{#if podcasts.length > 0}
    <Section title="Últimos podcasts" styles="container-page mb-10 overflow-hidden">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_16rem]">
            <ul class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[repeat(3,minmax(0,17.5rem))]">
                {#each podcasts as podcast (podcast.uuid)}
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
                                    class="aspect-[6/5] w-full bg-neutral-gray object-cover"
                                />
                                <h3 class="px-3 py-3 font-noto-sans text-base leading-tight font-black text-blue-night uppercase italic">
                                    <span
                                        class="block overflow-hidden"
                                        style="-webkit-line-clamp: 3; -webkit-box-orient: vertical; display: -webkit-box;"
                                    >
                                        {podcast.title}
                                    </span>
                                </h3>
                            </article>
                        </Link>
                    </li>
                {/each}
            </ul>
            <div class="hidden min-h-64 rounded-md bg-neutral-gray transition duration-300 ease-out hover:scale-[1.02] motion-reduce:transform-none motion-reduce:transition-none lg:block"></div>
        </div>
    </Section>
{/if}
