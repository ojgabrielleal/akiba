<script>
    export let title;
    export let variant = null;

    import { page, router } from "@inertiajs/svelte";
    import {
        Badge,
        Carousel,
        EmptyState,
        IconButton,
        Offcanvas,
        Section,
    } from "@/ui/components/private";
    import { RoleForm } from "@/ui/widgets/private";
    import { rolePermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = rolePermissions();
    let offCanvasRef;
    let roleSelected = null;

    const openRoleForm = (role = null) => {
        roleSelected = role;
        offCanvasRef.open();
    };

    const requestRemoveRole = (role) => {
        router.delete(`/panel/administration/role/${role.uuid}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    $: actions = [{
        title: "Criar cargo",
        icon: "/svg/plus.svg",
        permission: variant === "administration" && can.create,
        onClick: () => openRoleForm(),
    }];
</script>

<Offcanvas
    bind:this={offCanvasRef}
    title={roleSelected ? `Atualizar ${roleSelected.label}` : "Cadastrar cargo"}
>
    <div slot="content" let:close>
        <RoleForm {roleSelected} {close} />
    </div>
</Offcanvas>

<Section {title} {actions}>
    {#if roles?.data?.length}
        <Carousel label="Cargos da equipe">
            {#each roles.data as item (item.uuid)}
                <article class="flex h-44 w-40 shrink-0 flex-col rounded-md bg-blue-ocean">
                    <div class="flex items-center gap-1 px-2 pt-2">
                        <strong class="shrink-0 font-noto-sans text-2xl font-black leading-none text-suspense-aurora">
                            {item.members_total ?? 0}
                        </strong>
                        <Badge class="min-w-0 flex-1 px-2" title={item.label}>
                            {item.label}
                        </Badge>
                    </div>

                    <div class="flex min-h-0 flex-1 items-center justify-center px-4 py-2">
                        <img
                            src={item.icon ?? "/svg/dots.svg"}
                            alt=""
                            aria-hidden="true"
                            class="h-11 w-11 object-contain filter-orange-citric"
                            loading="lazy"
                        />
                    </div>

                    {#if variant === "administration" && (can.update || can.delete)}
                        <div class="flex h-10 items-center justify-end gap-1.5 rounded-b-md bg-blue-cerulean px-2">
                            {#if can.delete}
                                <IconButton
                                    variant="trash"
                                    label={`Remover ${item.label}`}
                                    size="sm"
                                    surface="dark"
                                    on:click={() => requestRemoveRole(item)}
                                />
                            {/if}
                            {#if can.update}
                                <IconButton
                                    variant="edit"
                                    label={`Editar ${item.label}`}
                                    size="sm"
                                    surface="dark"
                                    on:click={() => openRoleForm(item)}
                                />
                            {/if}
                        </div>
                    {/if}
                </article>
            {/each}
        </Carousel>
    {:else}
        <EmptyState
            title="Nenhum cargo por aqui"
            description="Cadastre um cargo para organizar as responsabilidades da equipe."
        />
    {/if}
</Section>
