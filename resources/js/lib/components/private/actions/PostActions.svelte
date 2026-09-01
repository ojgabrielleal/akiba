<script>
    export let status = null;
    export let processing = false;
    export let can = [];

    import { Button } from "@/lib/components/private";

    let activeAction = null;
    $: if (!processing) activeAction = null;
</script>

{#if can.create || can.update}
    <div class="w-full flex flex-col lg:flex-row lg:flex-wrap gap-4 justify-start">
        <Button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            variant="success"
            shape="pill"
            loading={processing && activeAction === "draft"}
            disabled={processing}
            on:click={() => activeAction = "draft"}
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
                loading={processing && activeAction === "revision"}
                disabled={processing}
                on:click={() => activeAction = "revision"}
            >
                {status === 'revision' ? 'Atualizar avaliação' : 'Enviar para avaliação'}
            </Button>
        {/if}
        {#if status === 'revision' && can.approve}
            <Button
                aria-label="aprovar"
                type="submit"
                value="published"
                variant="primary"
                shape="pill"
                loading={processing && activeAction === "published"}
                disabled={processing}
                on:click={() => activeAction = "published"}
            >
                Aprovar
            </Button>
        {:else if status === 'published'}
            <Button
                aria-label="atualizar"
                type="submit"
                value="published"
                variant="publish"
                shape="pill"
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
                variant="publish"
                shape="pill"
                loading={processing && activeAction === "published"}
                disabled={processing}
                on:click={() => activeAction = "published"}
            >
                Publicar
            </Button>
        {/if}
    </div>
{/if}
