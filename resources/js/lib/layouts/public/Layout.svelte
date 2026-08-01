<script>
    import { usePoll } from "@inertiajs/svelte";
    import { CookieConsent, FlashToaster, ProfileIncompleteNotice } from "@/lib/components/public";
    import { Footer, Navbar, PlayerBar } from "@/lib/widgets/public";

    export let flash = null;
    export let oauth = {};
    export let onair = null;
    export let stream = null;
    export let pageUrl = null;

    usePoll(10 * 1000, {
        only: ["onair"]
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
{#if oauth?.authenticated && !oauth?.profile_completed}
    <ProfileIncompleteNotice />
{/if}
<CookieConsent />
