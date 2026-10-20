<script>
    import { onMount } from "svelte";
    import Cookies from "js-cookie";
    import { publicAnimations } from "@/lib/constants";
    import { defaultPublicTheme, getStoredPublicTheme, scheduleAfterPageLoad } from "@/lib/utils";

    const COOKIE_KEY = "akiba_cookie_consent";

    export let publicThemeEnabled = false;

    let visible = false;
    let consentHeight = 0;
    let playerBarVisible = false;
    let selectedTheme = defaultPublicTheme;

    const dispatchVisibility = () => {
        window.dispatchEvent(new CustomEvent("akiba:cookie-consent-visibility", {
            detail: { visible, height: visible ? consentHeight : 0 },
        }));
    };

    $: if (consentHeight > 0) {
        dispatchVisibility();
    }

    const updatePlayerBarVisibility = (event) => {
        playerBarVisible = Boolean(event.detail?.visible);
    };

    onMount(() => {
        selectedTheme = getStoredPublicTheme();
        window.addEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
        dispatchVisibility();
        const stopTimer = scheduleAfterPageLoad(() => {
            visible = Cookies.get(COOKIE_KEY) !== "accepted";
            dispatchVisibility();
        });

        return () => {
            stopTimer();
            window.removeEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
        };
    });

    const accept = () => {
        Cookies.set(COOKIE_KEY, "accepted", {
            expires: 365,
            sameSite: "lax",
        });

        visible = false;
        dispatchVisibility();
    };
</script>

{#if visible}
    <aside
        bind:clientHeight={consentHeight}
        class={[
            "cookie-notice fixed inset-x-4 z-140 mx-auto max-w-2xl font-noto-sans text-suspense-aurora transition-[bottom] duration-300 ease-out motion-reduce:transition-none",
            playerBarVisible
                ? "bottom-[calc(5.5rem+env(safe-area-inset-bottom))]"
                : "bottom-[calc(1rem+env(safe-area-inset-bottom))]",
        ]}
        data-public-theme-scope={publicThemeEnabled ? "" : null}
        data-public-theme={publicThemeEnabled ? selectedTheme : null}
        role="status"
        aria-label="Aviso sobre cookies e dados"
    >
        <div class="public-cookie-consent rounded-xl border border-suspense-aurora/10 bg-blue-night/95 shadow-lg shadow-blue-night/20 backdrop-blur-md [[data-public-theme=light]_&]:border-[#000014]/10 [[data-public-theme=light]_&]:bg-white/95">
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:gap-5 sm:px-5">
                <p class="public-cookie-consent-text min-w-0 text-xs leading-relaxed text-suspense-aurora/75 [[data-public-theme=light]_&]:!text-[#000014]/75">
                    Usamos cookies essenciais para manter sua sessão e lembrar suas preferências.
                </p>
                <button
                    type="button"
                    class={["inline-flex min-h-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-orange-citric px-4 py-2 text-xs font-extrabold uppercase italic text-blue-night [[data-public-theme=light]_&]:!text-[#000014] hover:brightness-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric focus-visible:ring-offset-2 focus-visible:ring-offset-blue-night", publicAnimations.buttonInteractive]}
                    on:click={accept}
                >
                    Entendi
                </button>
            </div>
        </div>
    </aside>
{/if}

<style>
    @keyframes cookie-notice-enter {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cookie-notice {
        animation: cookie-notice-enter 300ms ease-out;
    }

    @media (prefers-reduced-motion: reduce) {
        .cookie-notice { animation: none; }
    }
</style>
