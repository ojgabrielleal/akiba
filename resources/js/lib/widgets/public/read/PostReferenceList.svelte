<script>
    export let references = [];

    $: referenceList = Array.isArray(references) ? references : references?.data ?? [];
</script>

{#if referenceList.length}
    <section class="mt-8 font-noto-sans uppercase">
        <h2 class="mb-3 text-xl leading-none font-normal text-orange-amber">
            Fontes de pesquisa
        </h2>
        <ul class="grid gap-2">
            {#each referenceList.slice(0, 2) as reference}
                <li>
                    <a
                        href={reference.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block rounded-md bg-blue-ocean px-4 py-3 text-sm font-black text-suspense-aurora transition duration-300 hover:-translate-y-0.5 hover:bg-blue-cerulean focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                    >
                        {reference.name}
                    </a>
                </li>
            {/each}
        </ul>
    </section>
{:else}
    <section class="mt-8 hidden font-noto-sans uppercase md:block md:invisible" aria-hidden="true">
        <div class="mb-3 text-xl leading-none font-normal text-orange-amber">
            Fontes de pesquisa
        </div>
        <div class="min-h-12 rounded-md px-4 py-3"></div>
    </section>
{/if}
