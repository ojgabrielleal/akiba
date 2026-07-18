<script>
    export let status = null;
    export let can = [];

    import { Button } from "@/ui/components/private";
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-col lg:flex-row lg:flex-wrap gap-4 justify-start">
        <Button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            variant="success"
            shape="pill"
        >
            {status === 'draft' ? 'Atualizar rascunho' : 'Salvar como rascunho'}
        </Button>
        {#if status !== 'published'}
            <Button
                aria-label="mandar pra avaliação"
                type="submit"
                value="revision"
                variant="accent"
                shape="pill"
            >
                {status === 'revision' ? 'Atualizar avaliação' : 'Enviar para avaliação'}
            </Button>
        {/if}
        {#if status === 'revision' && can.approve}
            <Button
                aria-label="aprovar"
                type="submit"
                value="published"
                variant="review"
                shape="pill"
            >
                Aprovar
            </Button>
        {:else if status === 'published'}
            <Button
                aria-label="atualizar"
                type="submit"
                value="published"
                variant="info"
                shape="pill"
            >
                Atualizar
            </Button>
        {:else if can.publish}
            <Button
                aria-label="publicar"
                type="submit"
                value="published"
                variant="info"
                shape="pill"
            >
                Publicar
            </Button>
        {/if}
    </div>
{/if}
