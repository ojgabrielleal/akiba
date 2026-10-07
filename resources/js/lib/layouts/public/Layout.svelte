<script>
    import { onMount } from "svelte";
    import { page, router, usePoll } from "@inertiajs/svelte";
    import axios from "axios";
    import { Button, CookieConsent, FlashToaster, Modal } from "@/lib/components/public";
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
    export let newBadges = [];

    let profileModalRef;
    let badgeModalRef;
    let activeBadgeIndex = 0;
    let acknowledgingBadge = false;
    let keyBuffer = [];

    const easterEggSequences = {
        konami: ["ArrowUp", "ArrowUp", "ArrowDown", "ArrowDown", "ArrowLeft", "ArrowRight", "ArrowLeft", "ArrowRight", "KeyB", "KeyA"],
        doom_iddqd: ["KeyI", "KeyD", "KeyD", "KeyQ", "KeyD"],
        mortal_kombat_abacabb: ["KeyA", "KeyB", "KeyA", "KeyC", "KeyA", "KeyB", "KeyB"],
        sonic_2_level_select: ["ArrowUp", "ArrowUp", "ArrowUp", "ArrowDown", "ArrowDown", "ArrowDown", "ArrowLeft", "ArrowRight", "ArrowLeft", "ArrowRight"],
        super_mario_continue: ["KeyA", "KeyB", "KeyB", "KeyA"],
    };

    $: profile = oauth?.profile;
    $: sharedNewBadges = newBadges?.length ? newBadges : ($page.props.newBadges ?? []);
    $: activeBadgeAssignment = sharedNewBadges?.[activeBadgeIndex] ?? null;
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

    const acknowledgeBadge = () => {
        if (!activeBadgeAssignment || acknowledgingBadge) return;

        acknowledgingBadge = true;

        router.patch(`/badge-assignment/${activeBadgeAssignment.uuid}/seen`, {}, {
            preserveScroll: true,
            preserveState: true,
            only: ["newBadges"],
            onFinish: () => {
                acknowledgingBadge = false;

                if (activeBadgeIndex + 1 < sharedNewBadges.length) {
                    activeBadgeIndex += 1;
                    return;
                }

                activeBadgeIndex = 0;
                badgeModalRef?.close();
            },
        });
    };

    const unlockEasterEgg = (easterEgg) => {
        if (!oauth?.authenticated) return;

        axios.post(`/easter-eggs/${easterEgg}/unlock`)
            .then(() => router.reload({ only: ["newBadges"] }))
            .catch(() => {});
    };

    const handleEasterEggKeydown = (event) => {
        if (event.target?.matches?.("input, textarea, select, [contenteditable='true']")) return;

        keyBuffer = [...keyBuffer, event.code].slice(-12);

        Object.entries(easterEggSequences).forEach(([easterEgg, sequence]) => {
            const tail = keyBuffer.slice(-sequence.length);

            if (tail.length === sequence.length && tail.every((key, index) => key === sequence[index])) {
                unlockEasterEgg(easterEgg);
                keyBuffer = [];
            }
        });
    };

    onMount(() => {
        const stopOAuthListener = listenForOAuthAction(
            OAuthAction.OPEN_PROFILE,
            openProfile,
        );

        applyPublicTheme(getStoredPublicTheme());
        startAutoplay();
        registerPresence();
        if (sharedNewBadges?.length) {
            badgeModalRef?.open();
        }
        const stopRefreshWatcher = startPublicSiteRefreshWatcher(() => publicSiteVersion);

        const handleVisibilityChange = () => {
            if (document.visibilityState === "visible") {
                registerPresence();
            }
        };

        document.addEventListener("visibilitychange", handleVisibilityChange);
        document.addEventListener("keydown", handleEasterEggKeydown);

        if (window.location.pathname === "/404" || document.title.includes("404")) {
            unlockEasterEgg("page_404");
        }

        return () => {
            stopOAuthListener?.();
            stopRefreshWatcher?.();
            document.removeEventListener("visibilitychange", handleVisibilityChange);
            document.removeEventListener("keydown", handleEasterEggKeydown);
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
<Modal
    bind:this={badgeModalRef}
    title="Você recebeu um novo emblema!"
    size="sm"
    closeOnBackdrop={false}
>
    {#if activeBadgeAssignment}
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="flex size-28 items-center justify-center overflow-hidden rounded-md bg-blue-marinho/10 p-3">
                {#if activeBadgeAssignment.badge?.image}
                    <img
                        src={activeBadgeAssignment.badge.image}
                        alt={activeBadgeAssignment.badge.name}
                        class="h-full w-full object-contain"
                    />
                {:else}
                    <span class="font-noto-sans text-5xl font-black italic text-orange-amber">+</span>
                {/if}
            </div>
            <div>
                <h3 class="font-noto-sans text-lg font-black uppercase italic text-blue-night">
                    {activeBadgeAssignment.badge?.name}
                </h3>
                <p class="mt-1 text-sm text-blue-ocean">Confira todos no seu perfil.</p>
            </div>
            <Button
                type="button"
                variant="primary"
                shape="pill"
                loading={acknowledgingBadge}
                on:click={acknowledgeBadge}
            >
                Confirmar recebimento
            </Button>
        </div>
    {/if}
</Modal>
<CookieConsent {publicThemeEnabled} />
