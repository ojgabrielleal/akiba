<script>
    import { createEventDispatcher } from "svelte";
    import { fly } from "svelte/transition";

    export let open = false;
    export let notifications = [];
    let className = "";
    export { className as class };

    const dispatch = createEventDispatcher();

    const close = () => dispatch("close");
    const markAsRead = (notification) => dispatch("markRead", notification);
    const markAllAsRead = () => dispatch("markAllRead");

    const resolveRelativeTime = (date) => {
        if (!date) return "Agora";

        const elapsedSeconds = Math.max(0, Math.floor((Date.now() - new Date(date).getTime()) / 1000));
        const units = [
            { limit: 60, amount: 1, singular: "segundo", plural: "segundos" },
            { limit: 3600, amount: 60, singular: "minuto", plural: "minutos" },
            { limit: 86400, amount: 3600, singular: "hora", plural: "horas" },
            { limit: 2592000, amount: 86400, singular: "dia", plural: "dias" },
        ];
        const unit = units.find((item) => elapsedSeconds < item.limit) ?? units[units.length - 1];
        const value = Math.max(1, Math.floor(elapsedSeconds / unit.amount));

        return `${value} ${value === 1 ? unit.singular : unit.plural} atrás`;
    };

    const handleKeydown = (event) => {
        if (open && event.key === "Escape") close();
    };
</script>

<svelte:window on:keydown={handleKeydown} on:click={close} />

{#if open}
    <aside
        class={[
            "z-120 w-[min(25rem,calc(100vw-2rem))] overflow-hidden rounded-lg border border-suspense-aurora/10 bg-blue-night/95 font-noto-sans text-suspense-aurora shadow-2xl shadow-black/45 backdrop-blur-md",
            className,
        ]}
        aria-label="Notificações"
        transition:fly={{ y: -6, duration: 180 }}
        on:click|stopPropagation
    >
        <header class="flex min-h-13 items-center justify-between gap-3 border-b border-suspense-aurora/10 px-4">
            <h2 class="text-sm font-extrabold text-suspense-aurora">
                Notificações
            </h2>
            <button
                type="button"
                class="cursor-pointer text-xs font-extrabold text-orange-citric transition hover:text-orange-amber focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                on:click={markAllAsRead}
            >
                Marcar todas como lidas
            </button>
        </header>

        {#if notifications.length}
            <div class="max-h-96 overflow-y-auto">
                {#each notifications as notification}
                    <article
                        class="grid grid-cols-[minmax(0,1fr)_2rem] gap-3 border-b border-suspense-aurora/10 px-4 py-4 last:border-b-0 transition hover:bg-suspense-aurora/5"
                    >
                        <span class="min-w-0">
                            <a
                                href={notification.url}
                                class="block min-w-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                            >
                                <span class="block truncate text-sm font-extrabold text-suspense-aurora hover:text-orange-citric">
                                    {notification.title}
                                </span>
                                <span class="mt-1 line-clamp-2 block text-xs leading-snug text-suspense-aurora/70">
                                    {notification.body}
                                </span>
                            </a>
                            <span class="mt-2 block text-xs font-bold text-blue-skywave/80">
                                {resolveRelativeTime(notification.created_at)}
                            </span>
                        </span>
                        <button
                            type="button"
                            class="mt-1 flex size-8 cursor-pointer items-center justify-center rounded-md border border-suspense-aurora/10 text-orange-citric transition hover:border-orange-citric/60 hover:bg-orange-citric/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber"
                            aria-label="Marcar notificação como lida"
                            on:click={() => markAsRead(notification)}
                        >
                            <img
                                src="/svg/verify.svg"
                                alt=""
                                aria-hidden="true"
                                class="size-4 filter-orange-citric"
                            />
                        </button>
                    </article>
                {/each}
            </div>
        {:else}
            <p class="px-4 py-8 text-center text-sm leading-snug text-suspense-aurora/65">
                Nenhuma notificação por aqui 📭
            </p>
        {/if}
    </aside>
{/if}
