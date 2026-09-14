<script>
    import { page, router } from "@inertiajs/svelte";
    import { fade } from "svelte/transition";
    import { Meta } from "@/lib/components/shared";
    import { Layout } from "@/lib/layouts/private";
    import { LocutionForm, SongRequestGrid } from "@/lib/widgets/private";
    import { resolvePlaceholderImage } from "@/lib/utils";

    $: ({ user, programs, onair, songRequests } = $page.props);
    $: activeOnair = onair?.data ?? null;
    $: userPermissions = Array.isArray(user?.permissions) ? user.permissions : Object.values(user?.permissions ?? {});
    $: canFinishAnyProgram = userPermissions.includes("locution.finish.any");
    $: isLocutionFormBlocked = ["live", "scheduled"].includes(activeOnair?.execution_mode);

    const redirectToDashboard = () => {
        router.get("/panel/dashboard/", {}, { preserveScroll: true });
    };

    const finishActiveProgram = () => {
        router.patch("/panel/locution/finish", {}, { preserveScroll: true });
    };
</script>

<Meta meta={{ title: "Locução" }} />
<Layout>
    <h1 class="sr-only">Locucao</h1>
    <div 
        class:opacity-50={isLocutionFormBlocked} 
        class:pointer-events-none={isLocutionFormBlocked} 
    >
        <LocutionForm {programs} />
    </div>
    {#if activeOnair?.execution_mode === "live"}
        <SongRequestGrid title="Pedidos musicais" {onair} {songRequests} />
    {/if}
</Layout>

{#if activeOnair?.execution_mode === "live" && activeOnair.program.host.uuid !== user.uuid}
    <div transition:fade={{ duration: 300 }} class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-blue-marinho/85 px-3 py-4 font-noto-sans backdrop-blur-md sm:px-5" role="dialog" aria-modal="true" aria-labelledby="live-lock-title" tabindex="-1">
        <div class="relative w-full max-w-lg overflow-hidden rounded-md border border-blue-skywave/20 bg-blue-ocean shadow-[0_1.25rem_4rem_rgba(0,0,0,0.35)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-orange-citric" aria-hidden="true"></div>
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-blue-skywave/10" aria-hidden="true"></div>
            <div class="grid gap-4 p-4 sm:grid-cols-[5.75rem_minmax(0,1fr)] sm:p-5">
                <div class="relative">
                    <div class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-blue-skywave/20 sm:left-auto sm:right-0 sm:translate-x-0" aria-hidden="true"></div>
                    <div class="relative z-10 mx-auto w-fit">
                        <div class="size-19 overflow-hidden rounded-md border-2 border-orange-citric bg-blue-marinho shadow-[0_0_0_0.25rem_rgba(0,0,20,0.16)] sm:size-20">
                            <img
                                src={resolvePlaceholderImage(activeOnair.program.host.avatar, "avatar", activeOnair.program.host.gender)}
                                alt=""
                                class="h-full w-full object-cover object-top"
                            />
                        </div>
                        <span class="absolute -bottom-2 left-1/2 min-w-16 -translate-x-1/2 rounded-full bg-red-crimson px-2 py-0.5 text-center text-[0.6rem] font-extrabold uppercase italic whitespace-nowrap text-suspense-aurora">
                            Ao vivo
                        </span>
                    </div>
                </div>

                <div class="relative z-10 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-6 items-center rounded-full bg-blue-marinho/55 px-3 text-[0.62rem] font-extrabold uppercase italic text-orange-citric">
                            Programa em andamento
                        </span>
                        <span class="inline-flex h-6 items-center rounded-full border border-blue-skywave/25 px-3 text-[0.62rem] font-extrabold uppercase italic text-blue-skywave">
                            Estúdio ocupado
                        </span>
                    </div>

                    <div class="mt-2">
                        <div class="min-w-0">
                            <h2 id="live-lock-title" class="text-xl font-extrabold uppercase italic leading-tight text-suspense-aurora sm:text-2xl">
                                {activeOnair.program.name}
                            </h2>
                            <p class="mt-1 truncate text-sm font-bold text-blue-skywave">
                                DJ {activeOnair.program.host.nickname}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-md border border-blue-skywave/15 bg-blue-marinho/35 px-4 py-3">
                        <p class="text-sm leading-relaxed text-suspense-aurora/82">
                            Não é possível começar um novo programa enquanto
                            {activeOnair.program.host.gender === "male" ? "o" : "a"} DJ
                            <span class="font-extrabold text-suspense-aurora">
                                {activeOnair.program.host.nickname}
                            </span>
                            está ao vivo. Aguarde {activeOnair.program.host.gender === "male" ? "ele" : "ela"} encerrar o programa.
                        </p>
                    </div>

                    <div class="mt-3 grid grid-cols-[auto_minmax(0,1fr)] items-center gap-3 rounded-md bg-blue-marinho/45 px-3 py-2.5">
                        <span class="flex size-7 items-center justify-center rounded-full bg-orange-citric text-blue-marinho">
                            <img src="/svg/alerts.svg" alt="" class="w-4 filter-blue-marinho" />
                        </span>
                        <p class="text-[0.68rem] font-extrabold uppercase italic leading-snug text-suspense-aurora/65">
                            Disponível após o término do programa atual.
                        </p>
                    </div>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        {#if canFinishAnyProgram}
                            <button
                                type="button"
                                class="flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full bg-orange-citric px-4 py-2 font-noto-sans text-[0.68rem] font-extrabold uppercase italic whitespace-nowrap text-blue-marinho transition duration-200 ease-out hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                                on:click={finishActiveProgram}
                            >
                                <img
                                    src="/svg/bloqued.svg"
                                    class="w-5 filter-blue-marinho"
                                    alt=""
                                />
                                Encerrar programa
                            </button>
                        {/if}
                        <button
                            type="button"
                            class="group/return flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-orange-citric bg-transparent px-4 py-2 font-noto-sans font-extrabold uppercase italic text-[0.68rem] whitespace-nowrap text-orange-citric transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-orange-citric hover:text-blue-marinho focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                            on:click={() => redirectToDashboard()}
                        >
                            <img
                                src="/svg/return.svg"
                                class="w-5 filter-orange-citric group-hover/return:filter-blue-marinho"
                                alt=""
                            />
                            Voltar ao dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

{#if activeOnair?.execution_mode === "scheduled"}
    <div transition:fade={{ duration: 300 }} class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-blue-marinho/85 px-3 py-4 font-noto-sans backdrop-blur-md sm:px-5" role="dialog" aria-modal="true" aria-labelledby="scheduled-lock-title" tabindex="-1">
        <div class="relative w-full max-w-lg overflow-hidden rounded-md border border-blue-skywave/20 bg-blue-ocean shadow-[0_1.25rem_4rem_rgba(0,0,0,0.35)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-orange-citric" aria-hidden="true"></div>
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-blue-skywave/10" aria-hidden="true"></div>
            <div class="grid gap-4 p-4 sm:grid-cols-[5.75rem_minmax(0,1fr)] sm:p-5">
                <div class="relative">
                    <div class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-blue-skywave/20 sm:left-auto sm:right-0 sm:translate-x-0" aria-hidden="true"></div>
                    <div class="relative z-10 mx-auto w-fit">
                        <div class="flex size-19 items-center justify-center overflow-hidden rounded-md border-2 border-orange-citric bg-blue-marinho shadow-[0_0_0_0.25rem_rgba(0,0,20,0.16)] sm:size-20">
                            <img
                                src="/svg/calendar.svg"
                                alt=""
                                class="w-8 filter-suspense-aurora"
                            />
                        </div>
                        <span class="absolute -bottom-2 left-1/2 min-w-17 -translate-x-1/2 rounded-full bg-orange-citric px-2 py-0.5 text-center text-[0.6rem] font-extrabold uppercase italic whitespace-nowrap text-blue-marinho">
                            Agendado
                        </span>
                    </div>
                </div>

                <div class="relative z-10 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-6 items-center rounded-full bg-blue-marinho/55 px-3 text-[0.62rem] font-extrabold uppercase italic text-orange-citric">
                            Programa agendado
                        </span>
                        <span class="inline-flex h-6 items-center rounded-full border border-blue-skywave/25 px-3 text-[0.62rem] font-extrabold uppercase italic text-blue-skywave">
                            Estúdio ocupado
                        </span>
                    </div>

                    <div class="mt-2">
                        <h2 id="scheduled-lock-title" class="text-xl font-extrabold uppercase italic leading-tight text-suspense-aurora sm:text-2xl">
                            {activeOnair.program.name}
                        </h2>
                        <p class="mt-1 truncate text-sm font-bold text-blue-skywave">
                            Agenda da programação
                        </p>
                    </div>

                    <div class="mt-4 rounded-md border border-blue-skywave/15 bg-blue-marinho/35 px-4 py-3">
                        <p class="text-sm leading-relaxed text-suspense-aurora/82">
                            Não é possível iniciar um novo programa ao vivo enquanto o programa
                            <span class="font-extrabold text-suspense-aurora">
                                {activeOnair.program.name}
                            </span>
                            está em andamento pela agenda.
                        </p>
                    </div>

                    <div class="mt-3 grid grid-cols-[auto_minmax(0,1fr)] items-center gap-3 rounded-md bg-blue-marinho/45 px-3 py-2.5">
                        <span class="flex size-7 items-center justify-center rounded-full bg-orange-citric text-blue-marinho">
                            <img src="/svg/alerts.svg" alt="" class="w-4 filter-blue-marinho" />
                        </span>
                        <p class="text-[0.68rem] font-extrabold uppercase italic leading-snug text-suspense-aurora/65">
                            Disponível após o término do horário.
                        </p>
                    </div>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        {#if canFinishAnyProgram}
                            <button
                                type="button"
                                class="flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full bg-orange-citric px-4 py-2 font-noto-sans text-[0.68rem] font-extrabold uppercase italic whitespace-nowrap text-blue-marinho transition duration-200 ease-out hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                                on:click={finishActiveProgram}
                            >
                                <img
                                    src="/svg/bloqued.svg"
                                    class="w-5 filter-blue-marinho"
                                    alt=""
                                />
                                Encerrar programa
                            </button>
                        {/if}
                        <button
                            type="button"
                            class="group/return-scheduled flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-orange-citric bg-transparent px-4 py-2 font-noto-sans font-extrabold uppercase italic text-[0.68rem] whitespace-nowrap text-orange-citric transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-orange-citric hover:text-blue-marinho focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                            on:click={() => redirectToDashboard()}
                        >
                            <img
                                src="/svg/return.svg"
                                class="w-5 filter-orange-citric group-hover/return-scheduled:filter-blue-marinho"
                                alt=""
                            />
                            Voltar ao dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
