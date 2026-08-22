<script>
    import { router } from "@inertiajs/svelte";
    import { IconButton } from "@/lib/components/public";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let post = {};
    export let item = {};
    export let oauth = null;
    export let depth = 0;
    export let isLast = false;

    let editing = false;
    let replying = false;
    let editComment = item.comment ?? "";
    let replyComment = "";

    $: replies = item.replies ?? [];
    $: canReply = Boolean(oauth?.authenticated);
    $: nested = depth > 0;
    $: isHidden = item.status === "hidden";
    $: isPending = item.status === "pending";
    $: canOwnerDelete = item.can_delete && !item.can_moderate_delete;
    $: canShowModeration = item.can_approve || item.can_hide || item.can_restore || item.can_moderate_delete;
    $: actionIconClass = "public-comment-action-icon h-[17px] w-[17px] object-contain opacity-70 group-hover:filter-orange-amber group-hover:opacity-100";

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

    const moderateComment = (action, method = "patch") => {
        const url = `/materia/${post.slug}/comment/${item.uuid}/${action}`;
        const options = {
            only: ["comments"],
            preserveScroll: true,
        };

        if (method === "delete") {
            router.delete(url, options);
            return;
        }

        router.patch(url, {}, options);
    };
</script>

<div class={["relative", nested ? "" : ""]}>
    <div class={["relative flex items-start", nested ? "gap-[26px]" : "gap-[28px]"]}>
        {#if nested}
            <svg
                class="absolute -left-[40px] top-0 hidden h-[28px] w-[32px] overflow-visible sm:block"
                viewBox="0 0 32 28"
                fill="none"
                aria-hidden="true"
            >
                <path
                    d="M1 0 V14 C1 21 6 24 13 24 H22"
                    stroke="#00a8ff"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path d="M22 19 L32 24 L22 29 Z" fill="#00a8ff" />
            </svg>
            {#if isLast}
                <span class="public-comment-thread-mask absolute -left-[40px] top-[31px] bottom-[-14px] z-20 hidden w-[2px] bg-[#000036] sm:block"></span>
            {/if}
        {/if}

        <div class={[
            "relative z-10 shrink-0 overflow-hidden rounded-full border-[3px] border-suspense-aurora bg-suspense-aurora",
            nested ? "mt-[1px] size-[44px]" : "mt-[2px] size-[56px]",
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
            "public-comment-card relative min-h-[58px] min-w-0 flex-1 rounded-[7px] border border-transparent bg-[#082b8f] px-[18px] py-[10px] shadow-none before:absolute before:left-[-20px] before:top-[16px] before:size-0 before:border-y-[13px] before:border-r-[21px] before:border-y-transparent before:border-r-[#082b8f] before:content-[''] after:absolute after:left-[-18px] after:top-[17px] after:size-0 after:border-y-[12px] after:border-r-[20px] after:border-y-transparent after:border-r-[#082b8f] after:content-['']",
            isHidden ? "opacity-70 outline outline-1 outline-blue-skywave/40" : "",
        ]}>
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 text-suspense-aurora">
                    <div class="flex min-w-0 flex-wrap items-center gap-x-[7px] gap-y-1 leading-none">
                        <p class="truncate text-[12px] font-black uppercase italic leading-none tracking-normal text-suspense-aurora">
                            {item.author?.name}
                        </p>
                        <span class="text-[11px] font-black leading-none text-suspense-aurora/55">
                            • {item.created_at}{item.is_edited ? " · editado" : ""}
                        </span>
                        {#if isHidden}
                            <span class="rounded-[2px] bg-blue-night/70 px-2 py-0.5 text-[10px] font-black leading-none text-orange-amber uppercase italic">
                                Oculto
                            </span>
                        {:else if isPending}
                            <span class="rounded-[2px] bg-orange-amber px-2 py-0.5 text-[10px] font-black leading-none text-blue-night uppercase italic">
                                Pendente
                            </span>
                        {/if}
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap justify-end gap-[8px] pt-[7px]">
                    {#if canReply && !nested}
                        <IconButton
                            variant="reply"
                            label="Responder"
                            size="sm"
                            tone="neutral"
                            surface="transparent"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass={actionIconClass}
                            class="public-comment-action-button group size-[20px]"
                            on:click={() => replying = !replying}
                        />
                    {/if}
                    {#if item.can_edit}
                        <IconButton
                            variant="edit"
                            label="Editar"
                            size="sm"
                            tone="neutral"
                            surface="transparent"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass={actionIconClass}
                            class="public-comment-action-button group size-[20px]"
                            on:click={() => editing = true}
                        />
                    {/if}
                    {#if canOwnerDelete}
                        <IconButton
                            variant="trash"
                            label="Apagar"
                            size="sm"
                            tone="neutral"
                            surface="transparent"
                            tooltipPosition="bottom"
                            interactive={false}
                            iconClass={actionIconClass}
                            class="public-comment-action-button group size-[20px]"
                            on:click={deleteComment}
                        />
                    {/if}
                    {#if canShowModeration}
                        {#if item.can_approve && isPending}
                            <IconButton
                                icon="/svg/eye.svg"
                                label="Aprovar"
                                size="sm"
                                tone="neutral"
                                surface="transparent"
                                tooltipPosition="bottom"
                                interactive={false}
                                iconClass={actionIconClass}
                                class="public-comment-action-button group size-[20px]"
                                on:click={() => moderateComment("approve")}
                            />
                        {/if}
                        {#if item.can_hide && !isHidden}
                            <IconButton
                                icon="/svg/close.svg"
                                label="Ocultar"
                                size="sm"
                                tone="neutral"
                                surface="transparent"
                                tooltipPosition="bottom"
                                interactive={false}
                                iconClass={actionIconClass}
                                class="public-comment-action-button group size-[20px]"
                                on:click={() => moderateComment("hide")}
                            />
                        {/if}
                        {#if item.can_restore && isHidden}
                            <IconButton
                                icon="/svg/return.svg"
                                label="Restaurar"
                                size="sm"
                                tone="neutral"
                                surface="transparent"
                                tooltipPosition="bottom"
                                interactive={false}
                                iconClass={actionIconClass}
                                class="public-comment-action-button group size-[20px]"
                                on:click={() => moderateComment("restore")}
                            />
                        {/if}
                        {#if item.can_moderate_delete}
                            <IconButton
                                variant="trash"
                                label="Excluir definitivamente"
                                size="sm"
                                tone="neutral"
                                surface="transparent"
                                tooltipPosition="bottom"
                                interactive={false}
                                iconClass={actionIconClass}
                                class="public-comment-action-button group size-[20px]"
                                on:click={() => moderateComment("moderate", "delete")}
                            />
                        {/if}
                    {/if}
                </div>
            </div>

            {#if editing}
                <form class="grid gap-3" on:submit|preventDefault={submitEdit}>
                    <textarea
                        bind:value={editComment}
                        rows="4"
                        maxlength="1000"
                        class="public-comment-input min-h-24 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-marinho px-4 py-3 text-sm font-bold text-suspense-aurora placeholder:text-suspense-aurora/45 focus:outline-none"
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
                            class="cursor-pointer rounded-full bg-orange-amber px-4 py-2 text-xs font-black text-blue-night uppercase italic disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={!editComment.trim()}
                        >
                            Salvar
                        </button>
                    </div>
                </form>
            {:else}
                <p class="mt-[6px] whitespace-pre-line text-[15px] font-semibold leading-[1.35] tracking-normal text-suspense-aurora">
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
                        class="public-comment-input min-h-20 w-full resize-none rounded-md border-2 border-blue-skywave/30 bg-blue-marinho px-4 py-3 text-sm font-bold text-suspense-aurora placeholder:text-suspense-aurora/45 focus:outline-none"
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
                            class="cursor-pointer rounded-full bg-orange-amber px-4 py-2 text-xs font-black text-blue-night uppercase italic disabled:cursor-not-allowed disabled:opacity-50"
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
        <div class="relative mt-[16px] grid gap-[14px] pl-[66px] before:absolute before:bottom-[35px] before:left-[26px] before:top-[-48px] before:w-[2px] before:bg-[#00a8ff] before:content-[''] max-sm:pl-[34px] max-sm:before:hidden">
            {#each replies as reply, index}
                <svelte:self post={post} item={reply} {oauth} depth={depth + 1} isLast={index === replies.length - 1} />
            {/each}
        </div>
    {/if}
</div>
