<script>
    import { router, useForm } from "@inertiajs/svelte";

    import { EmptyState, FormField, Modal, Section, TextArea } from "@/lib/components/private";
    import { resolvePlaceholderImage, audienceModerationPermissions } from "@/lib/utils";

    export let title = "Moderação de ouvintes";
    export let blockedRequesters = null;
    export let oauthAccounts = null;

    const can = audienceModerationPermissions();
    const modules = [
        { value: "song_requests", label: "Pedidos musicais", icon: "M9 18V5l12-2v13M9 8l12-2M9 18a3 3 0 1 1-3-3c1.66 0 3 1.34 3 3Zm12-2a3 3 0 1 1-3-3c1.66 0 3 1.34 3 3Z" },
        { value: "polls", label: "Enquetes", icon: "M5 20V10m7 10V4m7 16v-7" },
        { value: "comments", label: "Comentários", icon: "M21 11.5a8.5 8.5 0 0 1-8.5 8.5H4l-1 1V11.5a9 9 0 0 1 18 0ZM7 10h10M7 14h6" },
        { value: "enigmagame", label: "Enigma", icon: "M9 9a3 3 0 1 1 5 2c-1 1-2 1-2 3m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" },
    ];
    let blockModal;
    let search = "";
    let accountSearch = "";
    const blockForm = useForm({ oauth_account: "", modules: ["song_requests"], reason: "" });
    let blockingUuid = null;

    $: normalizedSearch = search.trim().toLocaleLowerCase("pt-BR");
    $: normalizedAccountSearch = accountSearch.trim().toLocaleLowerCase("pt-BR");
    $: filteredRequesters = (blockedRequesters ?? []).filter((requester) =>
        !normalizedSearch
        || requester.name?.toLocaleLowerCase("pt-BR").includes(normalizedSearch)
        || requester.module_label?.toLocaleLowerCase("pt-BR").includes(normalizedSearch)
    );
    $: filteredAccounts = (oauthAccounts ?? []).filter((account) =>
        !normalizedAccountSearch || account.name?.toLocaleLowerCase("pt-BR").includes(normalizedAccountSearch)
    );
    $: actions = [{
        title: "Bloquear ouvinte",
        icon: "/svg/bloqued.svg",
        permission: can.block && oauthAccounts !== null,
        onClick: () => {
            $blockForm.reset();
            $blockForm.clearErrors();
            blockModal?.open();
        },
    }];

    function isBlocked(account, selectedModules) {
        return selectedModules.length > 0 && selectedModules.every((module) =>
            (blockedRequesters ?? []).some((block) =>
                block.requester_uuid === account.uuid && block.module === module
            )
        );
    }

    function blockRequester(account) {
        if (blockingUuid || isBlocked(account, $blockForm.modules)) return;

        $blockForm.clearErrors();
        if (!$blockForm.modules.length) {
            $blockForm.setError("modules", "Selecione pelo menos um módulo.");
            return;
        }
        if (!$blockForm.reason.trim()) {
            $blockForm.setError("reason", "Informe o motivo do bloqueio.");
            return;
        }

        blockingUuid = account.uuid;
        $blockForm.oauth_account = account.uuid;
        $blockForm.post("/panel/administration/audience-block", {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => blockModal?.close(),
            onFinish: () => blockingUuid = null,
        });
    }

    function unblockRequester(id) {
        router.delete(`/panel/administration/song-request/requester/block/${id}`, {
            preserveScroll: true,
            preserveState: true,
        });
    }
</script>

{#if can.list && blockedRequesters !== null}
    <Modal bind:this={blockModal} title="Bloquear ouvinte" size="md">
        <div slot="content" class="flex max-h-[calc(90vh-5.75rem)] flex-col font-noto-sans text-blue-marinho sm:max-h-[calc(90vh-6.75rem)] [&>:not(ul)]:shrink-0">
            <fieldset>
                <legend class="mb-3 text-xs font-black uppercase italic">Bloquear nos módulos</legend>
                <p class="mb-3 text-xs text-blue-marinho/55">Selecione um ou mais módulos.</p>
                <div class="grid grid-cols-2 gap-2">
                    {#each modules as module (module.value)}
                        {@const selected = $blockForm.modules.includes(module.value)}
                        <label class={`relative grid min-w-0 cursor-pointer grid-cols-[1fr_auto] items-center gap-x-3 gap-y-2 rounded-lg border p-3 ${selected ? "border-orange-citric/60 bg-orange-citric/10" : "border-blue-marinho/10 bg-neutral-white"} ${$blockForm.processing ? "pointer-events-none opacity-50" : ""}`}>
                            <input type="checkbox" value={module.value} bind:group={$blockForm.modules} disabled={$blockForm.processing} class="peer sr-only" />
                            <span class={`flex size-9 shrink-0 items-center justify-center rounded-lg ${selected ? "bg-orange-citric/20 text-blue-marinho" : "bg-blue-marinho/5 text-blue-marinho/50"}`}>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true"><path d={module.icon} /></svg>
                            </span>
                            <span class="col-span-2 row-start-2 min-w-0 text-xs font-bold leading-snug">{module.label}</span>
                            <span class={`col-start-2 row-start-1 flex size-4 shrink-0 items-center justify-center rounded border ${selected ? "border-orange-citric bg-orange-citric" : "border-blue-marinho/20 bg-neutral-white"}`} aria-hidden="true">
                                {#if selected}
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" class="size-3"><path d="m3 8 3 3 7-7" /></svg>
                                {/if}
                            </span>
                        </label>
                    {/each}
                </div>
                {#if $blockForm.errors.modules}
                    <p role="alert" class="mt-2 text-sm text-red-crimson">{$blockForm.errors.modules}</p>
                {/if}
            </fieldset>
            {#if $blockForm.errors.oauth_account}
                <p role="alert" class="text-sm text-red-crimson">{$blockForm.errors.oauth_account}</p>
            {/if}

            <FormField for="audience-block-reason" label="Motivo do bloqueio" error={$blockForm.errors.reason} class="mt-5">
                <TextArea id="audience-block-reason" bind:value={$blockForm.reason} error={$blockForm.errors.reason} rows="3" maxlength="1000" required placeholder="Descreva por que este usuário será bloqueado" class="outline-none" />
            </FormField>

            <label for="oauth-account-search" class="mb-1.5 block text-xs font-black uppercase italic">Selecionar usuário</label>
            <div class="relative mb-4">
                <input
                    id="oauth-account-search"
                    type="search"
                    bind:value={accountSearch}
                    placeholder="Buscar conta OAuth"
                    class="h-10 w-full rounded-md border border-blue-marinho/20 bg-neutral-white px-4 pr-10 text-sm font-semibold text-blue-marinho placeholder:text-blue-marinho/40 outline-none"
                />
                <img src="/svg/search.svg" alt="" aria-hidden="true" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 filter-blue-marinho opacity-60" />
            </div>


            {#if filteredAccounts.length}
                <ul class="grid min-h-16 max-h-[40vh] shrink gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                    {#each filteredAccounts as account (account.uuid)}
                        {@const blocked = isBlocked(account, $blockForm.modules)}
                        <li>
                            <button
                                type="button"
                                class="group flex w-full cursor-pointer items-center gap-3 rounded-md border border-blue-marinho/10 bg-neutral-white p-2.5 text-left transition hover:bg-orange-citric/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric disabled:cursor-default disabled:opacity-50"
                                disabled={blocked || Boolean(blockingUuid)}
                                aria-label={blocked ? `${account.name} já está bloqueado nos módulos selecionados` : `Bloquear ${account.name}`}
                                on:click={() => blockRequester(account)}
                            >
                                <img src={resolvePlaceholderImage(account.avatar, "avatar")} alt="" aria-hidden="true" class="size-11 shrink-0 rounded-full border-2 border-orange-citric/60 object-cover object-top" loading="lazy" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black uppercase italic">{account.name}</span>
                                    <span class="block text-xs font-semibold text-blue-marinho/55">{blocked ? "Já bloqueado" : ({ discord: "Discord", google: "Google" }[account.provider] ?? account.provider)}</span>
                                </span>
                                <img src={blocked ? "/svg/verify.svg" : "/svg/bloqued.svg"} alt="" aria-hidden="true" class="size-5 shrink-0 filter-orange-citric" />
                            </button>
                        </li>
                    {/each}
                </ul>
            {:else}
                <EmptyState title="Nenhum usuário encontrado" description="Tente buscar usando outro nome." icon="/svg/search.svg" titleClass="text-blue-night" descriptionClass="text-blue-night/60" iconClass="filter-blue-marinho opacity-45" />
            {/if}

        </div>
    </Modal>

    <Section {title} {actions}>
        {#if blockedRequesters.length}
            <div class="mb-4 max-w-md">
                <label for="blocked-requester-search" class="sr-only">Buscar usuário bloqueado</label>
                <div class="relative">
                    <input id="blocked-requester-search" type="search" bind:value={search} placeholder="Buscar usuário ou módulo" class="h-10 w-full rounded-md border border-blue-skywave/25 bg-blue-ocean px-4 pr-10 font-noto-sans text-sm font-semibold text-suspense-aurora placeholder:text-suspense-aurora/45 outline-none" />
                    <img src="/svg/search.svg" alt="" aria-hidden="true" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 filter-orange-citric opacity-70" />
                </div>
            </div>

            {#if filteredRequesters.length}
                <ul class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {#each filteredRequesters as requester (requester.id)}
                        <li class="flex min-w-0 items-center gap-3 rounded-md border border-blue-skywave/15 bg-blue-ocean p-3 shadow-sm">
                            <img src={resolvePlaceholderImage(requester.avatar, "avatar")} alt="" aria-hidden="true" class="size-12 shrink-0 rounded-full border-2 border-orange-citric/70 object-cover object-top" loading="lazy" />
                            <div class="min-w-0 flex-1 font-noto-sans">
                                <p class="truncate text-sm font-black uppercase italic text-suspense-aurora" title={requester.name}>{requester.name}</p>
                                <p class="mt-0.5 text-xs font-black uppercase text-orange-morning">{requester.module_label}</p>
                                {#if requester.blocked_at}
                                    <p class="mt-0.5 text-xs font-semibold text-suspense-aurora/55">Quando: {requester.blocked_at}</p>
                                {/if}
                                {#if requester.blocked_by}<p class="mt-0.5 truncate text-xs font-semibold text-suspense-aurora/55">Bloqueado por {requester.blocked_by}</p>{/if}
                                <p class="mt-2 whitespace-pre-wrap break-words text-xs font-semibold text-suspense-aurora/80">Motivo: {requester.reason || "Motivo não informado"}</p>
                            </div>
                            {#if can.unblock}
                                <button type="button" class="shrink-0 cursor-pointer rounded-full bg-orange-citric px-3 py-1.5 font-noto-sans text-xs font-black text-blue-marinho transition hover:bg-orange-citric/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric focus-visible:ring-offset-2 focus-visible:ring-offset-blue-ocean" aria-label={`Desbloquear ${requester.name} em ${requester.module_label}`} on:click={() => unblockRequester(requester.id)}>Desbloquear</button>
                            {/if}
                        </li>
                    {/each}
                </ul>
            {:else}
                <EmptyState title="Nenhum usuário encontrado" description="Tente buscar usando outro nome ou módulo." icon="/svg/search.svg" />
            {/if}
        {:else}
            <EmptyState title="Nenhum usuário bloqueado" description="Os usuários bloqueados em algum módulo aparecerão aqui." />
        {/if}
    </Section>
{/if}
