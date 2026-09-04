<script>
    import { onMount } from "svelte";
    import { page } from "@inertiajs/svelte";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/public";
    import { OAuthAction, rememberOAuthAction } from "@/lib/utils";

    const providers = [
        {
            name: "google",
            label: "Entrar com Google",
            icon: "/svg/google.svg",
            class: "border border-blue-night/10 bg-suspense-aurora text-blue-night hover:bg-neutral-white",
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
            eyebrow: "Rádio Akiba",
            title: "Pede aí",
            description: "Entre com sua conta para pedir música, mandar recado e aparecer no player da Akiba.",
            action: OAuthAction.OPEN_SONG_REQUEST,
            icon: "/svg/music.svg",
        },
        event_submission: {
            eyebrow: "Eventos da comunidade",
            title: "Conta pra gente",
            description: "Entre com sua conta para enviar um evento otaku para a Akiba avaliar e publicar no calendário.",
            icon: "/svg/events.svg",
        },
        mystery: {
            eyebrow: "Enigma da Akiba",
            title: "Entre na investigação",
            description: "Use sua conta para perguntar, responder e participar dos enigmas da Akiba.",
            icon: "/svg/duvid.svg",
        },
        default: {
            eyebrow: "Área da comunidade",
            title: "Entre para continuar",
            description: "Use sua conta para comentar, reagir, pedir músicas e participar da Akiba.",
            icon: "/svg/profile.svg",
        },
    };
    const internalContext = {
        eyebrow: "Membro Akiba",
        title: "Reconhecemos você",
        description: "Vi que você é um membro da Akiba. Para continuar por aqui, entra no painel rapidinho e ativa sua sessão interna.",
        icon: "/svg/profile.svg",
    };

    $: ({ flash, oauth, onair, stream, authContext } = $page.props);
    $: pageUrl = $page.url;
    $: redirect = authContext?.redirect ?? "/";
    $: isInternalLogin = oauth?.is_member && !oauth?.member_session_authenticated;
    $: context = isInternalLogin ? internalContext : (contexts[authContext?.reason] ?? contexts.default);
    const providerHref = (provider) =>
        `/oauth/${provider.name}/redirect?redirect=${encodeURIComponent(redirect)}`;

    const authenticate = () => {
        rememberOAuthAction(context.action);
    };

    const iconStyle = (icon) => `mask-image: url('${icon}'); -webkit-mask-image: url('${icon}');`;

    onMount(() => {
        window.scrollTo({ top: 118, behavior: "smooth" });
    });
</script>

<Meta meta={{ title: context.title }} />
<Layout {flash} {oauth} {onair} {stream} {pageUrl} publicThemeEnabled>
    <section class="relative min-h-[calc(100vh-5rem)] overflow-hidden bg-blue-night">
        <img
            src="/img/pages/auth/continue-hero.png"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover object-left"
        />
        <div class="absolute inset-0 bg-blue-night/10" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 bg-linear-to-b from-transparent via-blue-night/75 to-blue-night" aria-hidden="true"></div>

        <div class="container-page relative z-10 flex min-h-[calc(100vh-5rem)] items-center justify-center py-12 lg:justify-end">
            <article class="w-full max-w-120 overflow-hidden rounded-md bg-suspense-aurora/96 font-noto-sans shadow-[0_1.5rem_4rem_rgba(0,0,0,0.45)] backdrop-blur-sm lg:mr-6">
                <div class="grid grid-cols-[0.45rem_minmax(0,1fr)]">
                    <span class="bg-orange-citric" aria-hidden="true"></span>
                    <div class="p-5 lg:p-7">
                        <div class="mb-5 flex items-start gap-4">
                            <span class="mt-1 flex size-10 shrink-0 items-center justify-center rounded-md bg-orange-citric text-blue-night">
                                <span
                                    aria-hidden="true"
                                    class="block size-5 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                    style={iconStyle(context.icon)}
                                ></span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase italic text-orange-citric">
                                    {context.eyebrow}
                                </p>
                                <h1 class="text-2xl font-black uppercase italic leading-tight text-blue-night sm:text-3xl">
                                    {context.title}
                                </h1>
                                <p class="mt-0.5 max-w-100 text-sm font-semibold leading-5 text-blue-night/65">
                                    {context.description}
                                </p>
                            </div>
                        </div>

                        {#if isInternalLogin}
                            <a
                                href="/panel"
                                class="flex min-h-12 items-center justify-center gap-3 rounded-md bg-orange-amber px-5 py-2.5 text-center text-sm font-extrabold uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                <span
                                    aria-hidden="true"
                                    class="block size-4 bg-current [mask-repeat:no-repeat] [mask-position:center] [mask-size:contain] [-webkit-mask-repeat:no-repeat] [-webkit-mask-position:center] [-webkit-mask-size:contain]"
                                    style="mask-image: url('/svg/profile.svg'); -webkit-mask-image: url('/svg/profile.svg');"
                                ></span>
                                Entrar no painel
                            </a>
                        {:else if oauth?.authenticated}
                            <a
                                href={redirect}
                                class="flex min-h-12 items-center justify-center rounded-md bg-orange-amber px-5 py-2.5 text-center text-sm font-extrabold uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                            >
                                Continuar
                            </a>
                        {:else}
                            <div class="grid gap-3">
                                {#each providers as provider}
                                    <a
                                        href={providerHref(provider)}
                                        class={[
                                            "group/provider flex min-h-13 items-center justify-center gap-3 rounded-md px-5 py-3 text-center text-sm font-extrabold uppercase italic shadow-sm transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                                            provider.class,
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
                            <div class="mt-4 flex items-center justify-center gap-2 text-[0.68rem] font-bold uppercase text-blue-night/40">
                                <span class="h-px flex-1 bg-blue-night/10" aria-hidden="true"></span>
                                <span>Acesso seguro pela comunidade</span>
                                <span class="h-px flex-1 bg-blue-night/10" aria-hidden="true"></span>
                            </div>
                        {/if}

                        <a
                            href={redirect}
                            class="mt-5 inline-flex text-xs font-bold uppercase text-blue-night/45 transition hover:text-orange-amber focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                        >
                            Voltar
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>
</Layout>
