<script>
    export let status = null;
    export let can = [];

    const buttonClasses = "cursor-pointer rounded-md px-3 py-1 text-center font-noto-sans text-sm font-extrabold uppercase italic leading-tight";
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-wrap flex-wrap items-center gap-3">
        <button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            class={`${buttonClasses} bg-green-forest text-blue-marinho`}
        >
            {status === 'draft' ? 'Atualizar' : 'Rascunho'}
        </button>
        {#if status !== 'published'}
            <button
                aria-label="mandar pra avaliação"
                type="submit"
                value="revision"
                class={`${buttonClasses} bg-orange-citric text-blue-marinho`}
            >
                {status === 'revision' ? 'Atualizar' : 'Avaliação'}
            </button>
        {/if}
        {#if status === 'revision' && can.approve}
            <button
                aria-label="aprovar"
                type="submit"
                value="published"
                class={`${buttonClasses} bg-purple-mystic text-suspense-aurora`}
            >
                Aprovar
            </button>
        {:else if status === 'published'}
            <button
                aria-label="publicar"
                type="submit"
                value="published"
                class={`${buttonClasses} bg-blue-ocean text-suspense-aurora`}
            >
                Atualizar
            </button>
        {:else if can.publish}
            <button
                aria-label="publicar"
                type="submit"
                value="published"
                class={`${buttonClasses} bg-blue-ocean text-suspense-aurora`}
            >
                Publicar
            </button>
        {/if}
    </div>
{/if}
