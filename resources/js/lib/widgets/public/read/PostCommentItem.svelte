<script>
    import { router } from "@inertiajs/svelte";
    import { IconButton } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let post = {};
    export let item = {};
    export let oauth = null;
    export let depth = 0;

    let editing = false;
    let replying = false;
    let editComment = item.comment ?? "";
    let replyComment = "";

    $: replies = item.replies ?? [];
    $: canReply = Boolean(oauth?.authenticated);
    $: nested = depth > 0;

    const fallbackAvatar = (event, gender = null) => {
        event.currentTarget.src = resolvePlaceholderImage(null, "avatar", gender);
    };

    const cancelEdit = () => {
        editing = false;
        editComment = item.comment ?? "";
    };

    const submitEdit = () => {
        router.patch(`/materia/${post.slug}/comment/${item.uuid}`, {
            comment: editComment,
        }, {
            only: ["comments"],
            preserveScroll: true,
            onSuccess: () => {
                editing = false;
            },
        });
    };

    const submitReply = () => {
        router.post(`/materia/${post.slug}/comment`, {
            comment: replyComment,
            parent_uuid: item.uuid,
        }, {
            only: ["comments"],
            preserveScroll: true,
            onSuccess: () => {
                replyComment = "";
                replying = false;
            },
        });
    };

    const deleteComment = () => {
        router.delete(`/materia/${post.slug}/comment/${item.uuid}`, {
            only: ["comments"],
            preserveScroll: true,
        });
    };
</script>

<div>
    <div class="flex items-start gap-6">
        <div class={[
            "mt-2 shrink-0 overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow",
            nested ? "size-10" : "size-12",
        ]}>
            <img
                src={resolvePlaceholderImage(item.author?.avatar, "avatar", item.author?.gender)}
                alt=""
                aria-hidden="true"
                class="h-full w-full scale-125 object-cover object-top"
                on:error={(event) => fallbackAvatar(event, item.author?.gender)}
            />
        </div>

        <article class={[
            "relative min-w-0 flex-1 rounded-md bg-blue-ocean shadow-[0_8px_0_rgba(0,0,20,0.12)] before:absolute before:left-[-0.9rem] before:size-0 before:border-y-[0.85rem] before:border-r-[0.95rem] before:border-y-transparent before:border-r-blue-ocean before:content-['']",
            nested ? "p-3 before:top-[1.2rem]" : "p-4 before:top-[1.45rem]",
        ]}>
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class={[
                        "truncate font-black text-suspense-aurora uppercase italic",
                        nested ? "text-xs" : "text-sm",
                    ]}>
                        {item.author?.name}
                    </p>
                    <p class={[
                        "font-bold text-suspense-aurora/60",
                        nested ? "text-[0.68rem]" : "text-xs",
                    ]}>
                        {item.created_at}{item.is_edited ? " · editado" : ""}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap justify-end gap-px">
                    {#if canReply && !nested}
                        <IconButton
                            variant="reply"
                            label="Responder"
                            size="sm"
                            tone="neutral"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass="size-3.5 group-hover:filter-orange-citric"
                            class="group size-6 bg-transparent"
                            on:click={() => replying = !replying}
                        />
                    {/if}
                    {#if item.can_edit}
                        <IconButton
                            variant="edit"
                            label="Editar"
                            size="sm"
                            tone="neutral"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass="size-3.5 group-hover:filter-orange-citric"
                            class="group size-6 bg-transparent"
                            on:click={() => editing = true}
                        />
                    {/if}
                    {#if item.can_delete}
                        <IconButton
                            variant="trash"
                            label="Apagar"
                            size="sm"
                            tone="neutral"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass="size-3.5 group-hover:filter-orange-citric"
                            class="group size-6 bg-transparent"
                            on:click={deleteComment}
                        />
                    {/if}
                </div>
            </div>

            {#if editing}
                <form class="grid gap-3" on:submit|preventDefault={submitEdit}>
                    <textarea
                        bind:value={editComment}
                        rows="4"
                        maxlength="1000"
                        class="min-h-24 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-marinho px-4 py-3 text-sm font-bold text-suspense-aurora placeholder:text-suspense-aurora/45 focus:outline-none"
                    ></textarea>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            class="cursor-pointer rounded-full bg-blue-cerulean px-4 py-2 text-xs font-black text-suspense-aurora uppercase italic"
                            on:click={cancelEdit}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-full bg-orange-citric px-4 py-2 text-xs font-black text-blue-night uppercase italic disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={!editComment.trim()}
                        >
                            Salvar
                        </button>
                    </div>
                </form>
            {:else}
                <p class={[
                    "whitespace-pre-line font-medium leading-relaxed text-suspense-aurora",
                    nested ? "text-xs" : "text-sm",
                ]}>
                    {item.comment}
                </p>
            {/if}

            {#if replying}
                <form class="mt-4 grid gap-3" on:submit|preventDefault={submitReply}>
                    <textarea
                        bind:value={replyComment}
                        rows="3"
                        maxlength="1000"
                        placeholder="Escreva sua resposta..."
                        class="min-h-20 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-marinho px-4 py-3 text-sm font-bold text-suspense-aurora placeholder:text-suspense-aurora/45 focus:outline-none"
                    ></textarea>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            class="cursor-pointer rounded-full bg-blue-cerulean px-4 py-2 text-xs font-black text-suspense-aurora uppercase italic"
                            on:click={() => replying = false}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-full bg-orange-citric px-4 py-2 text-xs font-black text-blue-night uppercase italic disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={!replyComment.trim()}
                        >
                            Responder
                        </button>
                    </div>
                </form>
            {/if}
        </article>
    </div>

    {#if replies.length}
        <div class="mt-3 grid gap-3 border-l-2 border-blue-skywave/45 pl-4 sm:ml-[4.25rem] sm:pl-5">
            {#each replies as reply}
                <svelte:self post={post} item={reply} {oauth} depth={depth + 1} />
            {/each}
        </div>
    {/if}
</div>
