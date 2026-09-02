<script>
    export let references = [];

    $: referenceList = Array.isArray(references) ? references : references?.data ?? [];
</script>

<section class="mt-8 font-noto-sans uppercase">
    <h2 class="mb-3 text-xl leading-none font-normal text-orange-amber">
        Fontes de pesquisa
    </h2>

    {#if referenceList.length}
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
    {:else}
        <p class="rounded-md border border-blue-skywave/30 px-4 py-5 text-center text-sm font-bold text-suspense-aurora/70">
            Sem fontes de pesquisa
        </p>
    {/if}
</section>
