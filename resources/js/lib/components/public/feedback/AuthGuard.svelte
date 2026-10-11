<script>
    import { page } from "@inertiajs/svelte";
    import MaskIcon from "@/lib/components/public/media/MaskIcon.svelte";
    import { rememberOAuthAction, themeClass } from "@/lib/utils";

    export let oauth = null;
    export let title = "Entre para continuar";
    export let description = "Você precisa estar autenticado para acessar este conteúdo.";
    export let buttonLabel = null;
    export let providers = [
        {
            name: "google",
            label: "Entrar com Google",
            icon: "/svg/google.svg",
            iconClass: "",
            class: "bg-neutral-white text-blue-night shadow-[0_0.75rem_1.5rem_rgba(0,0,20,0.22)] hover:bg-orange-citric",
        },
        {
            name: "discord",
            label: "Entrar com Discord",
            icon: "/svg/discord.svg",
            class: "bg-[#5865f2] text-neutral-white shadow-[0_0.75rem_1.5rem_rgba(0,0,20,0.24)] hover:bg-orange-citric hover:text-blue-night",
        },
    ];
    export let containerClass = "";
    export let titleClass = "text-blue-night";
    export let descriptionClass = "text-blue-night/60";
    export let buttonClass = themeClass("text", "blue-night", { fixed: true });
    export let action = null;
    export let compact = false;
    export let reason = "default";
    export let redirectTo = null;
    export let variant = "default";

    $: loginProviders = providers.length === 1 && buttonLabel
        ? [{ ...providers[0], label: buttonLabel }]
        : providers;
    $: currentUrl = $page.url ?? "/";
    $: authRedirect = redirectTo ?? currentUrl;
    $: resolvedOAuth = oauth ?? $page.props.oauth ?? {};
    $: recognizedPanelHref = `/panel/recognized-auth?redirect=${encodeURIComponent(authRedirect)}`;
    $: authHref = resolvedOAuth?.internal_browser_recognized
        ? recognizedPanelHref
        : `/entrar?reason=${encodeURIComponent(reason)}&redirect=${encodeURIComponent(authRedirect)}`;
    $: isAuthenticated = Boolean(resolvedOAuth?.authenticated);
    $: isSongRequestVariant = variant === "song-request";

    const authenticate = () => {
        rememberOAuthAction(action);
    };

</script>

{#if isAuthenticated}
    <slot />
{:else}
    <div class={[
        "flex flex-col items-center text-center font-noto-sans",
        compact ? "" : isSongRequestVariant ? "px-4 py-5 sm:px-6 sm:py-6" : "px-5 py-6",
        isSongRequestVariant && "mx-auto w-full max-w-sm",
        containerClass,
    ]}>
        {#if !compact}
            {#if isSongRequestVariant}
                <div class="mb-3 flex items-center justify-center gap-2 text-[0.62rem] font-black uppercase tracking-[0.22em] text-blue-skywave">
                    <span class="h-px w-10 bg-linear-to-r from-transparent to-blue-skywave/80" aria-hidden="true"></span>
                    Pedido musical
                    <span class="h-px w-10 bg-linear-to-l from-transparent to-blue-skywave/80" aria-hidden="true"></span>
                </div>
            {/if}
            <span class={[
                "flex items-center justify-center text-orange-citric",
                isSongRequestVariant ? "mb-4 gap-2.5" : "mb-3 gap-2",
            ]}>
                <span class={[
                    "h-px bg-orange-citric/70",
                    isSongRequestVariant ? "w-12" : "w-8",
                ]} aria-hidden="true"></span>
                {#if isSongRequestVariant}
                    <span class="flex items-center gap-1.5" aria-hidden="true">
                        <span class="h-5 w-1 rounded-full bg-blue-skywave/70"></span>
                        <img
                            src="/svg/akiba.svg"
                            alt=""
                            class="size-8 filter-orange-citric"
                            width="32"
                            height="32"
                        />
                        <span class="h-5 w-1 rounded-full bg-blue-skywave/70"></span>
                    </span>
                {:else}
                    <img
                        src="/svg/akiba.svg"
                        alt=""
                        aria-hidden="true"
                        class="size-8 filter-orange-citric"
                        width="32"
                        height="32"
                    />
                {/if}
                <span class={[
                    "h-px bg-orange-citric/70",
                    isSongRequestVariant ? "w-12" : "w-8",
                ]} aria-hidden="true"></span>
            </span>
            <p class={[
                isSongRequestVariant ? "text-[0.92rem] tracking-wide" : "text-sm",
                "font-black uppercase italic",
                titleClass,
                themeClass("text", "blue-night", { theme: "light" }),
            ]}>
                {title}
            </p>
            <p class={[
                "mt-1.5 max-w-72 leading-snug",
                isSongRequestVariant ? "text-[0.84rem] font-semibold" : "text-sm",
                descriptionClass,
                themeClass("text", "blue-night/70", { theme: "light" }),
            ]}>
                {description}
            </p>
            {#if isSongRequestVariant}
                <p class="mt-3 text-[0.65rem] font-black uppercase tracking-[0.16em] text-orange-citric/90">
                    Volte ao pedido depois de entrar
                </p>
            {/if}
        {/if}

        {#if compact}
            <a
                href={authHref}
                class={[
                    "flex min-h-9 cursor-pointer items-center gap-2 rounded-full bg-orange-citric px-4 py-2 text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                    buttonClass,
                    "!text-white",
                ]}
                on:click={authenticate}
            >
                <MaskIcon
                    icon="/svg/profile.svg"
                    class="size-5 !bg-white"
                />
                {buttonLabel ?? "Entrar"}
            </a>
        {:else}
            <a
                href={authHref}
                class={[
                    isSongRequestVariant
                        ? "mt-5 flex min-h-11 w-full max-w-72 items-center justify-center gap-2.5 rounded-full bg-orange-citric px-6 py-2.5 text-center font-noto-sans text-sm font-black uppercase italic shadow-[0_0.9rem_1.6rem_rgba(255,170,53,0.2)] transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                        : "mt-5 flex min-h-11 w-full max-w-xs items-center justify-center gap-3 rounded-full bg-orange-citric px-5 py-2.5 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                    themeClass("text", "blue-night", { fixed: true }),
                    "!text-white",
                ]}
                on:click={authenticate}
            >
                <MaskIcon icon="/svg/profile.svg" class="size-5 shrink-0 !bg-white" />
                Entrar
            </a>
        {/if}
    </div>
{/if}
