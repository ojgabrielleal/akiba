<script>
    import { page } from "@inertiajs/svelte";
    import toast, { Toaster } from "svelte-hot-french-toast";

    $: flash = $page.props.flash;

    let lastToastId = null;

    $: if (flash?.id && flash.id !== lastToastId) {
        lastToastId = flash.id;
        toast[flash.type](flash.message, {
            icon: flash.icon,
        });
    }
</script>

<Toaster />
