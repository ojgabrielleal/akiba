<script>
    export let title;
    export let variant = null;

    import { page, router } from "@inertiajs/svelte";
    import { Badge, EmptyState, GridList, IconButton, Offcanvas, Section } from "@/ui/components/private";
    import { UserForm, UserAccessForm } from "@/ui/widgets/private";
    import { resolvePlaceholderImage, userGridPermissions } from "@/utils";

    $: ({ users } = $page.props);

    let can = userGridPermissions();

    let offCanvasUserRef;
    let offCanvasUserAccessRef;
    let userSelected = null;
    $: accessOffcanvasTitle = userSelected ? userSelected.nickname : "Editar acessos";

    let actions = [
        {
            title: "Adicionar membro",
            icon: "/svg/plus.svg",
            permission: can.create && variant === "administration",
            onClick: () => offCanvasUserRef.open(),
        },
    ];

    const requestDeactivateUser = (user) => {
        router.patch(`/panel/administration/user/${user}/deactivate`, {},
            { preserveScroll: true },
        );
    };
</script>

<Offcanvas bind:this={offCanvasUserRef} title="Cadastrar membro">
    <div slot="content" let:close>
        <UserForm {close} />
    </div>
</Offcanvas>
<Offcanvas bind:this={offCanvasUserAccessRef} title={accessOffcanvasTitle}>
    <div slot="content" let:close>
        <UserAccessForm {userSelected} {close} />
    </div>
</Offcanvas>

{#if users}
    <Section {title} {actions}>
        {#if users.data.length > 0}
        <GridList preset="members" class="mt-16">
            {#each users.data as item}
                <li>
                    <article class="relative h-32 overflow-visible rounded-md bg-gradient-blue-cerulean-glow px-3 py-2">
                    <header class="relative z-10 max-w-[65%]">
                        <h3 class="truncate font-noto-sans text-xl font-extrabold uppercase italic text-suspense-aurora lg:text-2xl" title={item.nickname}>
                            {item.nickname}
                        </h3>
                        <p class="truncate font-noto-sans text-[0.65rem] font-semibold uppercase italic text-suspense-aurora" title={item.name}>
                            {item.name}
                        </p>
                    </header>
                    <img
                        class="absolute bottom-0 right-0 z-0 h-43 max-w-[58%] object-contain object-bottom"
                        src={resolvePlaceholderImage(item.avatar, "avatar", item.gender)}
                        alt={item.nickname}
                        loading="lazy"
                    />
                    <footer class="absolute inset-x-0 bottom-2 z-10 flex w-full items-end justify-between gap-2 px-2">
                        <div class="flex min-w-0 max-w-[65%] items-center gap-1">
                            {#if item.is_virtual}
                                <Badge variant="review" size="sm" class="min-w-0" title="Bot">
                                    <img
                                        src="/svg/robot.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="h-3.5 w-3.5 filter-suspense-aurora"
                                        loading="lazy"
                                    />
                                    Bot
                                </Badge>
                            {:else if item.highest_role}
                                <Badge variant="light" size="sm" class="min-w-0" title={item.highest_role.label}>
                                    {item.highest_role.label}
                                </Badge>
                            {/if}
                        </div>
                        <div class="flex shrink-0 gap-1" aria-label={`Ações de ${item.nickname}`}>
                            {#if can.update && variant === "administration"}
                                <IconButton
                                    href={`/panel/profile/${item.uuid}`}
                                    variant="edit"
                                    label="Editar perfil"
                                    size="sm"
                                    surface="dark"
                                    tone="accent"
                                />
                            {/if}
                            {#if !item.is_virtual && can.authority.update && variant === "administration"}
                                <IconButton
                                    variant="authority"
                                    label="Atualizar acessos"
                                    size="sm"
                                    on:click={() => { userSelected = item; offCanvasUserAccessRef.open(); }}
                                />
                            {/if}
                            {#if can.deactivate && variant === "administration"}
                                <IconButton
                                    variant="trash"
                                    label="Desativar membro"
                                    size="sm"
                                    surface="dark"
                                    on:click={() => requestDeactivateUser(item.uuid)}
                                />
                            {/if}
                        </div>
                    </footer>
                    </article>
                </li>
            {/each}
        </GridList>
        {:else}
            <EmptyState
                title="Nenhum membro encontrado"
                description="Os membros ativos aparecerão aqui."
            />
        {/if}
    </Section>
{/if}
