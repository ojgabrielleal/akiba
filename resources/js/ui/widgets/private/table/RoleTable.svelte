<script>
    export let title;

    import { router, page } from "@inertiajs/svelte";
    import { Button, IconButton, Offcanvas, Section } from "@/ui/components/private";
    import { RoleForm } from "@/ui/widgets/private";
    import { rolePermissions } from "@/utils";

    $: ({ roles } = $page.props);

    let can = rolePermissions();

    let offCanvasRef;
    let identifier;

    const requestRemoveRole = (role) => {
        router.delete(`/panel/administration/role/${role}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };
</script>

<Offcanvas bind:this={offCanvasRef} title={identifier ? "Atualizar cargo" : "Cadastrar cargo"}>
    <div slot="content" let:close>
        <RoleForm {identifier} {close} />
    </div>
</Offcanvas>

<Section {title}>
    {#if can.create}
        <div class="flex justify-center gap-5 mb-5">
            <Button
                variant="info"
                on:click={() => { identifier = null; offCanvasRef.open(); }}
            >
                Cadastrar cargo
            </Button>
        </div>
    {/if}
    {#if roles && roles.data.length > 0}
        <div class="overflow-x-auto w-full">
            <table class="min-w-[900px] w-full border-collapse table-auto">
                <thead>
                    <tr class="text-orange-amber uppercase text-lg font-extrabold font-noto-sans italic whitespace-nowrap">
                        <th class="p-4 text-start min-w-[180px]">
                            Cargo
                        </th>
                        <th class="p-4 text-start min-w-[180px]">
                            Membros relacionados
                        </th>
                        <th class="p-4 text-start min-w-[300px]">
                            Descrição
                        </th>
                        <th class="p-4 text-start min-w-[140px]"> </th>
                    </tr>
                </thead>
                <tbody>
                    {#each roles.data as item}
                        <tr class="border-t border-suspense-aurora/20 font-noto-sans text-suspense-aurora whitespace-nowrap">
                            <td class="p-4 align-center min-w-[180px]">
                                {item.label}
                            </td>
                            <td class="p-4 align-center min-w-[180px]">
                                {item.members_total} membros
                            </td>
                            <td class="p-4 min-w-[300px] max-w-[400px] whitespace-normal wrap-break-words">
                                {item.description}
                            </td>
                            <td class="p-4 min-w-[140px]">
                                <div class="flex justify-start gap-3">
                                    {#if can.update}
                                        <IconButton
                                            variant="edit"
                                            label="Atualizar"
                                            size="lg"
                                            surface="ocean"
                                            tone="light"
                                            on:click={() => { identifier = item.uuid; offCanvasRef.open(); }}
                                        />
                                    {/if}
                                    {#if can.delete}
                                        <IconButton
                                            variant="trash"
                                            label="Remover"
                                            size="lg"
                                            surface="danger"
                                            tone="light"
                                            on:click={() => { requestRemoveRole(item.uuid); }}
                                        />
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
</Section>
