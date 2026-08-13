<script>
    import { router } from "@inertiajs/svelte";
    import { Button, EmptyState, Modal, Pagination, Section } from "@/lib/components/private";
    import { hasPermission } from "@/lib/utils";

    export let title;
    export let submissions = null;

    let modalRef;
    let selectedSubmission = null;
    let reviewingSubmission = null;

    $: canReview = hasPermission("form.submission.review");

    const fieldLabels = {
        role: "Cargo",
        nickname: "Nick",
        whatsapp: "WhatsApp",
        age: "Idade",
        portfolio: "Trabalhos",
        interview_time: "Horário da pré-entrevista",
        message: "Motivo",
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
</script>

<Modal bind:this={modalRef} title={modalTitle(selectedSubmission)} size="sm">
    <div slot="content" let:close>
        {#if selectedSubmission}
            <article class="text-blue-marinho">
                <header class="mb-5 flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-noto-sans text-xs font-black uppercase italic text-orange-amber">
                            {selectedSubmission.subject ?? selectedSubmission.form_type}
                        </p>
                        <h3 class="break-words font-noto-sans text-2xl font-black uppercase italic">
                            {selectedSubmission.name}
                        </h3>
                        <p class="break-words text-sm font-semibold text-blue-skywave">
                            {selectedSubmission.contact}
                        </p>
                    </div>

                    <span class={["rounded-full px-3 py-1 font-noto-sans text-xs font-black uppercase italic", statusClasses[selectedSubmission.status] ?? statusClasses.pending]}>
                        {statusLabels[selectedSubmission.status] ?? selectedSubmission.status}
                    </span>
                </header>

                <dl class="grid gap-3">
                    {#each payloadEntries(selectedSubmission.payload) as [field, value]}
                        <div class="rounded-md bg-neutral-gray/20 p-3">
                            <dt class="font-noto-sans text-[0.7rem] font-black uppercase italic text-neutral-gray">
                                {fieldLabels[field] ?? field}
                            </dt>
                            <dd class="mt-1 whitespace-pre-wrap break-words text-sm font-semibold leading-6">
                                {value}
                            </dd>
                        </div>
                    {/each}
                </dl>

                <footer class="mt-5 flex items-center justify-between gap-3 overflow-visible border-t border-blue-marinho/10 pt-4">
                    <p class="text-xs font-semibold text-blue-marinho/55">
                        Recebido em {selectedSubmission.created_at}
                    </p>

                    {#if selectedSubmission.status === "pending" && canReview}
                        <div class="flex shrink-0 gap-2 overflow-visible">
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
                        </div>
                    {:else}
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
                        class="group min-h-36 rounded-md bg-blue-night p-4 text-left text-suspense-aurora transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-blue-ocean focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber motion-reduce:transform-none motion-reduce:transition-none"
                        on:click={() => openSubmission(submission)}
                    >
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <p class="min-w-0 font-noto-sans text-xs font-black uppercase italic text-orange-amber">
                                {submission.subject ?? submission.form_type}
                            </p>
                            <span class={["shrink-0 rounded-full px-3 py-1 font-noto-sans text-[0.65rem] font-black uppercase italic", statusClasses[submission.status] ?? statusClasses.pending]}>
                                {statusLabels[submission.status] ?? submission.status}
                            </span>
                        </div>

                        <h3 class="line-clamp-1 font-noto-sans text-xl font-black uppercase italic">
                            {submission.name}
                        </h3>
                        <p class="mt-1 line-clamp-1 text-sm font-semibold text-blue-skywave">
                            {submission.contact}
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
