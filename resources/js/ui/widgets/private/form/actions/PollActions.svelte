<script>
    export let status = null;
    export let can = [];

    import { Button } from "@/ui/components/private";
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-wrap flex-wrap items-center gap-3">
        <Button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            variant="success"
            size="sm"
        >
            {status === 'draft' ? 'Atualizar' : 'Rascunho'}
        </Button>
        {#if status !== 'published'}
            <Button
                aria-label="mandar pra avaliação"
                type="submit"
                value="revision"
                variant="accent"
                size="sm"
            >
                {status === 'revision' ? 'Atualizar' : 'Avaliação'}
            </Button>
        {/if}
        {#if status === 'revision' && can.approve}
            <Button
                aria-label="aprovar"
                type="submit"
                value="published"
                variant="review"
                size="sm"
            >
                Aprovar
            </Button>
        {:else if status === 'published'}
            <Button
                aria-label="publicar"
                type="submit"
                value="published"
                variant="info"
                size="sm"
            >
                Atualizar
            </Button>
        {:else if can.publish}
            <Button
                aria-label="publicar"
                type="submit"
                value="published"
                variant="info"
                size="sm"
            >
                Publicar
            </Button>
        {/if}
    </div>
{/if}
