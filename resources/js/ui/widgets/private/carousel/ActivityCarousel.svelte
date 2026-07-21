<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { Carousel, IconButton, Offcanvas, Section, Tooltip } from "@/ui/components/private/";
    import { ActivityForm } from "@/ui/widgets/private";
    import { activityPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ user, activities } = $page.props);

    let can = activityPermissions();

    let offcanvasRef;
    let activitySelected = null;
    $: offcanvasTitle = activitySelected ? `Atualizar ${activitySelected.title}` : "Criar atividade / aviso";

    let actions = [
        {
            title: "Criar aviso / atividade",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                activitySelected = null;
                offcanvasRef.open();
            },
        },
    ];

    const requestConfirmActivityParticipant = (activity) => {
        router.post(`/panel/dashboard/activity/${activity}/confirm`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <ActivityForm {activitySelected} {close} />
    </div>
</Offcanvas>

{#if activities}
    <Section {title} {actions}>
        <Carousel label={title}>
            {#each activities.data as item}
                {@const canParticipate = can.participate && !item.confirmations.some((conf) => conf.uuid === user.uuid)}
                <article class={["w-100 h-45 lg:w-116 shrink-0 rounded-md p-4 relative",
                    { "bg-gradient-orange-morning-aurora": item.allows_confirmations },
                    { "bg-gradient-blue-cerulean-glow": !item.allows_confirmations },
                ]}>
                    <div class={["font-noto-sans font-black italic uppercase text-xl flex items-center gap-1",
                        { "text-blue-marinho": item.allows_confirmations },
                        { "text-suspense-aurora": !item.allows_confirmations },
                    ]}>
                        {item.allows_confirmations ? item.title : item.author.nickname}
                    </div>
                    <div class={["font-noto-sans text-sm line-clamp-3 mt-1",
                        { "text-blue-marinho": item.allows_confirmations },
                        { "text-suspense-aurora": !item.allows_confirmations },
                    ]}>
                        {item.content}
                    </div>
                    {#if item.allows_confirmations}
                        <div class="flex gap-1 absolute bottom-3 left-4">
                            {#each item.confirmations.slice(0, 5) as conf}
                                <Tooltip>
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-suspense-aurora">
                                        <img
                                            src={resolvePlaceholderImage(conf.avatar, "avatar")}
                                            alt={conf.nickname}
                                            class="w-full h-full object-cover object-top scale-200"
                                            loading="lazy"
                                        />
                                    </div>
                                    <span slot="content">
                                        {conf.nickname}
                                    </span>
                                </Tooltip>
                            {/each}
                            {#if item.confirmations.length > 5}
                                <Tooltip>
                                    <span class="w-7 h-7 rounded-full bg-suspense-aurora flex items-center justify-center font-noto-sans text-xs font-black text-blue-night">
                                        +{item.confirmations.length - 5}
                                    </span>
                                    <div slot="content" class="px-1 py-1">
                                        <ul class="flex flex-col gap-1 text-left">
                                            {#each item.confirmations.slice(5) as conf}
                                                <li>
                                                    {conf.nickname}
                                                </li>
                                            {/each}
                                        </ul>
                                    </div>
                                </Tooltip>
                            {/if}
                        </div>
                    {/if}
                    <div class="flex gap-2 absolute bottom-3 right-4">
                        {#if can.update}
                            <IconButton
                                variant="edit"
                                label="Atualizar"
                                size="sm"
                                surface="dark"
                                class="hover:!brightness-100"
                                on:click={() => { activitySelected = item; offcanvasRef.open(); }}
                            />
                        {/if}
                        {#if canParticipate && item.allows_confirmations}
                            <IconButton
                                variant="verify"
                                label="Participar"
                                size="sm"
                                surface="dark"
                                tone="accent"
                                class="hover:!brightness-100"
                                on:click={() => requestConfirmActivityParticipant(item.uuid)}
                            />
                        {/if}
                    </div>
                </article>
            {/each}
        </Carousel>
    </Section>
{/if}
