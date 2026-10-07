<script>
    import { onMount } from "svelte";
    import { page, router, usePoll } from "@inertiajs/svelte";
    import { Button, FlashToaster, Modal } from "@/lib/components/private";
    import { pauseAudio } from "@/lib/stores";
    import { Navbar, StreamMetricsGrid } from "@/lib/widgets/private";

    export let newBadges = [];

    let badgeModalRef;
    let activeBadgeIndex = 0;
    let acknowledgingBadge = false;

    $: sharedNewBadges = newBadges?.length ? newBadges : ($page.props.newBadges ?? []);
    $: activeBadgeAssignment = sharedNewBadges?.[activeBadgeIndex] ?? null;

    // Polling for updates in audience, audience history, song requests and stream status every 60 seconds
    usePoll(60 * 1000, {
        only: ["songRequests", "audience", "audienceHistory", "stream"],
    });

    // Set background color on mount
    onMount(() => {
        document.body.style.backgroundColor = "var(--color-blue-marinho)";
        pauseAudio();

        if (sharedNewBadges?.length) {
            badgeModalRef?.open();
        }
    });

    const acknowledgeBadge = () => {
        if (!activeBadgeAssignment || acknowledgingBadge) return;

        acknowledgingBadge = true;

        router.post(`/badge-assignment/${activeBadgeAssignment.uuid}/seen`, {}, {
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
</script>

<FlashToaster />
<header class="mb-8 lg:mb-20 mt-5 lg:mt-10">
    <Navbar />
</header>
<main class="min-w-0 w-full max-w-full overflow-x-clip">
    <slot />
</main>
<footer>
    <div class="h-20"></div>
    <div class="w-full fixed bottom-0 z-50">
        <StreamMetricsGrid />
    </div>
</footer>
<Modal
    bind:this={badgeModalRef}
    title="Você recebeu um novo emblema!"
    size="sm"
>
    {#if activeBadgeAssignment}
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="flex size-28 items-center justify-center overflow-hidden rounded-md bg-suspense-aurora p-3">
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
                <h3 class="font-noto-sans text-lg font-black uppercase italic text-suspense-aurora">
                    {activeBadgeAssignment.badge?.name}
                </h3>
                <p class="mt-1 text-sm text-suspense-aurora/80">Confira todos no seu perfil.</p>
            </div>
            <Button
                type="button"
                variant="secondary"
                shape="pill"
                loading={acknowledgingBadge}
                on:click={acknowledgeBadge}
            >
                Confirmar recebimento
            </Button>
        </div>
    {/if}
</Modal>
