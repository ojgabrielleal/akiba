<script>
    export let status = null;
    export let can = [];

    const buttonClasses = "cursor-pointer rounded-full px-6 py-2 font-noto-sans font-extrabold uppercase italic";
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-col lg:flex-row lg:flex-wrap gap-4 justify-start">
        <button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            class={`${buttonClasses} bg-green-forest text-blue-marinho`}
        >
            {status === 'draft' ? 'Atualizar rascunho' : 'Salvar como rascunho'}
        </button>
        {#if status !== 'published'}
            <button
                aria-label="mandar pra avaliação"
                type="submit"
                value="revision"
                class={`${buttonClasses} bg-orange-citric text-blue-marinho`}
            >
                {status === 'revision' ? 'Atualizar avaliação' : 'Enviar para avaliação'}
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
                aria-label="atualizar"
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
