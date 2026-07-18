<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Button, IconButton, Offcanvas, Section } from "@/ui/components/private";
    import {
        UserForm,
        UserAccessForm,
        ActivityForm,
    } from "@/ui/widgets/private";
    import { resolvePlaceholderImage, userGridPermissions } from "@/utils";

    $: ({ users } = $page.props);

    let can = userGridPermissions();

    let offCanvasUserRef;
    let offCanvasUserAccessRef;
    let offCanvasActivityRef;
    let identifier;

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
<Offcanvas bind:this={offCanvasUserAccessRef} title="Configurações administrativas">
    <div slot="content" let:close>
        <UserAccessForm {identifier} {close} />
    </div>
</Offcanvas>
<Offcanvas bind:this={offCanvasActivityRef} title="Criar atividade/aviso">
    <div slot="content" let:close>
        <ActivityForm {close} />
    </div>
</Offcanvas>

{#if users}
    <div class="flex justify-center gap-5 mb-5">
        {#if can.create}
            <Button
                variant="outline"
                on:click={() => { offCanvasUserRef.open(); }}
            >
                Cadastrar membro
            </Button>
            <span class="border-l border-suspense-aurora/30"></span>
        {/if}
        {#if can.activity.create}
            <Button
                variant="outline"
                on:click={() => { offCanvasActivityRef.open(); }}
            >
                Criar Atividade e Avisos
            </Button>
        {/if}
    </div>
    <Section {title}>
        <ul class="mt-18 grid grid-cols-1 lg:grid-cols-4 gap-15 lg:gap-x-5 lg:gap-y-18">
            {#each users.data as item}
                {@const highestRole = item.roles.reduce((prev, current) => {
                    return prev.weight > current.weight ? prev : current;
                })}
                <li>
                    <article class="h-35 px-3 py-1 bg-blue-skywave rounded-sm relative">
                    <header>
                        <h3 class="text-suspense-aurora text-xl lg:text-2xl font-noto-sans font-extrabold italic uppercase">
                            {item.nickname}
                        </h3>
                        <p class="text-suspense-aurora text-xs font-noto-sans font-semibold italic uppercase">
                            {item.name}
                        </p>
                    </header>
                    <img
                        class="w-35 absolute right-0 bottom-0"
                        src={resolvePlaceholderImage(item.avatar, "avatar")}
                        alt=""
                        aria-hidden="true"
                    />
                    <footer class="w-full flex justify-between items-end px-3 absolute left-0 bottom-2">
                        <span class="rounded-full p-2 bg-suspense-aurora text-xs text-blue-marinho font-noto-sans font-extrabold uppercase italic">
                            {highestRole.label}
                        </span>
                        <div class="flex flex-wrap lg:flex-nowrap gap-2" aria-label={`Acoes de ${item.nickname}`}>
                            {#if !item.is_virtual && can.authority.update}
                                <IconButton
                                    variant="crown"
                                    label="Definir permissões"
                                    on:click={() => { identifier = item.uuid; offCanvasUserAccessRef.open(); }}
                                />
                            {/if}
                            <IconButton
                                href={`/panel/profile/${item.uuid}`}
                                variant="edit"
                                label="Editar perfil"
                                tone="dark"
                                surface="light"
                            />
                            {#if can.deactivate}
                                <IconButton
                                    variant="trash"
                                    label="Desativar perfil"
                                    surface="light"
                                    on:click={()=> requestDeactivateUser(item.uuid)}
                                />
                            {/if}
                        </div>
                    </footer>
                    </article>
                </li>
            {/each}
        </ul>
    </Section>
{/if}
