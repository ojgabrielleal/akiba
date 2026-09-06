<script>
    import { Link, page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { publicAnimations } from "@/lib/constants";
    import { Layout } from "@/lib/layouts/public";
    import { OAuthAction, rememberOAuthAction, themeClass } from "@/lib/utils";

    const providers = [
        {
            name: "google",
            label: "Entrar com Google",
            icon: "/svg/google.svg",
            class: "border border-suspense-aurora/15 bg-suspense-aurora text-blue-night hover:bg-neutral-white",
        },
        {
            name: "discord",
            label: "Entrar com Discord",
            icon: "/svg/discord.svg",
            class: "bg-[#5865f2] text-neutral-white hover:brightness-110",
        },
    ];

    const contexts = {
        song_request: {
            eyebrow: "Rede Akiba no ar",
            title: "Sintonize seu pedido",
            description: "Entre com sua conta para pedir música, mandar recado e participar da programação da Rede Akiba.",
            action: OAuthAction.OPEN_SONG_REQUEST,
            icon: "/svg/music.svg",
        },
        event_submission: {
            eyebrow: "Radar da Rede Akiba",
            title: "Avise a nossa central",
            description: "Entre com sua conta para indicar um evento otaku e colocar a cobertura no radar da Rede Akiba.",
            icon: "/svg/events.svg",
        },
        enigmagame: {
            eyebrow: "Frequência misteriosa",
            title: "Entre na investigação",
            description: "Use sua conta para perguntar, responder e participar dos enigmas da Rede Akiba.",
            icon: "/svg/search.svg",
        },
        default: {
            eyebrow: "Comunidade sintonizada",
            title: "Entre para continuar",
            description: "Use sua conta para comentar, reagir, pedir músicas e participar da Rede Akiba.",
            icon: "/svg/profile.svg",
        },
    };
    const internalContext = {
        eyebrow: "Equipe Rede Akiba",
        title: "Reconhecemos você",
        description: "Você é um membro da Rede Akiba. Para continuar por aqui, entra no painel rapidinho e ativa sua sessão interna.",
        icon: "/svg/profile.svg",
    };

    $: ({ flash, oauth, onair, stream, authContext } = $page.props);
    $: pageUrl = $page.url;
    $: redirect = authContext?.redirect ?? "/";
    $: isInternalLogin = oauth?.is_member && !oauth?.member_session_authenticated;
    $: context = isInternalLogin ? internalContext : (contexts[authContext?.reason] ?? contexts.default);
    $: iconInverted = authContext?.reason === "song_request";
    const providerHref = (provider) =>
        `/oauth/${provider.name}/redirect?redirect=${encodeURIComponent(redirect)}`;

    const authenticate = () => {
        rememberOAuthAction(context.action);
    };

    const iconStyle = (icon) => `mask-image: url('${icon}'); -webkit-mask-image: url('${icon}');`;
</script>

<Meta meta={{ title: context.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="public-page-background relative isolate overflow-hidden bg-blue-night text-suspense-aurora">
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: url('/img/textures/stars.webp');" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-32 bg-linear-to-b from-blue-night to-transparent" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 h-32 bg-linear-to-t from-blue-night to-transparent" aria-hidden="true"></div>

        <div class="container-page relative flex min-h-[calc(100vh-9rem)] items-center justify-center py-16 text-center">
            <article class="mx-auto flex w-full max-w-xl flex-col items-center font-noto-sans">
                <div class="flex items-center gap-3 text-[0.7rem] font-black uppercase tracking-[0.22em] text-orange-amber/90">
                    <span class="h-px w-9 bg-linear-to-r from-transparent to-orange-amber/70"></span>
                    {context.eyebrow}
                    <span class="h-px w-9 bg-linear-to-l from-transparent to-orange-amber/70"></span>
                </div>

                <div class="mt-5 flex items-center justify-center gap-2">
                    <span class="h-6 w-1.5 rounded-full bg-blue-skywave/45"></span>
                    <span class="h-11 w-1.5 rounded-full bg-orange-amber"></span>
                    <span class="flex h-16 w-18 items-center justify-center rounded-md bg-orange-amber shadow-[0_1rem_2.25rem_rgba(255,128,0,0.24)]">
                        <img
                            src={context.icon}
                            alt=""
                            aria-hidden="true"
                            class={[
                                "size-9 filter-blue-marinho",
                                iconInverted && "rotate-180",
                            ]}
                        />
                    </span>
                    <span class="h-11 w-1.5 rounded-full bg-orange-amber"></span>
                    <span class="h-6 w-1.5 rounded-full bg-blue-skywave/45"></span>
                </div>

                <h1 class="mt-6 font-noto-sans text-3xl font-black leading-tight text-suspense-aurora sm:text-4xl">
                    {context.title}
                </h1>

                <p class="mt-4 max-w-md font-noto-sans text-base font-semibold leading-7 text-neutral-gray">
                    {context.description}
                </p>

                <div class="mt-8 w-full max-w-xs">
                    {#if isInternalLogin}
                        <a
                            href="/panel"
                            class={[
                                "flex min-h-11 items-center justify-center gap-3 rounded-md bg-orange-amber px-6 py-2 font-noto-sans text-sm font-extrabold uppercase shadow-[0_0.75rem_1.5rem_rgba(255,163,26,0.22)] hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                themeClass("text", "blue-night", { fixed: true }),
                                publicAnimations.buttonInteractive,
                            ]}
                        >
                            <span
                                aria-hidden="true"
                                class="block size-4 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                style="mask-image: url('/svg/profile.svg'); -webkit-mask-image: url('/svg/profile.svg');"
                            ></span>
                            Entrar no painel
                        </a>
                    {:else if oauth?.authenticated}
                        <Link
                            href={redirect}
                            class={[
                                "flex min-h-11 items-center justify-center rounded-md bg-orange-amber px-6 py-2 font-noto-sans text-sm font-extrabold uppercase shadow-[0_0.75rem_1.5rem_rgba(255,163,26,0.22)] hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                themeClass("text", "blue-night", { fixed: true }),
                                publicAnimations.buttonInteractive,
                            ]}
                        >
                            Continuar
                        </Link>
                    {:else}
                        <div class="grid gap-3">
                            {#each providers as provider}
                                <a
                                    href={providerHref(provider)}
                                    class={[
                                        "group/provider flex min-h-12 items-center justify-center gap-3 rounded-md px-6 py-2.5 text-center text-sm font-extrabold uppercase shadow-sm hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                        provider.class,
                                        publicAnimations.buttonInteractive,
                                    ]}
                                    on:click={authenticate}
                                >
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-current/10 transition group-hover/provider:scale-110" aria-hidden="true">
                                        <span
                                            class="block size-4 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                            style={iconStyle(provider.icon)}
                                        ></span>
                                    </span>
                                    <span class="min-w-0">{provider.label}</span>
                                </a>
                            {/each}
                        </div>
                        <div class="mt-6 flex items-center justify-center gap-2 text-[0.68rem] font-bold uppercase tracking-[0.12em] text-blue-skywave">
                            <span class="h-px flex-1 bg-suspense-aurora/10" aria-hidden="true"></span>
                            <span>Acesso seguro pela comunidade</span>
                            <span class="h-px flex-1 bg-suspense-aurora/10" aria-hidden="true"></span>
                        </div>
                    {/if}

                    <div class="mt-7 flex items-center justify-center gap-3">
                        <span class="h-px w-10 bg-blue-skywave/25" aria-hidden="true"></span>
                        <Link
                            href={redirect}
                            class={[
                                "inline-flex min-h-10 min-w-24 items-center justify-center rounded-md border border-orange-amber/55 bg-blue-night/35 px-5 py-2 font-noto-sans text-xs font-extrabold uppercase text-orange-amber hover:bg-orange-amber/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
                                publicAnimations.buttonInteractive,
                            ]}
                        >
                            Voltar
                        </Link>
                        <span class="h-px w-10 bg-blue-skywave/25" aria-hidden="true"></span>
                    </div>
                </div>
            </article>
        </div>
    </section>
</Layout>
