<script>
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let accounts = [];
    export let emptyMessage = "Nenhuma conta agora.";

    const accountName = (account) => account.nickname ?? account.username ?? "Conta online";
    const seenAt = (account) => {
        if (!account.last_seen_at) {
            return "agora";
        }

        return new Intl.DateTimeFormat("pt-BR", {
            hour: "2-digit",
            minute: "2-digit",
        }).format(new Date(account.last_seen_at));
    };

    const seenAtLabel = (account) => {
        const value = seenAt(account);

        return value === "agora" ? "Apareceu agora" : `Apareceu às ${value}`;
    };
</script>

<div class="flex flex-col gap-3">
    {#each accounts as account (account.id)}
        <div class="flex items-center gap-3 rounded-md border border-neutral-gray/15 bg-white px-3 py-3">
            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-neutral-gray/10">
                <img
                    src={resolvePlaceholderImage(account.avatar, "avatar")}
                    alt={accountName(account)}
                    class="h-full w-full object-cover object-top"
                    loading="lazy"
                />
            </div>
            <div class="min-w-0">
                <p class="truncate font-noto-sans text-sm font-black italic uppercase text-neutral-gray">
                    {accountName(account)}
                </p>
                <p class="font-noto-sans text-xs font-bold text-neutral-gray/50">
                    {seenAtLabel(account)}
                </p>
            </div>
        </div>
    {:else}
        <div class="rounded-md border border-neutral-gray/15 bg-white px-4 py-6 text-center font-noto-sans text-sm font-bold text-neutral-gray/60">
            {emptyMessage}
        </div>
    {/each}
</div>
