<script>
    import { usePoll } from "@inertiajs/svelte";
    import { FlashToaster } from "@/ui/components/public";
    import { Navbar } from "@/ui/widgets/public";

    usePoll(10 * 1000, {
        only: ["onair"]
    });
</script>

<FlashToaster />
<header class="bg-blue-night">
    <Navbar />
</header>

<main>
    <slot />
</main>

<footer>
</footer>
