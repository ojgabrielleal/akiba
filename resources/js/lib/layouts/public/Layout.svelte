<script>
    import { onMount } from "svelte";
    import { usePoll } from "@inertiajs/svelte";
    import axios from "axios";
    import { CookieConsent, FlashToaster, Modal } from "@/lib/components/public";
    import { startAutoplay, syncMediaSessionMetadata } from "@/lib/stores";
    import {
        applyPublicTheme,
        getStoredPublicTheme,
        listenForOAuthAction,
        OAuthAction,
        startPublicSiteRefreshWatcher,
    } from "@/lib/utils";
    import { Footer, Navbar, PlayerBar, ProfileForm } from "@/lib/widgets/public";

    export let flash = null;
    export let oauth = {};
    export let onair = null;
    export let stream = null;
    export let pageUrl = null;
    export let publicThemeEnabled = false;
    export let publicSiteVersion = null;

    let profileModalRef;

    $: profile = oauth?.profile;
    $: nickname = profile?.nickname || profile?.username || "Perfil";
    $: canOpenProfile = oauth?.is_oauth || (oauth?.is_member && oauth?.can_view_profile && oauth?.can_update_profile);
    $: air = onair?.data?.[0] ?? null;
    $: syncMediaSessionMetadata(air, stream);

    usePoll(10 * 1000, {
        only: ["onair", "stream"],
    });

    const openProfile = () => {
        if (!canOpenProfile) return;

        profileModalRef?.open();
    };

    const registerPresence = () => {
        if (!oauth?.authenticated || typeof window === "undefined") return;

        const key = "akiba:presence:last-posted-at";
        const lastPostedAt = Number(window.sessionStorage.getItem(key) ?? 0);
        const now = Date.now();

        if (now - lastPostedAt < 60 * 1000) return;

        window.sessionStorage.setItem(key, String(now));
        axios.post("/presence").catch(() => {});
    };

    onMount(() => {
        const stopOAuthListener = listenForOAuthAction(
            OAuthAction.OPEN_PROFILE,
            openProfile,
        );

        applyPublicTheme(getStoredPublicTheme());
        startAutoplay();
        registerPresence();
        const stopRefreshWatcher = startPublicSiteRefreshWatcher(() => publicSiteVersion);

        const handleVisibilityChange = () => {
            if (document.visibilityState === "visible") {
                registerPresence();
            }
        };

        document.addEventListener("visibilitychange", handleVisibilityChange);

        return () => {
            stopOAuthListener?.();
            stopRefreshWatcher?.();
            document.removeEventListener("visibilitychange", handleVisibilityChange);
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
        size="lg"
    >
        <ProfileForm
            {profile}
            internal={oauth?.is_member}
            close={() => profileModalRef.close()}
        />
    </Modal>
{/if}
<CookieConsent {publicThemeEnabled} />
