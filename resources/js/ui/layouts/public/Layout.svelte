<script>
    import { usePoll } from "@inertiajs/svelte";
    import { FlashToaster } from "@/ui/components/public";
    import { Footer, Navbar, PlayerBar } from "@/ui/widgets/public";

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
<PlayerBar {onair} {stream} {pageUrl} />
