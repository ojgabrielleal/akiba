<script>
    import { useForm } from "@inertiajs/svelte";

    import { FormField, TextArea, TextInput } from "@/lib/components/private";
    import { Button } from "@/lib/components/private";
    import { enigmagamePermissions } from "@/lib/utils";

    export let close = () => {};
    export let enigmagameSelected = null;
    export let hidePublish = false;

    const can = enigmagamePermissions();
    let activeAction = null;

    $: form = useForm({
        status: enigmagameSelected?.status ?? "draft",
        title: enigmagameSelected?.title ?? "",
        content: enigmagameSelected?.content ?? "",
        solution: enigmagameSelected?.solution ?? "",
    });
    $: if (!$form.processing) activeAction = null;

    function submit(event) {
        const method = enigmagameSelected ? "patch" : "post";
        const url = enigmagameSelected
            ? `/panel/media/enigmagame/${enigmagameSelected.uuid}`
            : "/panel/media/enigmagame";

        $form.status = event.submitter.value;
        $form[method](url, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.type !== "error") {
                    close();
                }
            },
        });
    }
</script>

<form on:submit|preventDefault={submit}>
    <FormField for="enigmagame-title" label="Título" error={$form.errors.title}>
        <TextInput
            id="enigmagame-title"
            name="title"
            variant="offcanvas"
            bind:value={$form.title}
            error={$form.errors.title}
            required
        />
    </FormField>

    <FormField for="enigmagame-content" label="Conteúdo do enigma" error={$form.errors.content}>
        <TextArea
            id="enigmagame-content"
            name="content"
            rows="6"
            bind:value={$form.content}
            error={$form.errors.content}
            required
        />
    </FormField>

    <FormField for="enigmagame-solution" label="Resposta do enigma" error={$form.errors.solution} spacing="section">
        <TextArea
            id="enigmagame-solution"
            name="solution"
            rows="4"
            bind:value={$form.solution}
            error={$form.errors.solution}
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
        {:else if can.publish && !hidePublish}
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
    </div>
</form>
