<script>
    import { onMount } from "svelte";
    import { usePoll } from "@inertiajs/svelte";
    import { FlashToaster } from "@/ui/components/private";
    import { Navbar, StreamMetricsGrid } from "@/ui/widgets/private";

    // Polling for updates in audience, audience history, song requests and stream status every 60 seconds
    usePoll(60 * 1000, {
        only: ["songRequests", "audience", "audienceHistory", "stream"],
    });

    // Set background color on mount
    onMount(() => {
        document.body.style.backgroundColor = "var(--color-blue-marinho)";
    });
</script>

<FlashToaster />
<header class="mb-8 lg:mb-20 mt-5 lg:mt-10">
    <Navbar />
</header>
<main id="conteudo-principal" class="min-w-0 w-full max-w-full overflow-x-hidden">
    <slot />
</main>
<footer>
    <div class="h-20"></div>
    <div class="w-full fixed bottom-0 z-50">
        <StreamMetricsGrid />
    </div>
</footer>
