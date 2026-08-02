<script>
    import { usePoll } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { CookieConsent, FlashToaster, ProfileIncompleteNotice } from "@/lib/components/public";
    import { startPublicPresence, stopPublicPresence } from "@/lib/stores";
    import { Footer, Navbar, PlayerBar } from "@/lib/widgets/public";

    export let flash = null;
    export let oauth = {};
    export let onair = null;
    export let stream = null;
    export let pageUrl = null;

    usePoll(10 * 1000, {
        only: ["onair"]
    });

    onMount(() => {
        startPublicPresence(oauth);

        return stopPublicPresence;
    });
</script>

<FlashToaster {flash} />
<header class="bg-blue-night">
    <Navbar {oauth} />
</header>

<main>
    <slot />
</main>

<Footer />
<PlayerBar {onair} {stream} {pageUrl} {oauth} />
{#if oauth?.is_oauth && !oauth?.profile_completed}
    <ProfileIncompleteNotice />
{/if}
<CookieConsent />
