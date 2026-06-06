<script>
    export let label = null;
    export let status = null;
    export let can = [];
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-col lg:flex-row lg:flex-wrap gap-4 justify-start">
        <button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho py-2 px-6 rounded-full bg-green-forest"
        >
            {status === 'draft' ? 'Atualizar rascunho' : 'Salvar como rascunho'}
        </button>
        {#if status !== 'published'}
            <button
                aria-label="mandar pra avaliação"
                type="submit"
                value="revision"
                class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-blue-marinho py-2 px-6 rounded-full bg-orange-citric"
            >
                {status === 'revision' ? 'Atualizar avaliação' : 'Enviar para avaliação'}
            </button>
        {/if}
        {#if status === 'revision' && can.approve}
            <button
                aria-label="aprovar"
                type="submit"
                value="published"
                class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-purple-mystic"
            >
                Aprovar {label}
            </button>
        {:else if status === 'published'}
            <button
                aria-label="atualizar"
                type="submit"
                value="published"
                class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
            >
                Atualizar {label}
            </button>
        {:else if can.publish}
            <button
                aria-label="publicar"
                type="submit"
                value="published"
                class="cursor-pointer font-noto-sans font-extrabold italic uppercase text-suspense-aurora py-2 px-6 rounded-full bg-blue-ocean"
            >
                Publicar {label}
            </button>
        {/if}
    </div>
{/if}
