<script>
    import { onMount } from "svelte";
    import { usePoll } from "@inertiajs/svelte";
    import { CookieConsent, FlashToaster, Modal } from "@/lib/components/public";
    import { startAutoplay } from "@/lib/stores";
    import {
        applyPublicTheme,
        getStoredPublicTheme,
        listenForOAuthAction,
        OAuthAction,
    } from "@/lib/utils";
    import { Footer, Navbar, PlayerBar, ProfileForm } from "@/lib/widgets/public";

    export let flash = null;
    export let oauth = {};
    export let onair = null;
    export let stream = null;
    export let pageUrl = null;
    export let publicThemeEnabled = false;

    let profileModalRef;

    $: profile = oauth?.profile;
    $: nickname = profile?.nickname || profile?.username || "Perfil";
    $: canOpenProfile = oauth?.is_oauth || (oauth?.is_member && oauth?.can_view_profile && oauth?.can_update_profile);

    usePoll(10 * 1000, {
        only: ["onair"]
    });

    const openProfile = () => {
        if (!canOpenProfile) return;

        profileModalRef?.open();
    };

    onMount(() => {
        const stopOAuthListener = listenForOAuthAction(
            OAuthAction.OPEN_PROFILE,
            openProfile,
        );

        applyPublicTheme(getStoredPublicTheme());
        startAutoplay();

        return () => {
            stopOAuthListener?.();
        };
    });
</script>

<FlashToaster {flash} />
<div
    data-public-theme-scope={publicThemeEnabled ? "" : null}
    data-public-theme={publicThemeEnabled ? "akiba" : null}
>
    <header class="public-header-background bg-blue-night">
        <Navbar {oauth} />
    </header>

    <main>
        <slot />
    </main>

    <Footer />
    <PlayerBar {onair} {stream} {pageUrl} {oauth} />
</div>
{#if canOpenProfile}
    <Modal
        bind:this={profileModalRef}
        label={`Perfil de ${nickname}`}
        size="sm"
    >
        <ProfileForm
            {profile}
            internal={oauth?.is_member}
            close={() => profileModalRef.close()}
        />
    </Modal>
{/if}
<CookieConsent {publicThemeEnabled} />
