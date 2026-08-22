<script>
    import { router } from "@inertiajs/svelte";
    import { onMount } from "svelte";
    import { fade } from "svelte/transition";

    let visible = false;
    let startedAt = 0;
    let hideTimeout;

    const isPublicPath = (path) => {
        return !path.startsWith("/panel") && path !== "/login";
    };

    const shouldShow = (event) => {
        const currentPath = window.location.pathname;
        const targetPath = event?.detail?.visit?.url?.pathname ?? currentPath;

        return isPublicPath(currentPath) && isPublicPath(targetPath);
    };

    const show = (event) => {
        if (!shouldShow(event)) return;

        clearTimeout(hideTimeout);
        startedAt = Date.now();
        visible = true;
    };

    const hide = () => {
        const elapsed = Date.now() - startedAt;
        const remaining = Math.max(220 - elapsed, 0);

        clearTimeout(hideTimeout);
        hideTimeout = window.setTimeout(() => {
            visible = false;
        }, remaining);
    };

    onMount(() => {
        const stopStart = router.on("start", show);
        const stopFinish = router.on("finish", hide);
        const stopCancel = router.on("cancel", hide);
        const stopError = router.on("error", hide);

        return () => {
            clearTimeout(hideTimeout);
            stopStart();
            stopFinish();
            stopCancel();
            stopError();
        };
    });
</script>

{#if visible}
    <div
        class="fixed inset-0 z-100 grid place-items-center bg-blue-night/75 backdrop-blur-sm"
        role="status"
        aria-live="polite"
        aria-label="Carregando página"
        transition:fade={{ duration: 140 }}
    >
        <div class="flex flex-col items-center">
            <div class="akiba-loader-logo relative size-24" aria-hidden="true">
                <span class="akiba-loader-wave akiba-loader-wave-one"></span>
                <span class="akiba-loader-wave akiba-loader-wave-two"></span>
                <svg
                    class="akiba-loader-mark relative size-full"
                    viewBox="0 0 38 38"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        class="akiba-loader-mark-path"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M0.0785868 37.7953H0V37.7167H0.0785868V37.7953ZM3.37378 37.7957V3.88036L7.25372 0H30.5342L34.4142 3.88036V27.1609L30.5342 31.0408H26.6543V20.7751L11.1341 24.6554V37.7957H3.37378ZM26.6543 13.5807V7.76072H11.1341V17.461L26.6543 13.581V13.5807ZM37.7097 0H37.7883V0.0786244H37.7097V0Z"
                    />
                </svg>
            </div>
        </div>
    </div>
{/if}

<style>
    .akiba-loader-logo {
        opacity: 0.82;
        filter: drop-shadow(0 1.25rem 2.5rem rgba(0, 0, 20, 0.42));
        animation: akiba-logo-lock 1.4s ease-in-out infinite;
    }

    .akiba-loader-wave {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 8rem;
        height: 8rem;
        border: 1px solid color-mix(in srgb, var(--color-blue-skywave) 24%, transparent);
        border-radius: 9999px;
        transform: translate(-50%, -50%) scale(0.5);
        opacity: 0;
        animation: akiba-loader-wave 1.4s ease-out infinite;
    }

    .akiba-loader-wave-two {
        animation-delay: 180ms;
        border-color: color-mix(in srgb, var(--color-orange-amber) 24%, transparent);
    }

    .akiba-loader-mark {
        overflow: visible;
    }

    .akiba-loader-mark-path {
        fill: color-mix(in srgb, var(--color-orange-citric) 0%, transparent);
        stroke: color-mix(in srgb, var(--color-orange-citric) 86%, transparent);
        stroke-width: 1.35;
        stroke-linejoin: round;
        stroke-linecap: round;
        stroke-dasharray: 170;
        stroke-dashoffset: 170;
        animation: akiba-mark-draw 1.4s ease-in-out infinite;
    }

    @keyframes akiba-logo-lock {
        0%,
        100% {
            transform: translateX(0);
        }

        10% {
            transform: translateX(-0.08rem);
        }

        16% {
            transform: translateX(0.08rem);
        }

        22% {
            transform: translateX(0);
        }
    }

    @keyframes akiba-mark-draw {
        0% {
            fill: color-mix(in srgb, var(--color-orange-citric) 0%, transparent);
            stroke-dashoffset: 170;
        }

        58% {
            fill: color-mix(in srgb, var(--color-orange-citric) 0%, transparent);
            stroke-dashoffset: 0;
        }

        76%,
        94% {
            fill: color-mix(in srgb, var(--color-orange-citric) 86%, transparent);
            stroke-dashoffset: 0;
        }

        100% {
            fill: color-mix(in srgb, var(--color-orange-citric) 0%, transparent);
            stroke-dashoffset: -170;
        }
    }

    @keyframes akiba-loader-wave {
        0% {
            transform: translate(-50%, -50%) scale(0.48);
            opacity: 0;
        }

        18% {
            opacity: 0.8;
        }

        100% {
            transform: translate(-50%, -50%) scale(1.3);
            opacity: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .akiba-loader-mark-path,
        .akiba-loader-logo,
        .akiba-loader-wave {
            animation: none;
        }

        .akiba-loader-mark-path {
            fill: var(--color-orange-citric);
            stroke-dashoffset: 0;
        }

        .akiba-loader-wave {
            display: none;
        }
    }
</style>
