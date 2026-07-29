<script>
    import { page } from "@inertiajs/svelte";
    import toast, { Toaster } from "svelte-hot-french-toast";

    $: ({ flash } = $page.props);

    let lastToast = null;
    $: toastId = flash?.id;

    $: if (flash && toastId !== lastToast) {
        lastToast = toastId;
        toast[flash.type](flash.message, {
            icon: flash.icon,
        });
    }
</script>
<Toaster />
