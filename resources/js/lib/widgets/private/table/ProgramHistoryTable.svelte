<script>
    import { Badge, EmptyState, Pagination, Section } from "@/lib/components/private/";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let onair = null;

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
</script>

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
                                    <img
                                        src={resolvePlaceholderImage(
                                            item.program.host.avatar,
                                            "avatar",
                                            item.program.host.gender,
                                        )}
                                        alt={`Avatar de ${item.program.host.nickname}`}
                                        class="size-9 shrink-0 rounded-full bg-suspense-aurora object-cover object-top"
                                        loading="lazy"
                                    />
                                    <span class="max-w-28 truncate">
                                        {item.program.host.nickname}
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
                                {formatNumber(item.song_requests_total)} atendidos
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
