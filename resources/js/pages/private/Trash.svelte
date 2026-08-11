<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { TrashGrid } from "@/lib/widgets/private";

    $: ({ trash_items: trashItems = [] } = $page.props);
</script>

<Meta meta={{ title: "Lixeira" }} />
<Layout>
    <h1 class="sr-only">Lixeira</h1>
    <TrashGrid title="Lixeira" items={trashItems} />
</Layout>
