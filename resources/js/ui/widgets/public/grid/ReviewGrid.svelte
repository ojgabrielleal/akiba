<script>
    export let title;
    export let variant = "home";

    import { page, Link } from "@inertiajs/svelte";
    import { Section } from "@/ui/components/public";

    $: ({ reviews } = $page.props);
</script>

{#if variant === "home" && reviews.data.length > 0}
    <Section {title}>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
            {#each reviews.data as item}
                <Link
                    title=""
                    aria-label=""
                    href={`review/${item.slug}`}
                    class="aspect-square sm:aspect-auto sm:h-60 bg-orange-citric rounded-t-xl rounded-b-md relative"
                >
                    <img
                        src={item.cover}
                        alt={item.title}
                        class="w-full h-[90%] sm:h-50 object-cover rounded-t-md"
                    />
                    <div class="p-2">
                        <h1 class="text-lg text-center text-blue-night font-noto-sans font-extrabold italic uppercase line-clamp-1">
                            {item.title}
                        </h1>
                    </div>
                </Link>
            {/each}
        </div>
    </Section>
{/if}
