<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/public";
    import { TeamDirectory } from "@/lib/widgets/public";

    $: ({ onair, stream, flash, oauth, members } = $page.props);
    $: pageUrl = $page.url;
</script>

<Meta meta={{ title: "Equipe" }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl}>
    <TeamDirectory {members} />
</Layout>
