<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { InactiveItemGrid } from "@/lib/widgets/private";

    $: ({ inactive_items: inactiveItems = [] } = $page.props);
</script>

<Meta meta={{ title: "Itens desativados" }} />
<Layout>
    <h1 class="sr-only">Itens desativados</h1>
    <InactiveItemGrid title="Itens desativados" items={inactiveItems} />
</Layout>
