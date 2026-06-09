<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { ProfileForm } from "@/ui/widgets/private";

    $: ({ profile } = $page.props);

    $: titleAdapted = profile ? profile.data.nickname : "Perfil";
</script>

<Meta meta={{ title: titleAdapted } } />
<Layout>
    <h1 class="sr-only">{titleAdapted}</h1>
    <ProfileForm />
</Layout>
