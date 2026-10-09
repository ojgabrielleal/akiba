<script>
    import { onDestroy, onMount } from "svelte";
    import { Button } from "@/lib/components/public";

    const dismissedUntilKey = "akiba:pwa-install-dismissed-until";
    const dismissedForDays = 7;

    let installEvent = null;
    let visible = false;
    let installing = false;

    const isMobileViewport = () => window.matchMedia("(max-width: 767px)").matches;
    const isStandalone = () => (
        window.matchMedia("(display-mode: standalone)").matches ||
        window.navigator.standalone === true
    );

    const isDismissed = () => {
        const dismissedUntil = Number(localStorage.getItem(dismissedUntilKey) ?? 0);

        return dismissedUntil > Date.now();
    };

    const showIfAllowed = () => {
        visible = Boolean(installEvent) && isMobileViewport() && !isStandalone() && !isDismissed();
    };

    const dismiss = () => {
        const dismissedUntil = Date.now() + dismissedForDays * 24 * 60 * 60 * 1000;

        localStorage.setItem(dismissedUntilKey, String(dismissedUntil));

        visible = false;
    };

    const install = async () => {
        if (!installEvent || installing) return;

        installing = true;
        installEvent.prompt();

        const choice = await installEvent.userChoice.catch(() => null);

        if (choice?.outcome !== "accepted") {
            dismiss();
        } else {
            visible = false;
        }

        installEvent = null;
        installing = false;
    };

    const handleBeforeInstallPrompt = (event) => {
        event.preventDefault();
        installEvent = event;
        showIfAllowed();
    };

    const handleAppInstalled = () => {
        installEvent = null;
        visible = false;
    };

    const handleResize = () => showIfAllowed();

    onMount(() => {
        window.addEventListener("beforeinstallprompt", handleBeforeInstallPrompt);
        window.addEventListener("appinstalled", handleAppInstalled);
        window.addEventListener("resize", handleResize);

        return () => {
            window.removeEventListener("beforeinstallprompt", handleBeforeInstallPrompt);
            window.removeEventListener("appinstalled", handleAppInstalled);
            window.removeEventListener("resize", handleResize);
        };
    });

    onDestroy(() => {
        document.body.style.overflow = "";
    });

    $: if (typeof document !== "undefined") {
        document.body.style.overflow = visible ? "hidden" : "";
    }
</script>

{#if visible}
    <div
        class="fixed inset-0 z-[210] flex h-[100dvh] items-end justify-center bg-blue-night/60 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] backdrop-blur-sm md:hidden"
        role="presentation"
        on:click|self={dismiss}
    >
        <div
            class="relative w-full max-w-sm overflow-hidden rounded-md border border-orange-amber/35 bg-blue-ocean text-suspense-aurora shadow-2xl shadow-blue-night/60"
            role="dialog"
            aria-modal="true"
            aria-labelledby="pwa-install-title"
        >
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,183,3,0.24),transparent_42%),linear-gradient(135deg,rgba(0,145,255,0.28),transparent_55%)]" aria-hidden="true"></div>
            <div class="absolute -right-10 -top-10 size-32 rounded-full border border-suspense-aurora/10" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-orange-amber" aria-hidden="true"></div>

            <div class="relative p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid size-14 shrink-0 place-items-center rounded-md bg-suspense-aurora shadow-lg shadow-blue-night/35">
                        <img
                            src="/img/pwa/icon-256.png"
                            alt=""
                            aria-hidden="true"
                            class="size-11 rounded-sm object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="mb-1 w-fit rounded-sm bg-orange-amber px-2 py-0.5 font-noto-sans text-[0.62rem] font-black uppercase italic leading-none text-blue-night">
                            App para o celular
                        </p>
                        <h2 id="pwa-install-title" class="font-noto-sans text-xl font-black uppercase italic leading-none tracking-normal">
                            Instale o app da akiba
                        </h2>
                    </div>
                </div>

                <p class="font-noto-sans text-sm font-bold leading-snug text-suspense-aurora/82">
                    Ouça a rádio, leia matérias e interaja somente com um toque, na palma da sua mão.
                </p>

                <div class="mt-4 grid grid-cols-[0.85fr_1.15fr] gap-2">
                    <Button type="button" variant="ghost" size="sm" class="w-full !text-xs" on:click={dismiss} disabled={installing}>
                        Agora não
                    </Button>
                    <Button type="button" variant="primary" size="sm" class="w-full !bg-orange-citric !text-xs" on:click={install} loading={installing} disabled={!installEvent}>
                        Instalar app
                    </Button>
                </div>
            </div>
        </div>
    </div>
{/if}
