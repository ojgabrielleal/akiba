<script>
    import { onMount } from "svelte";
    import { page, usePoll } from "@inertiajs/svelte";
    import toast, { Toaster } from "svelte-hot-french-toast";
    import { Navbar, StreamMetricsGrid } from "@/ui/widgets/private";

    $: ({ flash } = $page.props);

    // Toasts configurations ( Flash Message )
    let lastToast = null;
    $: toastId = flash?.id ?? flash?.message;

    $: if (flash?.message && toastId !== lastToast) {
        lastToast = toastId;

        toast(flash.message, {
            icon: flash.icon,
        });
    }

    // Polling for updates in audience, song requests and stream status every 60 seconds
    usePoll(60 * 1000, {
        only: ["songRequests", "audience", "stream"],
    });

    // Set background color on mount
    onMount(() => {
        document.body.style.backgroundColor = "var(--color-blue-marinho)";
    });
</script>

<Toaster />
<header class="mb-8 lg:mb-20 mt-5 lg:mt-10">
    <Navbar />
</header>
<main>
    <slot />
</main>
<footer>
    <div class="h-20"></div>
    <div class="w-full fixed bottom-0 z-50">
        <StreamMetricsGrid />
    </div>
</footer>
