<script>
    import { onDestroy, onMount } from "svelte";
    import { OAuthAction, dispatchOAuthAction } from "@/lib/utils";

    let playerBarVisible = false;
    let cookieConsentVisible = false;

    const updatePlayerBarVisibility = (event) => {
        playerBarVisible = Boolean(event.detail?.visible);
    };

    const updateCookieConsentVisibility = (event) => {
        cookieConsentVisible = Boolean(event.detail?.visible);
    };

    onMount(() => {
        window.addEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
        window.addEventListener("akiba:cookie-consent-visibility", updateCookieConsentVisibility);
    });

    onDestroy(() => {
        window.removeEventListener("akiba:player-bar-visibility", updatePlayerBarVisibility);
        window.removeEventListener("akiba:cookie-consent-visibility", updateCookieConsentVisibility);
    });
</script>

<aside
    class={[
        "fixed right-4 left-4 z-140 font-noto-sans text-orange-morning transition-[bottom] duration-300 ease-out sm:left-auto sm:w-[min(24rem,calc(100vw-2rem))]",
        cookieConsentVisible
            ? (playerBarVisible ? "bottom-[15.5rem]" : "bottom-44")
            : (playerBarVisible ? "bottom-22" : "bottom-4"),
    ]}
    role="status"
    aria-label="Perfil incompleto"
>
    <div class="rounded-md border border-dashed border-orange-morning/35 bg-blue-night/95 p-4 shadow-2xl backdrop-blur-md">
        <p class="font-noto-sans text-sm font-extrabold uppercase italic">
            Perfil incompleto
        </p>
        <p class="mt-1 text-sm leading-snug text-suspense-aurora/75">
            Complete seu perfil para participar de tudo da Akiba.
        </p>
        <button
            type="button"
            class="mt-3 inline-flex min-h-9 cursor-pointer items-center justify-center rounded-full bg-orange-citric px-4 py-2 font-noto-sans text-xs font-extrabold uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
            on:click={() => dispatchOAuthAction(OAuthAction.OPEN_PROFILE)}
        >
            Completar perfil
        </button>
    </div>
</aside>
