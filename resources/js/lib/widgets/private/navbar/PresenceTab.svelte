<script>
    import { Offcanvas } from "@/lib/components/private";
    import { privatePresence } from "@/lib/stores/privatePresence";
    import { resolvePlaceholderImage } from "@/lib/utils";

    let offcanvasRef;

    $: users = $privatePresence.users;
    $: isConnected = $privatePresence.status === "connected";
    $: hasError = $privatePresence.status === "error";
    $: onlineLabel = `${users.length} online`;
</script>

<button
    type="button"
    class={[
        "flex cursor-pointer items-center gap-3 rounded-md border px-3 py-2 font-noto-sans transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber",
        hasError ? "border-red-crimson/40 bg-red-crimson/10 text-red-crimson" : "border-green-500/30 bg-green-500/10 text-green-500",
    ]}
    aria-label="Abrir presença do painel"
    on:click={() => offcanvasRef.open()}
>
    <img
        src="/svg/team.svg"
        alt=""
        aria-hidden="true"
        class={["h-5 w-5", hasError ? "filter-red-crimson" : "filter-blue-skywave"]}
    />
    <span class="leading-none">
        <span class="block text-[0.65rem] font-black uppercase tracking-wide">
            Agora no painel
        </span>
        <span class="block text-xs font-bold">
            {hasError ? "indisponível" : onlineLabel}
        </span>
    </span>
</button>

<Offcanvas bind:this={offcanvasRef}>
    <div slot="content">
        <div class="mb-5">
            <p class="font-noto-sans text-xs font-black uppercase text-neutral-gray/50">
                Agora no painel
            </p>
            <p class="font-noto-sans text-2xl font-black italic uppercase text-blue-night">
                {onlineLabel}
            </p>
        </div>

        {#if hasError}
            <div class="rounded-md border border-red-crimson/20 bg-red-crimson/10 px-4 py-4 font-noto-sans text-sm font-bold text-red-crimson">
                Não foi possível entrar no canal de presença.
            </div>
        {:else if !isConnected}
            <div class="rounded-md border border-neutral-gray/15 bg-white px-4 py-6 text-center font-noto-sans text-sm font-bold text-neutral-gray/60">
                Conectando presença...
            </div>
        {:else}
            <div class="flex flex-col gap-3">
            {#each users as user (user.id)}
                <div class="flex items-center gap-3 rounded-md border border-neutral-gray/15 bg-white px-3 py-3">
                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-neutral-gray/10">
                        <img
                            src={resolvePlaceholderImage(user.avatar, "avatar", user.gender)}
                            alt={user.nickname ?? user.name}
                            class="h-full w-full object-cover object-top scale-200"
                            loading="lazy"
                        />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-noto-sans text-sm font-black italic uppercase text-neutral-gray">
                            {user.nickname ?? user.name}
                        </p>
                        <p class="flex items-center gap-1 font-noto-sans text-xs font-bold text-green-500">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            Online
                        </p>
                    </div>
                </div>
            {:else}
                <div class="rounded-md border border-neutral-gray/15 bg-white px-4 py-6 text-center font-noto-sans text-sm font-bold text-neutral-gray/60">
                    Ninguém online agora.
                </div>
            {/each}
            </div>
        {/if}
    </div>
</Offcanvas>
