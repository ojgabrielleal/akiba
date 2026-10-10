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
    export let providersLayout = "list";
    export let reason = "default";
    export let redirectTo = null;

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

    const authenticate = (event, url = null) => {
        rememberOAuthAction(action);

        if (!url || !url.startsWith("/oauth/")) return;

        const popup = openOAuthPopup(url);

        if (popup) {
            event.preventDefault();
            popup.focus();
        }
    };

    const openOAuthPopup = (url) => {
        const width = 520;
        const height = 720;
        const left = window.screenX + Math.max(0, (window.outerWidth - width) / 2);
        const top = window.screenY + Math.max(0, (window.outerHeight - height) / 2);

        return window.open(
            url,
            "akiba_oauth",
            `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
        );
    };

</script>

{#if isAuthenticated}
    <slot />
{:else}
    <div class={["flex flex-col items-center text-center font-noto-sans", compact ? "" : "px-5 py-6", containerClass]}>
        {#if !compact}
            <span class="mb-3 flex items-center justify-center gap-2 text-orange-citric">
                <span class="h-px w-8 bg-orange-citric/70" aria-hidden="true"></span>
                <img
                    src="/svg/akiba.svg"
                    alt=""
                    aria-hidden="true"
                    class="size-8 filter-orange-citric"
                    loading="lazy"
                />
                <span class="h-px w-8 bg-orange-citric/70" aria-hidden="true"></span>
            </span>
            <p class={["text-sm font-black uppercase italic", titleClass, themeClass("text", "blue-night", { theme: "light" })]}>
                {title}
            </p>
            <p class={["mt-1 max-w-72 text-sm leading-snug", descriptionClass, themeClass("text", "blue-night/70", { theme: "light" })]}>
                {description}
            </p>
        {/if}

        {#if compact}
            <a
                href={authHref}
                class={[
                    "flex min-h-9 cursor-pointer items-center gap-2 rounded-full bg-orange-citric px-4 py-2 text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                    buttonClass,
                    "!text-white",
                ]}
                on:click={(event) => authenticate(event, authHref)}
            >
                <MaskIcon
                    icon="/svg/profile.svg"
                    class="size-5 !bg-white"
                />
                {buttonLabel ?? "Entrar"}
            </a>
        {:else}
            {#if providersLayout === "inline"}
                <div class="mt-4 flex items-center justify-center gap-4">
                    {#each loginProviders as provider}
                        <a
                            href={`/oauth/${provider.name}/redirect`}
                            class={[
                                "group/provider flex size-12 items-center justify-center rounded-full transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                provider.class,
                            ]}
                            aria-label={provider.label}
                            title={provider.label}
                            on:click={(event) => authenticate(event, `/oauth/${provider.name}/redirect`)}
                        >
                            <MaskIcon
                                icon={provider.icon}
                                class={["size-5 bg-current transition group-hover/provider:scale-110", themeClass("bg", "neutral-white", { theme: "light" })]}
                            />
                        </a>
                    {/each}
                </div>
            {:else}
                <div class="mt-5 grid w-full max-w-sm gap-3">
                    {#each loginProviders as provider}
                        <a
                            href={`/oauth/${provider.name}/redirect`}
                            class={[
                                "flex min-h-12 items-center justify-center gap-3 rounded-md px-5 py-2.5 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                provider.class,
                                themeClass("text", "blue-night", { theme: "light" }),
                            ]}
                            on:click={(event) => authenticate(event, `/oauth/${provider.name}/redirect`)}
                        >
                            <MaskIcon
                                icon={provider.icon}
                                class={["size-4 shrink-0 bg-current", themeClass("bg", "neutral-white", { theme: "light" })]}
                            />
                            {provider.label}
                        </a>
                    {/each}
                </div>
            {/if}
        {/if}
    </div>
{/if}
