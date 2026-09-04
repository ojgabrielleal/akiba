<script>
    import { router, useForm } from "@inertiajs/svelte";
    import { Button, EmptyState, FormField, Modal, Pagination, Section, TextArea } from "@/lib/components/private";
    import { hasPermission } from "@/lib/utils";

    export let title;
    export let submissions = null;

    let modalRef;
    let selectedSubmission = null;
    let reviewingSubmission = null;
    let deletingSubmission = null;
    const commentForm = useForm({ comment: "" });

    $: canReview = hasPermission("form.submission.review");
    $: if (selectedSubmission && submissions?.data) {
        const updatedSubmission = submissions.data.find((item) => item.uuid === selectedSubmission.uuid);

        if (updatedSubmission && updatedSubmission !== selectedSubmission) {
            selectedSubmission = updatedSubmission;
        }
    }

    const fieldLabels = {
        role: "Cargo",
        nickname: "Nick",
        whatsapp: "WhatsApp",
        age: "Idade",
        portfolio: "Trabalhos",
        interview_time: "Horário da pré-entrevista",
        message: "Motivo",
        event_uuid: "Evento UUID",
        event_title: "Evento",
        event_name: "Nome do evento",
        city: "Cidade",
        state: "Estado",
        social_links: "Redes sociais",
    };

    const statusLabels = {
        pending: "Pendente",
        approved: "Aprovado",
        rejected: "Reprovado",
    };

    const formTypeLabels = {
        recruitment: "Recrutamento",
        complaint: "Reclamação",
        contact: "Contato",
        event_registration: "Informação de evento",
    };

    const statusClasses = {
        pending: "bg-orange-amber text-blue-marinho",
        approved: "bg-green-mint text-blue-marinho",
        rejected: "bg-red-crimson text-suspense-aurora",
    };

    const payloadEntries = (payload) =>
        Object.entries(payload ?? {}).filter(([, value]) => value !== null && value !== "");

    const modalTitle = (submission) =>
        formTypeLabels[submission?.form_type] ?? "Formulário recebido";

    const submissionSubject = (submission) =>
        submission?.form_type === "event_registration"
            ? "Informações de evento"
            : (submission?.subject ?? submission?.form_type);

    const legacyEventName = (submission) =>
        /^Cadastro no evento:\s*/i.test(submission?.subject ?? "")
            ? submission.subject.replace(/^Cadastro no evento:\s*/i, "").trim()
            : null;

    const submissionTitle = (submission) =>
        submission?.form_type === "event_registration"
            ? (submission?.payload?.event_name ?? submission?.payload?.event_title ?? legacyEventName(submission) ?? submission?.name)
            : submission?.name;

    const submissionSubtitle = (submission) =>
        submission?.form_type === "event_registration"
            ? ([submission?.payload?.city, submission?.payload?.state].filter(Boolean).join(" - ") || submission?.contact)
            : submission?.contact;

    const openSubmission = (submission) => {
        selectedSubmission = submission;
        modalRef.open();
    };

    const review = (submission, action, close = () => {}) => {
        router.patch(`/panel/administration/form-submission/${submission.uuid}/${action}`, {}, {
            preserveScroll: true,
            preserveState: true,
            only: ["formSubmissions", "flash"],
            onStart: () => reviewingSubmission = `${submission.uuid}-${action}`,
            onSuccess: () => close(),
            onFinish: () => reviewingSubmission = null,
        });
    };

    const submitComment = (submission) => {
        $commentForm.post(`/panel/administration/form-submission/${submission.uuid}/comment`, {
            preserveScroll: true,
            preserveState: true,
            only: ["formSubmissions", "flash"],
            onSuccess: () => $commentForm.reset("comment"),
        });
    };

    const destroySubmission = (submission, close = () => {}) => {
        router.delete(`/panel/administration/form-submission/${submission.uuid}`, {
            preserveScroll: true,
            preserveState: true,
            only: ["formSubmissions", "flash"],
            onStart: () => deletingSubmission = submission.uuid,
            onSuccess: () => {
                selectedSubmission = null;
                close();
            },
            onFinish: () => deletingSubmission = null,
        });
    };
</script>

<Modal bind:this={modalRef} title={modalTitle(selectedSubmission)} size="lg">
    <div slot="content" let:close>
        {#if selectedSubmission}
            <article class="text-blue-marinho">
                <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-noto-sans text-xs font-black uppercase italic text-orange-amber">
                            {submissionSubject(selectedSubmission)}
                        </p>
                        <h3 class="mt-1 break-words font-noto-sans text-2xl font-black uppercase italic leading-7">
                            {submissionTitle(selectedSubmission)}
                        </h3>
                        <p class="mt-1 break-words text-sm font-semibold text-blue-skywave">
                            {submissionSubtitle(selectedSubmission)}
                        </p>
                    </div>

                    <span class={["rounded-full px-3 py-1 font-noto-sans text-xs font-black uppercase italic", statusClasses[selectedSubmission.status] ?? statusClasses.pending]}>
                        {statusLabels[selectedSubmission.status] ?? selectedSubmission.status}
                    </span>
                </header>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)]">
                    <section>
                        <h4 class="mb-3 font-noto-sans text-sm font-black uppercase italic text-blue-marinho">
                            Dados recebidos
                        </h4>
                        <dl class="grid gap-3">
                            {#each payloadEntries(selectedSubmission.payload) as [field, value]}
                                <div class="rounded-md bg-neutral-gray/20 p-4">
                                    <dt class="font-noto-sans text-[0.7rem] font-black uppercase italic text-neutral-gray">
                                        {fieldLabels[field] ?? field}
                                    </dt>
                                    <dd class="mt-1 whitespace-pre-wrap break-words text-sm font-semibold leading-6">
                                        {value}
                                    </dd>
                                </div>
                            {/each}
                        </dl>
                    </section>

                    <aside class="rounded-md border border-blue-marinho/10 bg-blue-marinho/5 p-4">
                        <h4 class="font-noto-sans text-sm font-black uppercase italic text-blue-marinho">
                            Comentários
                        </h4>

                        {#if selectedSubmission.comments?.length}
                            <div class="mt-3 grid max-h-60 gap-2 overflow-y-auto pr-1">
                                {#each selectedSubmission.comments as comment}
                                    <article class="rounded-md bg-blue-marinho/5 p-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-noto-sans text-xs font-black uppercase italic text-blue-marinho">
                                                {comment.user?.nickname ?? comment.user?.name ?? "Equipe Akiba"}
                                            </p>
                                            <span class="text-[0.68rem] font-semibold text-blue-marinho/50">
                                                {comment.created_at}
                                            </span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-wrap break-words text-sm font-semibold leading-5 text-blue-marinho/80">
                                            {comment.comment}
                                        </p>
                                    </article>
                                {/each}
                            </div>
                        {:else}
                            <p class="mt-2 text-sm font-semibold text-blue-marinho/55">
                                Nenhum comentário registrado ainda.
                            </p>
                        {/if}

                        {#if canReview}
                            <form class="mt-5" on:submit|preventDefault={() => submitComment(selectedSubmission)}>
                                <FormField for="comment" label="Novo comentário" error={$commentForm.errors.comment} spacing="compact">
                                    <TextArea
                                        id="comment"
                                        name="comment"
                                        rows="3"
                                        maxlength="2000"
                                        bind:value={$commentForm.comment}
                                        error={$commentForm.errors.comment}
                                    />
                                </FormField>
                                <div class="flex justify-end">
                                    <Button
                                        type="submit"
                                        variant="accent"
                                        size="sm"
                                        loading={$commentForm.processing}
                                        disabled={!$commentForm.comment?.trim()}
                                    >
                                        Comentar
                                    </Button>
                                </div>
                            </form>
                        {/if}

                        <div class="mt-5 border-t border-blue-marinho/10 pt-4">
                            <h4 class="font-noto-sans text-sm font-black uppercase italic text-blue-marinho">
                                Moderação
                            </h4>
                            <div class="mt-3 flex flex-wrap items-center gap-2 overflow-visible">
                                {#if canReview}
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        class="size-8 p-0!"
                                        aria-label="Excluir formulário"
                                        loading={deletingSubmission === selectedSubmission.uuid}
                                        on:click={() => destroySubmission(selectedSubmission, close)}
                                    >
                                        <img
                                            src="/svg/trash.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="size-5 filter-suspense-aurora"
                                        />
                                    </Button>
                                {/if}

                                {#if selectedSubmission.status === "pending" && canReview}
                                    <Button
                                        variant="success"
                                        size="sm"
                                        class="size-8 p-0!"
                                        aria-label="Aprovar formulário"
                                        loading={reviewingSubmission === `${selectedSubmission.uuid}-approve`}
                                        on:click={() => review(selectedSubmission, "approve", close)}
                                    >
                                        <img
                                            src="/svg/like.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="size-5 filter-suspense-aurora"
                                        />
                                    </Button>
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        class="size-8 p-0!"
                                        aria-label="Reprovar formulário"
                                        loading={reviewingSubmission === `${selectedSubmission.uuid}-reject`}
                                        on:click={() => review(selectedSubmission, "reject", close)}
                                    >
                                        <img
                                            src="/svg/angry.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="size-5 invert brightness-0"
                                        />
                                    </Button>
                                {/if}
                            </div>
                        </div>
                    </aside>
                </div>

                <footer class="mt-6 flex flex-wrap items-center justify-between gap-3 overflow-visible border-t border-blue-marinho/10 pt-4">
                    <p class="text-xs font-semibold text-blue-marinho/55">
                        Recebido em {selectedSubmission.created_at}
                    </p>

                    {#if selectedSubmission.status !== "pending"}
                        <p class="text-xs font-semibold text-blue-marinho/55">
                            Revisado em {selectedSubmission.reviewed_at}
                        </p>
                    {/if}
                </footer>
            </article>
        {/if}
    </div>
</Modal>

{#if submissions}
    <Section {title}>
        {#if submissions.data.length}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {#each submissions.data as submission}
                    <button
                        type="button"
                        class="group min-h-36 cursor-pointer rounded-md bg-blue-night p-4 text-left text-suspense-aurora transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-blue-ocean focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                        on:click={() => openSubmission(submission)}
                    >
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <p class="min-w-0 font-noto-sans text-xs font-black uppercase italic text-orange-amber">
                                {submissionSubject(submission)}
                            </p>
                            <span class={["shrink-0 rounded-full px-3 py-1 font-noto-sans text-[0.65rem] font-black uppercase italic", statusClasses[submission.status] ?? statusClasses.pending]}>
                                {statusLabels[submission.status] ?? submission.status}
                            </span>
                        </div>

                        <h3 class="line-clamp-1 font-noto-sans text-xl font-black uppercase italic">
                            {submissionTitle(submission)}
                        </h3>
                        <p class="mt-1 line-clamp-1 text-sm font-semibold text-blue-skywave">
                            {submissionSubtitle(submission)}
                        </p>

                        <p class="mt-5 text-xs font-semibold text-suspense-aurora/50">
                            Recebido em {submission.created_at}
                        </p>
                    </button>
                {/each}
            </div>
        {:else}
            <EmptyState
                title="Nenhum formulário recebido"
                description="As inscrições e outros contatos aparecerão aqui."
            />
        {/if}
        <Pagination
            pages={submissions}
            only={["formSubmissions"]}
            loadingLabel="Carregando formulários..."
        />
    </Section>
{/if}
