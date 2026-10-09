<script>
    import { useForm } from "@inertiajs/svelte";

    import { FormField, Preview, TextArea, TextInput } from "@/lib/components/private";
    import { Button } from "@/lib/components/private";
    import { enigmagamePermissions } from "@/lib/utils";

    export let close = () => {};
    export let enigmagameSelected = null;
    export let publishBlocked = false;

    const can = enigmagamePermissions();
    let activeAction = null;

    $: form = useForm({
        _method: enigmagameSelected ? "PATCH" : "POST",
        status: enigmagameSelected?.status ?? "draft",
        title: enigmagameSelected?.title ?? "",
        content: enigmagameSelected?.content ?? "",
        image: null,
        solution: enigmagameSelected?.solution ?? "",
        solution_image: null,
        solution_synopsis: enigmagameSelected?.solution_synopsis ?? "",
    });
    $: if (!$form.processing) activeAction = null;

    function blockPublish() {
        alert("Não é possível publicar porque já existe um enigma ativo.");
    }

    function submit(event) {
        const url = enigmagameSelected
            ? `/panel/media/enigmagame/${enigmagameSelected.uuid}`
            : "/panel/media/enigmagame";

        $form.status = event.submitter.value;
        $form.post(url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="image" label="Imagem do enigma" error={$form.errors.image} help="Imagem principal exibida junto do desafio no site.">
        <Preview
            size="default"
            tone="muted"
            color="muted"
            fit="cover"
            name="image"
            src={$form.image ?? enigmagameSelected?.image}
            oninput={(event) => ($form.image = event.target.files[0])}
            required={!enigmagameSelected}
            error={$form.errors.image}
        />
    </FormField>

    <FormField for="enigmagame-title" label="Título" error={$form.errors.title} help="Nome curto do enigma no painel e na área pública.">
        <TextInput
            id="enigmagame-title"
            name="title"
            variant="offcanvas"
            bind:value={$form.title}
            error={$form.errors.title}
            required
        />
    </FormField>

    <FormField for="enigmagame-content" label="Texto do enigma" error={$form.errors.content} spacing="section" help="A pista ou descrição que os ouvintes verão para tentar descobrir a resposta.">
        <TextArea
            id="enigmagame-content"
            name="content"
            rows="5"
            variant="offcanvas"
            bind:value={$form.content}
            error={$form.errors.content}
            required
        />
    </FormField>

    <FormField for="enigmagame-solution" label="Resposta correta" error={$form.errors.solution} spacing="section" help="Resposta oficial usada na revelação do enigma.">
        <TextArea
            id="enigmagame-solution"
            name="solution"
            rows="4"
            bind:value={$form.solution}
            error={$form.errors.solution}
        />
    </FormField>

    <FormField for="solution_image" label="Imagem da obra" error={$form.errors.solution_image} help="Imagem exibida quando a resposta for revelada. Pode ser capa, poster ou arte relacionada.">
        <Preview
            size="compact"
            tone="muted"
            color="muted"
            fit="cover"
            name="solution_image"
            src={$form.solution_image ?? enigmagameSelected?.solution_image}
            oninput={(event) => ($form.solution_image = event.target.files[0])}
            error={$form.errors.solution_image}
        />
    </FormField>

    <FormField for="enigmagame-solution-synopsis" label="Texto da revelação" error={$form.errors.solution_synopsis} spacing="section" help="Texto opcional mostrado junto da resposta, explicando a obra ou a ligação com a pista.">
        <TextArea
            id="enigmagame-solution-synopsis"
            name="solution_synopsis"
            rows="5"
            variant="offcanvas"
            bind:value={$form.solution_synopsis}
            error={$form.errors.solution_synopsis}
        />
    </FormField>

    <div class="flex flex-wrap items-center gap-3">
        <Button
            aria-label="salvar como rascunho"
            type="submit"
            value="draft"
            variant="success"
            size="sm"
            loading={$form.processing && activeAction === "draft"}
            disabled={$form.processing}
            on:click={() => activeAction = "draft"}
        >
            {enigmagameSelected ? ($form.status === "draft" ? "Atualizar" : "Rascunho") : "Salvar como rascunho"}
        </Button>

        {#if $form.status === "active"}
            <Button
                aria-label="atualizar publicado"
                type="submit"
                value="active"
                variant="publish"
                size="sm"
                loading={$form.processing && activeAction === "active"}
                disabled={$form.processing}
                on:click={() => activeAction = "active"}
            >
                Atualizar
            </Button>
        {:else if can.publish}
            {#if publishBlocked}
                <Button
                    aria-label="publicar"
                    type="button"
                    variant="publish"
                    size="sm"
                    disabled={$form.processing}
                    on:click={blockPublish}
                >
                    Publicar
                </Button>
            {:else}
                <Button
                    aria-label="publicar"
                    type="submit"
                    value="active"
                    variant="publish"
                    size="sm"
                    loading={$form.processing && activeAction === "active"}
                    disabled={$form.processing}
                    on:click={() => activeAction = "active"}
                >
                    Publicar
                </Button>
            {/if}
        {/if}
    </div>
</form>
