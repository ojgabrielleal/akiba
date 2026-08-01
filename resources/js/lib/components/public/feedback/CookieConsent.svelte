<script>
    import { onDestroy, onMount } from "svelte";

    const STORAGE_KEY = "akiba_cookie_consent";

    let visible = false;
    let playerBarVisible = false;

    const dispatchVisibility = () => {
        window.dispatchEvent(new CustomEvent("akiba:cookie-consent-visibility", {
            detail: { visible },
        }));
    };

    const updatePlayerBarVisibility = (event) => {
        playerBarVisible = Boolean(event.detail?.visible);
    };

    onMount(() => {
        try {
            visible = localStorage.getItem(STORAGE_KEY) !== "accepted";
        } catch {
            visible = true;
        }

        window.addEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
        dispatchVisibility();
    });

    onDestroy(() => {
        window.removeEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
    });

    const accept = () => {
        try {
            localStorage.setItem(STORAGE_KEY, "accepted");
        } catch {
            // The banner can still be dismissed during the current visit.
        }

        visible = false;
        dispatchVisibility();
    };
</script>

{#if visible}
    <aside
        class={[
            "fixed right-4 left-4 z-140 font-noto-sans text-suspense-aurora transition-[bottom] duration-300 ease-out sm:left-auto sm:w-[min(24rem,calc(100vw-2rem))]",
            playerBarVisible ? "bottom-22" : "bottom-4",
        ]}
        role="status"
        aria-label="Aviso sobre cookies e dados"
    >
        <div class="rounded-md border border-dashed border-orange-citric/35 bg-blue-night/95 p-4 shadow-2xl backdrop-blur-md">
            <p class="text-sm leading-snug text-suspense-aurora/75">
                Usamos cookies e dados essenciais para manter sua sessão, lembrar preferências e melhorar a experiência na Akiba.
            </p>
            <button
                type="button"
                class="mt-3 inline-flex min-h-9 cursor-pointer items-center justify-center rounded-full bg-orange-citric px-4 py-2 font-noto-sans text-xs font-extrabold uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                on:click={accept}
            >
                Entendi
            </button>
        </div>
    </aside>
{/if}
