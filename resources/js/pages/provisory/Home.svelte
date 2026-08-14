<script>
    import { onMount } from "svelte";
    import { page, usePoll } from "@inertiajs/svelte";
    import { Toaster } from "svelte-hot-french-toast";
    import { Modal } from "@/lib/components/public";
    import { Meta } from "@/lib/components/shared";
    import { syncMediaSessionMetadata } from "@/lib/stores";
    import {
        listenForOAuthAction,
        OAuthAction,
    } from "@/lib/utils";
    import { MainPlayer, MobilePlayer, ProfileForm } from "@/lib/widgets/public";

    $: ({ onair, stream, oauth } = $page.props);
    $: air = onair?.data?.[0] ?? null;
    $: profile = oauth?.profile;
    $: nickname = profile?.nickname || profile?.username || "Perfil";
    $: canOpenProfile = oauth?.is_oauth || (oauth?.is_member && oauth?.can_view_profile && oauth?.can_update_profile);
    $: syncMediaSessionMetadata(air, stream);

    let profileModalRef;

    usePoll(10 * 1000, {
        only: ["onair", "stream"],
    });

    const openProfile = () => {
        if (!canOpenProfile) return;

        profileModalRef?.open();
    };

    onMount(() => {
        document.body.style.backgroundColor = "var(--color-blue-night)";

        const stopOAuthListener = listenForOAuthAction(
            OAuthAction.OPEN_PROFILE,
            openProfile,
        );

        return () => {
            stopOAuthListener?.();
        };
    });
</script>

<Meta />
<Toaster />
<header>
    <div class="w-full flex overflow-hidden fixed top-0 z-10">
        <div class="flex shrink-0">
            <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(0_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                Em Reforma
            </div>
            <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
        </div>
        {#each Array(15) as _, index}
            <div class="flex -ml-5 shrink-0">
                <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                    Em Reforma
                </div>
                <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
            </div>
        {/each}
    </div>
</header>

<main class="flex flex-col justify-center items-center gap-5 lg:gap-10 h-screen">
    <div class="container-page mt-20">
        <div class="w-full flex justify-center">
            <img
                src="/img/brand/logo.webp"
                alt="Logo"
                class="w-80 md:w-100 lg:w-110"
                loading="lazy"
            />
        </div>
    </div>

    <div class="hidden lg:block w-full">
        <MainPlayer {onair} {stream} {oauth} />
    </div>
    <div class="lg:hidden w-full pb-10">
        <MobilePlayer {onair} {stream} {oauth} />
    </div>
</main>

<footer>
    <div class="w-full flex overflow-hidden fixed bottom-0 z-10">
        <div class="flex shrink-0">
            <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(0_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                Em Reforma
            </div>
            <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
        </div>
        {#each Array(15) as _, index}
            <div class="flex -ml-5 shrink-0">
                <div class="bg-blue-ocean py-1 px-10 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)] text-suspense-aurora font-noto-sans font-extrabold italic uppercase">
                    Em Reforma
                </div>
                <div class="w-40 bg-orange-amber py-1 px-4 -ml-5 [clip-path:polygon(1.25rem_0,100%_0,calc(100%-1.25rem)_100%,0_100%)]"></div>
            </div>
        {/each}
    </div>
</footer>

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
