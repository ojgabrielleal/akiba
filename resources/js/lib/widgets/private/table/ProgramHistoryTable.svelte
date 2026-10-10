<script>
    import { Badge, EmptyState, IconButton, Modal, Pagination, Section } from "@/lib/components/private/";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let onair = null;

    let songRequestsModalRef;
    let songRequestsLoading = false;
    let songRequests = [];
    let selectedOnair = null;
    let songRequestsError = null;

    function category(executionMode) {
        const categories = {
            live: {
                label: "Ao vivo",
                icon: "/svg/onair.svg",
                variant: "success",
            },
            scheduled: {
                label: "Pré-gravado",
                icon: "/svg/play.svg",
                variant: "review",
            },
        };

        return categories[executionMode] ?? {
            label: executionMode,
            icon: "/svg/radio.svg",
            variant: "dark",
        };
    }

    function formatNumber(value) {
        return Number(value ?? 0).toLocaleString("pt-BR");
    }

    function requestStatus(item) {
        if (item.type === "message") {
            if (item.was_read) return { label: "Lido", class: "bg-green-mint text-blue-night" };
            if (item.was_dismissed) return { label: "Dispensado", class: "bg-red-crimson text-suspense-aurora" };

            return { label: "Pendente", class: "bg-orange-amber text-blue-night" };
        }

        if (item.was_reproduced) return { label: "Atendido", class: "bg-green-mint text-blue-night" };
        if (item.was_canceled) return { label: "Cancelado", class: "bg-red-crimson text-suspense-aurora" };

        return { label: "Pendente", class: "bg-orange-amber text-blue-night" };
    }

    function requestTitle(item) {
        if (item.music) {
            return item.music.name ?? "Musica sem titulo";
        }

        return "Recado do ouvinte";
    }

    function requestSubtitle(item) {
        if (item.music) {
            return [item.music.artist, item.music.production].filter(Boolean).join(" • ") || "Pedido musical";
        }

        return item.message;
    }

    async function openSongRequests(item) {
        selectedOnair = item;
        songRequests = [];
        songRequestsError = null;
        songRequestsLoading = true;
        songRequestsModalRef.open();

        try {
            const response = await fetch(`/panel/reports/onair/${item.uuid}/song-requests`, {
                headers: {
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error("Nao foi possivel carregar os pedidos.");
            }

            const payload = await response.json();
            songRequests = payload.data ?? [];
        } catch (error) {
            songRequestsError = error.message || "Nao foi possivel carregar os pedidos.";
        } finally {
            songRequestsLoading = false;
        }
    }
</script>


<Modal bind:this={songRequestsModalRef} title={selectedOnair ? `Pedidos de ${selectedOnair.program.name}` : "Pedidos do programa"} size="xl">
    <div slot="content" class="max-h-[70vh] overflow-y-auto pr-1">
        {#if songRequestsLoading}
            <div class="py-10 text-center font-noto-sans text-sm font-bold uppercase italic text-blue-night">
                Carregando pedidos...
            </div>
        {:else if songRequestsError}
            <EmptyState title="Não foi possível carregar" description={songRequestsError} titleClass="text-blue-night" descriptionClass="text-blue-night/60" />
        {:else if songRequests.length}
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {#each songRequests as item (item.uuid)}
                    {@const status = requestStatus(item)}
                    <article class="flex min-h-64 flex-col rounded-md border border-blue-night/10 bg-suspense-aurora p-3 font-noto-sans text-blue-night shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-[1.2rem] font-extrabold uppercase italic leading-tight">
                                    {item.name ?? "Ouvinte"}
                                </h3>
                                {#if item.address}
                                    <p class="mt-1 truncate text-xs font-normal leading-tight text-blue-night/70">
                                        {item.address}
                                    </p>
                                {/if}
                            </div>
                            <span class={["shrink-0 rounded-sm px-2.5 py-1 text-[0.65rem] font-black uppercase italic", status.class]}>
                                {status.label}
                            </span>
                        </div>

                        <div class="my-5 flex items-center justify-center w-full">
                            <div class="relative w-full">
                                <div class="absolute left-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <img
                                        src={item.music ? "/svg/music.svg" : "/svg/telegram.svg"}
                                        alt=""
                                        aria-hidden="true"
                                        class={[item.music ? "w-6 rotate-180" : "w-7", "filter-orange-amber"]}
                                        loading="lazy"
                                    />
                                </div>
                                <div class="absolute right-0 w-2/5 h-[0.1rem] bg-orange-amber rounded-full top-1/2 -translate-y-1/2"></div>
                            </div>
                        </div>

                        {#if item.music}
                            <div class="flex min-w-0 items-center gap-3">
                                <img
                                    src={resolvePlaceholderImage(item.music.anime?.image ?? item.music.image, "placeholder")}
                                    alt={`Capa de ${item.music.anime?.name ?? item.music.production ?? item.music.name}`}
                                    class="h-15 w-15 shrink-0 rounded-md border border-blue-night/10 object-cover object-top"
                                    loading="lazy"
                                />
                                <div class="min-w-0 flex-1 text-sm">
                                    <div class="block w-full truncate">
                                        <span class="font-light">Anime:</span>
                                        {item.music.anime?.name ?? item.music.production ?? "Sem anime"}
                                    </div>
                                    <div class="block w-full truncate">
                                        <span class="font-light">Artista:</span>
                                        {item.music.artist ?? "Sem artista"}
                                    </div>
                                    <div class="block w-full truncate">
                                        <span class="font-light">Música:</span>
                                        {item.music.name ?? "Sem titulo"}
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {#if item.message}
                            <p class="mt-4 whitespace-pre-line break-words text-sm font-normal leading-relaxed text-blue-night">
                                {item.message}
                            </p>
                        {/if}

                        <div class="mt-auto pt-4 text-right text-sm font-extrabold italic text-orange-amber">
                            {item.created_at}
                        </div>
                    </article>
                {/each}
            </div>
        {:else}
            <EmptyState title="Nenhum pedido" description="Esse programa não recebeu pedidos." titleClass="text-blue-night" descriptionClass="text-blue-night/60" />
        {/if}
    </div>
</Modal>

<Section {title}>
    {#if onair?.data?.length}
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[920px] table-auto border-collapse">
                <thead>
                    <tr class="whitespace-nowrap font-noto-sans text-base font-extrabold uppercase italic text-orange-amber">
                        <th class="min-w-[180px] px-3 py-3 text-start">
                            Locutor
                        </th>
                        <th class="min-w-[220px] px-3 py-3 text-start">
                            Programa
                        </th>
                        <th class="min-w-[150px] px-3 py-3 text-start">
                            Categoria
                        </th>
                        <th class="min-w-[180px] px-3 py-3 text-start">
                            Data e hora
                        </th>
                        <th class="min-w-[90px] px-3 py-3 text-center">
                            Pico
                        </th>
                        <th class="min-w-[140px] px-3 py-3 text-start">
                            Pedidos
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {#each onair.data as item (item.uuid)}
                        {@const itemCategory = category(item.execution_mode)}
                        <tr class="whitespace-nowrap border-t border-suspense-aurora/35 font-noto-sans text-sm font-semibold uppercase text-suspense-aurora">
                            <td class="px-3 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 shrink-0 overflow-hidden rounded-full border-2 border-suspense-aurora bg-suspense-aurora shadow">
                                        <img
                                            src={resolvePlaceholderImage(
                                                item.host.avatar,
                                                "avatar",
                                                item.host.gender,
                                            )}
                                            alt={`Avatar de ${item.host.nickname}`}
                                            class="h-full w-full object-cover object-top scale-125"
                                            loading="lazy"
                                        />
                                    </div>
                                    <span class="max-w-28 truncate">
                                        {item.host.nickname}
                                    </span>
                                </div>
                            </td>
                            <td class="max-w-60 px-3 py-3 align-middle">
                                <span class="block truncate">{item.program.name}</span>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <Badge variant={itemCategory.variant} class="rounded-sm px-2.5">
                                    <img
                                        src={itemCategory.icon}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-3.5 filter-suspense-aurora"
                                        loading="lazy"
                                    />
                                    {itemCategory.label}
                                </Badge>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                {item.created_at}
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                {formatNumber(item.peak_listeners)}
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="flex items-center gap-2">
                                    <IconButton
                                        variant="eye"
                                        label="Ver pedidos"
                                        size="sm"
                                        surface="dark"
                                        on:click={() => openSongRequests(item)}
                                    />
                                    <span class="min-w-0 truncate">{formatNumber(item.song_requests_total)} atendidos</span>
                                </div>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
            <Pagination pages={onair} only={["onair"]} />
        </div>
    {:else}
        <EmptyState
            title="Nenhuma transmissão encontrada"
            description="As transmissões aparecerão aqui após o primeiro programa."
        />
    {/if}
</Section>
