<script>
    export let title;

    import { page, Link } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";

    $: ({ featureds } = $page.props);
</script>

{#if featureds.data.length > 0}
    <Section {title}>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-10 xl:gap-5">
            {#each featureds.data as item}
                <Link
                    title=""
                    aria-label=""
                    href={`materia/${item.slug}`}
                    class="aspect-square sm:aspect-auto sm:h-50 sm:p-4 bg-blue-skywave rounded-md relative overflow-hidden sm:overflow-visible flex flex-col sm:flex-row"
                >
                    <img
                        src={item.cover}
                        alt={item.title}
                        class="absolute inset-0 w-full h-full object-cover sm:static sm:w-auto sm:h-auto sm:hidden"
                        loading="lazy"
                    />
                    <div class="absolute inset-0 bg-linear-to-t from-black to-transparent sm:hidden"></div>
                    <div class="relative z-10 p-3 mt-auto sm:mt-0 sm:p-0">
                        <h1 class="w-full sm:w-[50%] font-noto-sans font-extrabold text-white sm:text-suspense-aurora text-md sm:text-lg uppercase italic line-clamp-4 sm:line-clamp-5">
                            {item.title}
                        </h1>
                    </div>
                    <img
                        src={item.image}
                        alt={item.title}
                        class="hidden sm:block absolute bottom-0 right-0 sm:w-55"
                        loading="lazy"
                    />
                </Link>
            {/each}
        </div>
    </Section>
{/if}
