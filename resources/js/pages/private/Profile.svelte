<script>
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { ProfileForm } from "@/lib/widgets/private";

    $: ({ profile } = $page.props);

    $: titleAdapted = profile ? profile.data.nickname : "Perfil";
</script>

<Meta meta={{ title: titleAdapted } } />
<Layout>
    <h1 class="sr-only">{titleAdapted}</h1>
    <ProfileForm {profile} />
</Layout>
