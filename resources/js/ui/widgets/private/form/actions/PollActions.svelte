<script>
    export let status = null;
    export let can = [];
    export let processing = false;

    import { Button } from "@/ui/components/private";

    let activeAction = null;
    $: if (!processing) activeAction = null;
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-wrap flex-wrap items-center gap-3">
        <Button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            variant="success"
            size="sm"
            loading={processing && activeAction === "draft"}
            disabled={processing}
            on:click={() => activeAction = "draft"}
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
                loading={processing && activeAction === "revision"}
                disabled={processing}
                on:click={() => activeAction = "revision"}
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
                loading={processing && activeAction === "published"}
                disabled={processing}
                on:click={() => activeAction = "published"}
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
                loading={processing && activeAction === "published"}
                disabled={processing}
                on:click={() => activeAction = "published"}
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
                loading={processing && activeAction === "published"}
                disabled={processing}
                on:click={() => activeAction = "published"}
            >
                Publicar
            </Button>
        {/if}
    </div>
{/if}
