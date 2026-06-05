<script>
    export let title;

    import { page, router, Link } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import {
        UserForm,
        UserAccessForm,
        ActivityForm,
    } from "@/ui/widgets/private";
    import { userGridPermissions } from "@/utils";

    $: ({ users } = $page.props);

    let can = userGridPermissions();

    let offCanvasUserRef;
    let offCanvasUserAccessRef;
    let offCanvasActivityRef;
    let identifier;

    const requestDeactivateUser = (user) => {
        router.delete(`/panel/administration/user/${user}`, {},
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
            <button type="button" class="text-blue-skywave text-xl font-noto-sans font-extrabold italic uppercase cursor-pointer" on:click={() => { offCanvasUserRef.open(); }}>
                Cadastrar membro
            </button>
            <span class="border-l border-suspense-aurora/30"></span>
        {/if}
        {#if can.activity.create}
            <button type="button" class="text-blue-skywave text-xl font-noto-sans font-extrabold italic uppercase cursor-pointer" on:click={() => { offCanvasActivityRef.open(); }}>
                Criar Atividade e Avisos
            </button>
        {/if}
    </div>
    <Section {title}>
        <div class="mt-18 grid grid-cols-1 lg:grid-cols-4 gap-15 lg:gap-x-5 lg:gap-y-18">
            {#each users.data as item}
                {@const highestRole = item.roles.reduce((prev, current) => {
                    return prev.weight > current.weight ? prev : current;
                })}
                <article class="h-35 px-3 py-1 bg-blue-skywave rounded-sm relative">
                    <dl>
                        <dt class="text-suspense-aurora text-xl lg:text-2xl font-noto-sans font-extrabold italic uppercase">
                            {item.nickname}
                        </dt>
                        <dd class="text-suspense-aurora text-xs font-noto-sans font-semibold italic uppercase">
                            {item.name}
                        </dd>
                    </dl>
                    <img
                        class="w-35 absolute right-0 bottom-0"
                        src={item.avatar}
                        alt=""
                        aria-hidden="true"
                    />
                    <dl class="w-full flex justify-between items-end px-3 absolute left-0 bottom-2">
                        <dt class="rounded-full p-2 bg-suspense-aurora text-xs text-blue-marinho font-noto-sans font-extrabold uppercase italic">
                            {highestRole.label}
                        </dt>
                        <dd class="flex flex-wrap lg:flex-nowrap gap-2">
                            {#if !item.is_virtual && can.authority.update}
                                <button type="button"
                                    aria-label="Definir permissões"
                                    class="w-8 h-8 bg-suspense-aurora rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                                    on:click={() => { identifier = item.uuid; offCanvasUserAccessRef.open(); }}
                                >
                                    <img
                                        src="/svg/crown.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-4 filter-blue-marinho"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                            <Link
                                title=""
                                href={`/panel/profile/${item.uuid}`}
                                aria-label="Editar perfil"
                                class="w-8 h-8 bg-suspense-aurora rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                            >
                                <img
                                    src="/svg/edit.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-4 filter-blue-marinho"
                                    loading="lazy"
                                />
                            </Link>
                            {#if can.deactivate}
                                <button type="button"
                                    aria-label="Desativar perfil"
                                    class="w-8 h-8 bg-suspense-aurora rounded-md flex justify-center items-center font-noto-sans italic font-extrabold cursor-pointer"
                                    on:click={()=> requestDeactivateUser(item.uuid)}
                                >
                                    <img
                                        src="/svg/trash.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-4 filter-red-crimson"
                                        loading="lazy"
                                    />
                                </button>
                            {/if}
                        </dd>
                    </dl>
                </article>
            {/each}
        </div>
    </Section>
{/if}
