<script>
    export let errors = [];
</script>

{#if errors.length}
    <div class="mb-6 rounded-md border border-red-crimson bg-red-crimson/10 px-4 py-3 font-noto-sans text-sm font-bold text-red-crimson">
        {#each errors as error}
            <p>{error}</p>
        {/each}
    </div>
{/if}
