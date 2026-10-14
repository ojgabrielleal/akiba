<script>
    import { router } from "@inertiajs/svelte";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let post = {};
    export let comments = [];
    export let total = 0;

    let editingUuid = null;
    let editComment = "";

    $: commentLabel = total === 1 ? "1 comentário" : `${total} comentários`;
    $: commentBasePath = post?.slug ? `/materia/${post.slug}` : null;

    const refreshOptions = {
        only: ["post", "posts"],
        preserveScroll: true,
    };

    function startEdit(item) {
        editingUuid = item.uuid;
        editComment = item.comment ?? "";
    }

    function cancelEdit() {
        editingUuid = null;
        editComment = "";
    }

    function submitEdit(item) {
        if (!commentBasePath) return;

        router.patch(
            `${commentBasePath}/comment/${item.uuid}`,
            { comment: editComment },
            {
                ...refreshOptions,
                onSuccess: cancelEdit,
            },
        );
    }

    function deleteComment(item) {
        if (!commentBasePath) return;

        router.delete(`${commentBasePath}/comment/${item.uuid}`, refreshOptions);
    }

    function moderateComment(item, action, method = "patch") {
        if (!commentBasePath) return;

        const url = `${commentBasePath}/comment/${item.uuid}/${action}`;

        if (method === "delete") {
            router.delete(url, refreshOptions);
            return;
        }

        router.patch(url, {}, refreshOptions);
    }
</script>

<div class="overflow-hidden rounded-md bg-suspense-aurora">
    <div class="flex items-center justify-between gap-3 border-b border-blue-marinho/10 px-3.5 py-3 sm:px-4">
        <p class="text-xs font-black uppercase italic text-orange-citric">Comentários</p>
        <span class="rounded-full bg-blue-marinho/8 px-3 py-1 text-[0.68rem] font-black uppercase italic text-blue-marinho/70">{commentLabel}</span>
    </div>
    {#if comments.length}
        <div class="grid gap-3 p-3.5 sm:p-4">
            {#each comments as item}
                {@const isHidden = item.status === "hidden"}
                {@const isPending = item.status === "pending"}
                {@const isEditing = editingUuid === item.uuid}
                {@const canOwnerDelete = item.can_delete && !item.can_moderate_delete}
                <article class="grid gap-3 rounded-md border border-blue-marinho/10 bg-neutral-white p-3 shadow-[0_0.5rem_1.25rem_rgba(0,0,20,0.06)] sm:grid-cols-[2.75rem_minmax(0,1fr)]">
                    <div class="flex items-start gap-3 sm:block">
                        <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-orange-citric bg-blue-marinho">
                            <img src={resolvePlaceholderImage(item.author?.avatar, "avatar", item.author?.gender)} alt="" aria-hidden="true" class="h-full w-full scale-125 object-cover object-top" />
                        </div>
                        <div class="min-w-0 sm:hidden">
                            <p class="truncate text-xs font-black uppercase italic text-blue-marinho">{item.author?.name ?? "Akiba ID"}</p>
                            <p class="mt-0.5 text-[0.68rem] font-bold text-blue-marinho/50">{item.created_at}{item.is_edited ? " · editado" : ""}</p>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="hidden min-w-0 sm:block">
                                <p class="truncate text-xs font-black uppercase italic text-blue-marinho">{item.author?.name ?? "Akiba ID"}</p>
                                <p class="mt-0.5 text-[0.68rem] font-bold text-blue-marinho/50">{item.created_at}{item.is_edited ? " · editado" : ""}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                {#if isPending}
                                    <span class="rounded bg-orange-amber px-2 py-1 text-[0.62rem] font-black uppercase italic text-blue-marinho">Pendente</span>
                                {:else if isHidden}
                                    <span class="rounded bg-blue-marinho px-2 py-1 text-[0.62rem] font-black uppercase italic text-suspense-aurora">Oculto</span>
                                {/if}
                                {#if item.can_edit}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-orange-citric focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric" aria-label="Editar comentário" on:click={() => startEdit(item)}>
                                        <img src="/svg/edit.svg" alt="" aria-hidden="true" class="w-3.5 brightness-0" />
                                    </button>
                                {/if}
                                {#if canOwnerDelete}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-red-crimson focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-crimson" aria-label="Apagar comentário" on:click={() => deleteComment(item)}>
                                        <img src="/svg/trash.svg" alt="" aria-hidden="true" class="w-3.5 brightness-0" />
                                    </button>
                                {/if}
                                {#if item.can_approve && isPending}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-orange-citric focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric" aria-label="Aprovar comentário" on:click={() => moderateComment(item, "approve")}>
                                        <img src="/svg/eye.svg" alt="" aria-hidden="true" class="w-3.5 brightness-0" />
                                    </button>
                                {/if}
                                {#if item.can_hide && !isHidden}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-orange-citric focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric" aria-label="Ocultar comentário" on:click={() => moderateComment(item, "hide")}>
                                        <img src="/svg/close.svg" alt="" aria-hidden="true" class="w-3 brightness-0" />
                                    </button>
                                {/if}
                                {#if item.can_restore && isHidden}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-orange-citric focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric" aria-label="Restaurar comentário" on:click={() => moderateComment(item, "restore")}>
                                        <img src="/svg/return.svg" alt="" aria-hidden="true" class="w-3.5 brightness-0" />
                                    </button>
                                {/if}
                                {#if item.can_moderate_delete}
                                    <button type="button" class="flex size-7 cursor-pointer items-center justify-center rounded bg-blue-marinho/5 transition hover:bg-red-crimson focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-crimson" aria-label="Excluir comentário definitivamente" on:click={() => moderateComment(item, "moderate", "delete")}>
                                        <img src="/svg/trash.svg" alt="" aria-hidden="true" class="w-3.5 brightness-0" />
                                    </button>
                                {/if}
                            </div>
                        </div>

                        {#if isEditing}
                            <form class="mt-2 grid gap-2" on:submit|preventDefault={() => submitEdit(item)}>
                                <textarea bind:value={editComment} rows="3" maxlength="1000" class="min-h-20 w-full resize-none rounded border border-blue-marinho/15 bg-suspense-aurora px-3 py-2 text-sm font-bold leading-6 text-blue-marinho focus:outline-none focus:ring-2 focus:ring-orange-citric"></textarea>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" class="cursor-pointer rounded-full bg-blue-marinho/10 px-3 py-1.5 text-xs font-black uppercase italic text-blue-marinho" on:click={cancelEdit}>Cancelar</button>
                                    <button type="submit" class="cursor-pointer rounded-full bg-orange-citric px-3 py-1.5 text-xs font-black uppercase italic text-blue-marinho disabled:cursor-not-allowed disabled:opacity-50" disabled={!editComment.trim()}>Salvar</button>
                                </div>
                            </form>
                        {:else}
                            <p class="mt-2 whitespace-pre-line break-words text-sm font-bold leading-6 text-blue-marinho/80">{item.comment}</p>
                        {/if}

                        {#if item.replies?.length}
                            <div class="mt-3 grid gap-2 border-l-2 border-orange-citric pl-3">
                                {#each item.replies as reply}
                                    <div class="rounded bg-blue-marinho/5 px-3 py-2">
                                        <p class="text-[0.68rem] font-black uppercase italic text-blue-marinho/70">{reply.author?.name ?? "Akiba ID"} · {reply.created_at}</p>
                                        <p class="mt-1 whitespace-pre-line break-words text-sm font-semibold leading-5 text-blue-marinho/75">{reply.comment}</p>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </article>
            {/each}
        </div>
    {:else}
        <div class="px-4 py-9 text-center">
            <p class="text-sm font-black uppercase italic text-blue-marinho">Sem comentários ainda</p>
            <p class="mt-1 text-sm font-semibold text-blue-marinho/55">Quando a conversa começar, ela aparece neste painel.</p>
        </div>
    {/if}
</div>
