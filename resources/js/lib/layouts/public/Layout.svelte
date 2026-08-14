<script>
    import { onMount } from "svelte";
    import { usePoll } from "@inertiajs/svelte";
    import { CookieConsent, FlashToaster } from "@/lib/components/public";
    import { startAutoplay } from "@/lib/stores";
    import { Footer, Navbar, PlayerBar } from "@/lib/widgets/public";

    export let flash = null;
    export let oauth = {};
    export let onair = null;
    export let stream = null;
    export let pageUrl = null;

    usePoll(10 * 1000, {
        only: ["onair"]
    });

    onMount(() => startAutoplay());
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
<CookieConsent />
