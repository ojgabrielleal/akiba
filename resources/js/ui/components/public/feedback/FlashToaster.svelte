<script>
    import toast, { Toaster } from "svelte-hot-french-toast";

    export let flash = null;

    let lastToastId = null;

    $: if (flash?.id && flash.id !== lastToastId) {
        lastToastId = flash.id;
        toast[flash.type](flash.message, {
            icon: flash.icon,
        });
    }
</script>

<Toaster />
