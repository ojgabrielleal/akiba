<script>
    import { usePoll } from "@inertiajs/svelte";
    import { Navbar } from "@/ui/widgets/public";

    usePoll(10 * 1000, {
        only: ["onair"]
    });
</script>

<header class="h-0">
    <Navbar />
</header>

<main id="conteudo-principal">
    <slot />
</main>

<footer>
</footer>
