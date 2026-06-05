<script>
    export let title;

    import { page } from "@inertiajs/svelte";
    import { Section, ButtonPagination } from "@/ui/components/private/";

    $: ({ onair } = $page.props);
</script>

<Section {title}>
    {#if onair && onair.data.length > 0}
        <div class="overflow-x-auto w-full">
            <table class="min-w-[900px] w-full border-collapse table-auto">
                <thead>
                    <tr class="text-orange-amber uppercase text-lg font-extrabold font-noto-sans italic whitespace-nowrap">
                        <th class="py-4 px-4 text-start min-w-[180px]">
                            Locutor
                        </th>
                        <th class="py-4 px-4 text-start min-w-[250px]">
                            Programa
                        </th>
                        <th class="py-4 px-4 text-start min-w-[140px]">
                            Categoria
                        </th>
                        <th class="py-4 px-4 text-start min-w-[180px]">
                            Data e hora
                        </th>
                        <th class="py-4 px-4 text-start min-w-[120px]">
                            Pedidos
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {#each onair.data as item}
                        <tr class="border-t border-suspense-aurora/20 font-noto-sans text-suspense-aurora whitespace-nowrap">
                            <td class="py-6 px-4 align-middle">
                                {item.program.host.nickname}
                            </td>
                            <td class="py-6 px-4 align-middle">
                                {item.program.name}
                            </td>
                            <td class="py-6 px-4 align-middle">
                                {item.type === "live" ? "Ao Vivo" : "Pré-gravado"}
                            </td>
                            <td class="py-6 px-4 align-middle">
                                {item.created_at}
                            </td>
                            <td class="py-6 px-4 align-middle">
                                {item.song_requests_total} Atendidos
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
            <ButtonPagination pages={onair} only={["onair"]} />
        </div>
    {/if}
</Section>
