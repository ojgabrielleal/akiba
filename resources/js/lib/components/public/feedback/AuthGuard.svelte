<script>
    import { page } from "@inertiajs/svelte";
    import { rememberOAuthAction, themeClass } from "@/lib/utils";

    export let oauth = {};
    export let title = "Entre para continuar";
    export let description = "Você precisa estar autenticado para acessar este conteúdo.";
    export let buttonLabel = null;
    export let filters = "filter-blue-night";
    export let providers = [
        {
            name: "google",
            label: "Entrar com Google",
            icon: "/svg/google.svg",
            iconClass: "",
            class: "border border-blue-night/10 bg-neutral-white text-blue-night shadow-sm hover:border-orange-amber/70 hover:bg-neutral-white",
        },
        {
            name: "discord",
            label: "Entrar com Discord",
            icon: "/svg/discord.svg",
            class: "bg-[#5865f2] text-neutral-white shadow-[0_0.75rem_1.5rem_rgba(88,101,242,0.28)] hover:brightness-110",
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
    $: authHref = `/entrar?reason=${encodeURIComponent(reason)}&redirect=${encodeURIComponent(authRedirect)}`;
    $: resolvedOAuth = oauth ?? $page.props.oauth ?? {};
    $: isAuthenticated = Boolean(resolvedOAuth?.authenticated);

    const authenticate = () => {
        rememberOAuthAction(action);
    };

    const providerIconStyle = (provider) => `mask-image: url('${provider.icon}'); -webkit-mask-image: url('${provider.icon}');`;
</script>

{#if isAuthenticated}
    <slot />
{:else}
    <div class={["flex flex-col items-center text-center font-noto-sans", compact ? "" : "px-5 py-6", containerClass]}>
        {#if !compact}
            <span class="mb-3 flex size-10 items-center justify-center rounded-md bg-orange-citric text-blue-night">
                <span
                    class="block size-5 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                    style="mask-image: url('/svg/profile.svg'); -webkit-mask-image: url('/svg/profile.svg');"
                ></span>
            </span>
            <p class={["text-sm font-black uppercase italic", titleClass]}>
                {title}
            </p>
            <p class={["mt-1 max-w-72 text-sm leading-snug", descriptionClass]}>
                {description}
            </p>
        {/if}

        {#if compact}
            <a
                href={authHref}
                class={[
                    "flex min-h-9 cursor-pointer items-center gap-2 rounded-full bg-orange-amber px-4 py-2 text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                    buttonClass,
                ]}
                on:click={authenticate}
            >
                <img
                    src="/svg/profile.svg"
                    alt=""
                    aria-hidden="true"
                    class={["h-4 w-4", filters]}
                />
                {buttonLabel ?? "Entrar"}
            </a>
        {:else}
            <div
                class={[
                    "mt-5 grid w-full gap-3",
                    providersLayout === "inline" ? "max-w-xl sm:grid-cols-2" : "max-w-sm",
                ]}
            >
                {#each loginProviders as provider}
                    <a
                        href={`/oauth/${provider.name}/redirect`}
                        class={[
                            "flex min-h-12 items-center justify-center gap-3 rounded-md px-5 py-2.5 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                            provider.class,
                        ]}
                        on:click={authenticate}
                    >
                        <span
                            aria-hidden="true"
                            class="block size-4 shrink-0 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                            style={providerIconStyle(provider)}
                        ></span>
                        {provider.label}
                    </a>
                {/each}
            </div>
        {/if}
    </div>
{/if}
