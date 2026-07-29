<script>
    import { rememberOAuthAction } from "@/utils";

    export let oauth = {};
    export let title = "Entre para continuar";
    export let description = "Você precisa estar autenticado para acessar este conteúdo.";
    export let buttonLabel = "Entrar com Discord";
    export let color = "#ff8000";
    export let filters = "filter-blue-night";
    export let containerClass = "";
    export let titleClass = "text-blue-night";
    export let descriptionClass = "text-blue-night/60";
    export let buttonClass = "text-blue-night";
    export let action = null;
    export let compact = false;

    const authenticate = () => {
        rememberOAuthAction(action);
    };
</script>

{#if oauth.authenticated}
    <slot />
{:else}
    <div class={["flex flex-col items-center text-center font-noto-sans", compact ? "" : "py-7", containerClass]}>
        {#if !compact}
            <img
                src="/svg/discord.svg"
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
        <a
            href="/oauth/discord/redirect"
            class={[
                compact ? "" : "mt-5",
                "flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-extrabold uppercase italic transition duration-300 ease-out hover:-translate-y-0.5 focus-visible:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber active:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none",
                buttonClass,
            ]}
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
