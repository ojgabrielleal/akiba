<script>
    import { rememberOAuthAction } from "@/utils";

    export let oauth = {};
    export let title = "Entre para continuar";
    export let description = "Você precisa estar autenticado para acessar este conteúdo.";
    export let buttonLabel = "Entrar com Discord";
    export let color = "#ff8000";
    export let filters = "filter-blue-night";
    export let action = null;

    const authenticate = () => {
        rememberOAuthAction(action);
    };
</script>

{#if oauth.authenticated}
    <slot />
{:else}
    <div class="flex flex-col items-center py-7 text-center font-noto-sans">
        <img
            src="/svg/discord.svg"
            alt=""
            aria-hidden="true"
            class={["mb-3 h-8 w-8", filters]}
        />
        <p class="text-base font-extrabold uppercase italic text-blue-night">
            {title}
        </p>
        <p class="mt-1 text-sm text-blue-night/60">
            {description}
        </p>
        <a
            href="/oauth/discord/redirect"
            class="mt-5 flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-extrabold uppercase italic text-blue-night"
            style:background-color={color}
            on:click={authenticate}
        >
            <img
                src="/svg/discord.svg"
                alt=""
                aria-hidden="true"
                class={["h-4 w-4", filters]}
            />
            {buttonLabel}
        </a>
    </div>
{/if}
