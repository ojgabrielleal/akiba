<script>
    import { OAuthAction, dispatchOAuthAction } from "@/lib/utils";

    export let oauth = {};

    $: profile = oauth?.profile ?? {};
    $: avatar = profile?.avatar || "/img/placeholders/avatar.webp";
    $: nickname = profile?.nickname || profile?.username || "Perfil Akiba";
</script>

<aside class="fixed right-4 bottom-[6rem] left-4 z-[70] font-noto-sans text-suspense-aurora" role="status" aria-label="Perfil incompleto">
    <div class="overflow-hidden rounded-md border border-orange-citric/35 bg-blue-ocean/95 shadow-2xl shadow-blue-night/45 backdrop-blur-md">
        <div class="h-1 bg-orange-citric"></div>
        <div class="flex flex-col gap-4 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-orange-citric">
                    <img src="/svg/profile.svg" alt="" aria-hidden="true" class="size-5 filter-blue-marinho" />
                </div>
                <div class="min-w-0 uppercase italic">
                    <p class="text-sm font-black leading-tight text-orange-morning">
                        Perfil incompleto
                    </p>
                    <p class="mt-0.5 text-xs font-bold leading-snug text-suspense-aurora/72 sm:text-sm">
                        Complete seus dados para comentar, pedir músicas e participar da comunidade.
                    </p>
                </div>
            </div>
            <button
                type="button"
                class="inline-flex min-h-10 shrink-0 cursor-pointer items-center justify-center rounded-full bg-orange-citric px-5 py-2 font-noto-sans text-xs font-black uppercase italic text-blue-night transition duration-300 ease-out hover:-translate-y-0.5 hover:brightness-105 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none"
                on:click={() => dispatchOAuthAction(OAuthAction.OPEN_PROFILE)}
            >
                Completar perfil
            </button>
        </div>
    </div>
</aside>
