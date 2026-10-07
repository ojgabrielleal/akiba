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
    title="Novo emblema desbloqueado!"
    size="sm"
    bordered={false}
    closeOnBackdrop={false}
>
    {#if activeBadgeAssignment}
        <div class="relative isolate -m-5 overflow-hidden rounded-b-xl bg-blue-night px-5 py-6 text-center">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(255,134,0,0.22),transparent_34%),linear-gradient(180deg,rgba(0,145,255,0.22),rgba(0,25,76,0.78))]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-8 top-7 -z-10 h-24 rounded-full bg-orange-morning/20 blur-2xl" aria-hidden="true"></div>

            <div class="mx-auto flex size-32 items-center justify-center rounded-full border border-orange-morning/45 bg-blue-marinho/80 p-3 shadow-[0_18px_45px_rgba(0,0,0,0.35)]">
                <div class="flex size-full items-center justify-center rounded-full bg-suspense-aurora p-4 shadow-inner">
                    {#if activeBadgeAssignment.badge?.image}
                        <img
                            src={activeBadgeAssignment.badge.image}
                            alt={activeBadgeAssignment.badge.name}
                            class="h-full w-full object-contain drop-shadow-[0_0.35rem_0.35rem_rgba(0,0,20,0.22)]"
                        />
                    {:else}
                        <span class="font-noto-sans text-5xl font-black italic text-orange-amber">+</span>
                    {/if}
                </div>
            </div>

            <div class="mt-5">
                <p class="font-noto-sans text-xs font-black uppercase italic text-orange-morning">
                    Emblema conquistado
                </p>
                <h3 class="mt-1 break-words font-noto-sans text-xl font-black uppercase italic leading-tight text-suspense-aurora">
                    {activeBadgeAssignment.badge?.name}
                </h3>
                <p class="mx-auto mt-2 max-w-64 text-sm font-semibold leading-snug text-suspense-aurora/75">
                    Ele já está guardado no seu perfil da Akiba.
                </p>
            </div>

            <Button
                type="button"
                variant="primary"
                shape="pill"
                loading={acknowledgingBadge}
                class="mt-6 bg-orange-citric px-7 text-blue-night shadow-[0_10px_24px_rgba(255,134,0,0.28)]"
                on:click={acknowledgeBadge}
            >
                Confirmar recebimento
            </Button>
        </div>
    {/if}
</Modal>
<CookieConsent {publicThemeEnabled} />
