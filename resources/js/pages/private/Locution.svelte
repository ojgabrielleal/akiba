<script>
    import { page, router } from "@inertiajs/svelte";
    import { fade } from "svelte/transition";
    import { Meta } from "@/config";
    import { Layout } from "@/ui/layouts/private";
    import { LocutionForm, SongRequestGrid } from "@/ui/widgets/private";

    $: ({ user, onair } = $page.props);

    $: isLocutionFormBlocked = ["live", "playlist", "scheduled"].includes(onair.data.execution_mode);

    const redirectToDashboard = () => {
        router.get("/panel/dashboard/", {}, { preserveScroll: true });
    };
</script>

<Meta meta={{ title: "Locução" }} />
<Layout>
    <div 
        class:opacity-50={isLocutionFormBlocked} 
        class:pointer-events-none={isLocutionFormBlocked} 
    >
        <LocutionForm />
    </div>
    {#if onair.data.execution_mode === "live"}
        <SongRequestGrid title="Pedidos musicais" />
    {/if}
</Layout>

{#if onair.data.execution_mode === "live" && onair.data.program.host.uuid !== user.uuid}
    <div transition:fade={{ duration: 300 }} class="fixed inset-0 z-50 flex items-center justify-center bg-blue-marinho/85 px-5 font-noto-sans backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="live-lock-title" tabindex="-1">
        <div class="w-full max-w-md overflow-hidden rounded-md bg-blue-ocean shadow-[0_1.25rem_4rem_rgba(0,0,0,0.35)]">
            <div class="flex items-center gap-4 px-5 py-5">
                <div class="relative shrink-0">
                    <div class="h-20 w-20 overflow-hidden rounded-md bg-blue-marinho">
                        <img
                            src={onair.data.program.host.avatar}
                            alt=""
                            class="h-full w-full object-cover object-top"
                        />
                    </div>
                    <span class="absolute -bottom-2 left-1/2 min-w-16 -translate-x-1/2 rounded-md bg-red-crimson px-2 py-0.5 text-center text-[0.65rem] font-extrabold uppercase italic whitespace-nowrap text-suspense-aurora">
                        Ao vivo
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase italic text-orange-amber">
                        Programa em andamento
                    </p>
                    <h2 id="live-lock-title" class="mt-1 text-xl font-extrabold uppercase italic leading-tight text-suspense-aurora">
                        {onair.data.program.name}
                    </h2>
                    <p class="mt-1 truncate text-sm font-semibold text-blue-skywave">
                        DJ {onair.data.program.host.nickname}
                    </p>
                </div>
            </div>
            <div class="px-5 pt-2 pb-5">
                <p class="text-sm leading-relaxed text-suspense-aurora/80">
                    Não é possível começar um novo programa enquanto
                    {onair.data.program.host.gender === "male" ? "o" : "a"} DJ
                    <span class="font-extrabold text-suspense-aurora">
                        {onair.data.program.host.nickname}
                    </span>
                    está ao vivo. Aguarde {onair.data.program.host.gender === "male" ? "ele" : "ela"} encerrar o programa.
                </p>
                <div class="mt-5 rounded-md bg-blue-marinho/35 px-4 py-3 text-center text-xs font-semibold uppercase italic text-suspense-aurora/60">
                    💡 Disponível após o término do programa atual.
                </div>
                <div class="mt-5 flex justify-start">
                    <button
                        type="button"
                        class="flex cursor-pointer items-center justify-center gap-1 rounded-full bg-orange-citric px-2 py-2 font-noto-sans font-extrabold uppercase italic text-sm text-blue-marinho"
                        on:click={() => redirectToDashboard()}
                    >
                        <img
                            src="/svg/return.svg"
                            class="w-4 filter-blue-marinho"
                            alt=""
                        />
                        Voltar ao dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

{#if onair.data.execution_mode === "scheduled"}
    <div transition:fade={{ duration: 300 }} class="fixed inset-0 z-50 flex items-center justify-center bg-blue-marinho/85 px-5 font-noto-sans backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="scheduled-lock-title" tabindex="-1">
        <div class="w-full max-w-md overflow-hidden rounded-md bg-blue-ocean shadow-[0_1.25rem_4rem_rgba(0,0,0,0.35)]">
            <div class="flex items-center gap-4 px-5 py-5">
                <div class="relative shrink-0">
                    <div class="flex h-20 w-20 items-center justify-center rounded-md bg-blue-marinho">
                        <img
                            src="/svg/calendar.svg"
                            alt=""
                            class="w-9 filter-suspense-aurora"
                        />
                    </div>
                    <span class="absolute -bottom-2 left-1/2 min-w-20 -translate-x-1/2 rounded-md bg-orange-amber px-2 py-0.5 text-center text-[0.65rem] font-extrabold uppercase italic whitespace-nowrap text-blue-marinho">
                        Agendado
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase italic text-orange-amber">
                        Programa agendado
                    </p>
                    <h2 id="scheduled-lock-title" class="mt-1 text-xl font-extrabold uppercase italic leading-tight text-suspense-aurora">
                        {onair.data.program.name}
                    </h2>
                    <p class="mt-1 truncate text-sm font-semibold text-blue-skywave">
                        Agenda da programação
                    </p>
                </div>
            </div>
            <div class="px-5 pt-2 pb-5">
                <p class="text-sm leading-relaxed text-suspense-aurora/80">
                    Não é possível iniciar um novo programa ao vivo enquanto o programa 
                    <span class="font-extrabold text-suspense-aurora">
                        {onair.data.program.name}
                    </span>
                    está em andamento pela agenda. Aguarde o encerramento do programa agendado.
                </p>
                <div class="mt-5 rounded-md bg-blue-marinho/35 px-4 py-3 text-center text-xs font-semibold uppercase italic text-suspense-aurora/60">
                    💡 Disponível após o término do programa agendado.
                </div>
                <div class="mt-5 flex justify-start">
                    <button
                        type="button"
                        class="flex cursor-pointer items-center justify-center gap-1 rounded-full bg-orange-citric px-2 py-2 font-noto-sans font-extrabold uppercase italic text-sm text-blue-marinho"
                        on:click={() => redirectToDashboard()}
                    >
                        <img
                            src="/svg/return.svg"
                            class="w-4 filter-blue-marinho"
                            alt=""
                        />
                        Voltar ao dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
