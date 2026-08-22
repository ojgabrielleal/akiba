<script>
    import { rememberOAuthAction } from "@/lib/utils";
    import Modal from "../overlays/Modal.svelte";

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
            class: "border border-blue-night/10 bg-neutral-white text-blue-night shadow-sm hover:bg-neutral-white",
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
    export let buttonClass = "text-blue-night";
    export let action = null;
    export let compact = false;
    export let providersLayout = "list";

    let providerModalRef;

    $: loginProviders = providers.length === 1 && buttonLabel
        ? [{ ...providers[0], label: buttonLabel }]
        : providers;

    const openProviderModal = () => {
        providerModalRef.open();
    };

    const authenticate = () => {
        rememberOAuthAction(action);
    };

    const providerIconStyle = (provider) => `mask-image: url('${provider.icon}'); -webkit-mask-image: url('${provider.icon}');`;
</script>

{#if oauth.is_member && !oauth.member_session_authenticated}
    <div class={["flex flex-col items-center text-center font-noto-sans", compact ? "" : "py-7", containerClass]}>
        {#if !compact}
            <img
                src="/svg/profile.svg"
                alt=""
                aria-hidden="true"
                class={["mb-3 h-8 w-8", filters]}
            />
            <p class={["text-base font-extrabold uppercase italic", titleClass]}>
                Login do painel necessário
            </p>
            <p class={["mt-1 text-sm", descriptionClass]}>
                Sua conta interna foi reconhecida, mas a sessão do painel não está ativa.
            </p>
        {/if}

        <a
            href="/panel"
            class={[
                "mt-5 flex min-h-12 items-center justify-center gap-3 rounded-md bg-orange-amber px-5 py-2.5 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                buttonClass,
            ]}
        >
            <img
                src="/svg/profile.svg"
                alt=""
                aria-hidden="true"
                class={["h-4 w-4", filters]}
            />
            Entrar no painel
        </a>
    </div>
{:else if oauth.authenticated}
    <slot />
{:else}
    <div class={["flex flex-col items-center text-center font-noto-sans", compact ? "" : "py-7", containerClass]}>
        {#if !compact}
            <img
                src="/svg/profile.svg"
                alt=""
                aria-hidden="true"
                class={["mb-3 h-8 w-8", filters]}
            />
            <p class={["text-base font-extrabold uppercase italic", titleClass]}>
                {title}
            </p>
            <p class={["mt-1 text-sm", descriptionClass]}>
                {description}
            </p>
        {/if}

        {#if compact}
            <button
                type="button"
                class={[
                    "flex cursor-pointer items-center gap-2 rounded-full bg-orange-amber px-5 py-2.5 text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                    buttonClass,
                ]}
                on:click={openProviderModal}
            >
                <img
                    src="/svg/profile.svg"
                    alt=""
                    aria-hidden="true"
                    class={["h-4 w-4", filters]}
                />
                {buttonLabel ?? "Entrar"}
            </button>

            <Modal
                bind:this={providerModalRef}
                label="Escolha uma opção de login"
                size="sm"
            >
                <div class="px-1 py-2 text-center">
                    <div class="mb-5 flex flex-col items-center font-noto-sans">
                        <span class="mb-3 flex size-8 items-center justify-center text-blue-night">
                            <span
                                class="block size-7 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                style="mask-image: url('/svg/profile.svg'); -webkit-mask-image: url('/svg/profile.svg');"
                            ></span>
                        </span>
                        <p class="text-base font-extrabold uppercase italic text-blue-night">
                            Entre para continuar
                        </p>
                        <p class="mx-auto mt-1 max-w-64 text-sm font-normal leading-snug text-blue-night/70">
                            Use sua conta para comentar, reagir, pedir músicas e participar da Akiba.
                        </p>
                    </div>
                    <div class="grid gap-3">
                        {#each loginProviders as provider}
                            <a
                                href={`/oauth/${provider.name}/redirect`}
                                class={[
                                    "group/provider relative flex min-h-[3.25rem] items-center justify-center gap-3 overflow-hidden rounded-md px-5 py-3 text-center font-noto-sans text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                    provider.class,
                                ]}
                                on:click={authenticate}
                            >
                                <span
                                    aria-hidden="true"
                                    class="block size-4 shrink-0 bg-current transition group-hover/provider:scale-110 [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                    style={providerIconStyle(provider)}
                                ></span>
                                <span class="relative">{provider.label}</span>
                            </a>
                        {/each}
                    </div>
                </div>
            </Modal>
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
