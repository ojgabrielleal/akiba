<script>
    import { router } from "@inertiajs/svelte";
    import {
        Badge,
        EmptyState,
        GridList,
        IconButton,
        Offcanvas,
        Section,
    } from "@/lib/components/private";
    import { BadgeForm } from "@/lib/widgets/private";
    import { badgePermissions } from "@/lib/utils";

    export let title;
    export let variant = null;
    export let badges = null;
    export let badgeTargets = null;

    const can = badgePermissions();
    let offCanvasRef;
    let badgeSelected = null;

    $: actions = [{
        title: "Criar emblema",
        icon: "/svg/plus.svg",
        permission: variant === "administration" && can.create,
        onClick: () => openBadgeForm(),
    }];

    function openBadgeForm(badge = null) {
        badgeSelected = badge;
        offCanvasRef.open();
    }

    function requestDeleteBadge(badge) {
        router.delete(`/panel/administration/badge/${badge.uuid}`, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function sourceLabel(source) {
        return ({
            enigmagame: "Enigma Otaku",
            song_request: "Pedidos musicais",
            podcast: "Podcasts",
        })[source] ?? "Sem origem";
    }

    function triggerLabel(trigger) {
        return ({
            "enigmagame.most_wins": "Pessoa que mais venceu enigmas",
            "song_request.most_requests": "Pessoa que mais fez pedidos",
            "song_request.played_total": "Pedidos atendidos",
            "podcast.all_listened": "Todos os podcasts ouvidos",
            "podcast.listened_total": "Quantidade de podcasts ouvidos",
            "site.presence_window": "Presença no site durante a janela",
        })[trigger] ?? trigger;
    }


    function audienceLabel(item) {
        if (item.audience !== "target") {
            return "Todos os usuários";
        }

        const target = (badgeTargets ?? []).find((candidate) => (
            candidate.type === item.target_type && candidate.uuid === item.target_uuid
        ));

        return target ? `${target.label} (${target.detail})` : "Usuário selecionado";
    }

    function formatSchedule(value) {
        if (!value) return null;

        return new Date(value).toLocaleString("pt-BR", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }
</script>

<Offcanvas
    bind:this={offCanvasRef}
    title={badgeSelected ? `Atualizar ${badgeSelected.name}` : "Cadastrar emblema"}
>
    <div slot="content" let:close>
        <BadgeForm {badgeSelected} {badgeTargets} {close} />
    </div>
</Offcanvas>

{#if badges}
    <Section {title} {actions}>
        {#if badges.data.length > 0}
            <GridList preset="wide">
                {#each badges.data as item (item.uuid)}
                    <li>
                        <article class="relative flex h-full min-h-38 flex-col overflow-hidden rounded-md bg-blue-ocean p-3 text-suspense-aurora shadow-sm shadow-blue-night/20">
                            {#if variant === "administration" && (can.update || can.delete)}
                                <div class="absolute right-2 top-2 z-20 flex gap-1">
                                    {#if can.delete}
                                        <IconButton
                                            variant="trash"
                                            label={`Excluir ${item.name}`}
                                            size="sm"
                                            surface="dark"
                                            on:click={() => requestDeleteBadge(item)}
                                        />
                                    {/if}
                                    {#if can.update}
                                        <IconButton
                                            variant="edit"
                                            label={`Editar ${item.name}`}
                                            size="sm"
                                            surface="dark"
                                            tone="accent"
                                            on:click={() => openBadgeForm(item)}
                                        />
                                    {/if}
                                </div>
                            {/if}

                            <header class="grid grid-cols-[4rem_minmax(0,1fr)] gap-3 pr-16">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md bg-suspense-aurora p-1">
                                    {#if item.image}
                                        <img
                                            src={item.image}
                                            alt=""
                                            aria-hidden="true"
                                            class="h-full w-full object-contain"
                                            loading="lazy"
                                        />
                                    {:else}
                                        <span class="font-noto-sans text-3xl font-black italic text-blue-ocean">+</span>
                                    {/if}
                                </div>

                                <div class="min-w-0 pt-0.5">
                                    <div class="mb-2 flex min-h-5 flex-wrap items-center gap-1.5">
                                        <Badge variant={item.type === "stealable" ? "review" : item.type === "achievement" ? "success" : "accent"} size="sm">
                                            {item.type_label}
                                        </Badge>
                                        {#if !item.is_active}
                                            <Badge variant="danger" size="sm">Inativo</Badge>
                                        {/if}
                                    </div>
                                    <h3 class="truncate font-noto-sans text-lg font-black uppercase italic leading-tight" title={item.name}>
                                        {item.name}
                                    </h3>
                                    <p class="truncate font-noto-sans text-xs font-semibold uppercase italic text-orange-amber" title={item.code}>
                                        {item.code}
                                    </p>
                                </div>
                            </header>

                            {#if item.description}
                                <p class="mt-3 line-clamp-2 text-sm leading-snug text-suspense-aurora/85">
                                    {item.description}
                                </p>
                            {/if}

                            <dl class="mt-4 grid gap-1.5 text-xs text-suspense-aurora/90">
                                <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                    <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Origem:</dt>
                                    <dd class="truncate" title={sourceLabel(item.source)}>{sourceLabel(item.source)}</dd>
                                </div>
                                <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                    <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Ativos:</dt>
                                    <dd class="truncate">{item.active_assignments_total ?? 0} / {item.assignments_total ?? 0}</dd>
                                </div>
                                {#if item.trigger}
                                    <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                        <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Gatilho:</dt>
                                        <dd class="truncate" title={triggerLabel(item.trigger)}>{triggerLabel(item.trigger)}</dd>
                                    </div>
                                {/if}
                                {#if item.threshold}
                                    <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                        <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Meta:</dt>
                                        <dd class="truncate">{item.threshold}</dd>
                                    </div>
                                {/if}
                                {#if item.type === "scheduled"}
                                    <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                        <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Janela:</dt>
                                        <dd class="truncate" title={`${formatSchedule(item.starts_at)} até ${formatSchedule(item.ends_at)}`}>
                                            {formatSchedule(item.starts_at)} até {formatSchedule(item.ends_at)}
                                        </dd>
                                    </div>
                                    <div class="flex min-w-0 items-center gap-1.5 rounded-sm bg-blue-marinho/35 px-2 py-1.5">
                                        <dt class="shrink-0 font-noto-sans font-black uppercase italic text-orange-amber">Alvo:</dt>
                                        <dd class="truncate" title={audienceLabel(item)}>{audienceLabel(item)}</dd>
                                    </div>
                                {/if}
                            </dl>
                        </article>
                    </li>
                {/each}
            </GridList>
        {:else}
            <EmptyState
                title="Nenhum emblema cadastrado"
                description="Crie emblemas concedidos, competitivos, de evento ou conquista para usar nas próximas integrações."
            />
        {/if}
    </Section>
{/if}
