<script>
    export let title;

    import { onDestroy, onMount } from "svelte";
    import { page, router } from "@inertiajs/svelte";
    import { EmptyState, Section } from "@/ui/components/private";
    import { resolvePlaceholderImage } from "@/utils";

    const periods = [
        { value: "day", label: "Dia" },
        { value: "week", label: "Semana" },
        { value: "month", label: "Mês" },
        { value: "semester", label: "Semestre" },
    ];

    let canvas;
    let chart;
    let loading = false;

    const xAxisTickLimit = (width) => {
        if (width < 640) return 8;
        if (width < 1024) return 12;

        return history?.period === "day" ? 25 : 12;
    };

    const audienceLabelsPlugin = {
        id: "audienceLabels",
        afterDatasetsDraw(chart) {
            const { ctx } = chart;

            ctx.save();
            ctx.font = "700 10px Noto Sans, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "bottom";

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const metadata = chart.getDatasetMeta(datasetIndex);

                metadata.data.forEach((point, pointIndex) => {
                    const listeners = dataset.data[pointIndex];
                    if (listeners === null || listeners === undefined) return;

                    ctx.fillStyle = dataset.borderColor;
                    ctx.fillText(String(listeners), point.x, point.y - 5);
                });
            });

            ctx.restore();
        },
    };

    $: history = $page.props.audienceHistory?.data ?? null;
    $: if (chart && history) updateChart();

    const chartData = () => ({
        labels: history?.labels ?? [],
        datasets: (history?.series ?? []).map((series) => ({
            label: series.name,
            data: series.data,
            borderColor: series.color,
            backgroundColor: series.color,
            borderWidth: 3,
            pointRadius: 0,
            pointHitRadius: 8,
            pointHoverRadius: 4,
            pointBorderWidth: 0,
            tension: 0,
            spanGaps: true,
        })),
    });

    const updateChart = () => {
        chart.data = chartData();
        chart.options.scales.x.ticks.autoSkip = true;
        chart.options.scales.x.ticks.maxTicksLimit = xAxisTickLimit(chart.width);
        chart.update();
    };

    const selectPeriod = (period) => {
        if (loading || period === history?.period) return;

        router.get(
            window.location.pathname,
            { audience_period: period },
            {
                only: ["audienceHistory"],
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onStart: () => loading = true,
                onFinish: () => loading = false,
            },
        );
    };

    onMount(async () => {
        if (!canvas) return;

        const {
            CategoryScale,
            Chart,
            LinearScale,
            LineController,
            LineElement,
            PointElement,
            Tooltip,
        } = await import("chart.js");

        Chart.register(
            CategoryScale,
            LinearScale,
            LineController,
            LineElement,
            PointElement,
            Tooltip,
        );

        chart = new Chart(canvas, {
            type: "line",
            data: chartData(),
            plugins: [audienceLabelsPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                onResize: (chart, size) => {
                    chart.options.scales.x.ticks.maxTicksLimit = xAxisTickLimit(size.width);
                },
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "#000036",
                        borderColor: "#0091ff",
                        borderWidth: 1,
                        titleColor: "#ffb000",
                        bodyColor: "#ffffff",
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.parsed.y} ouvintes`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: "rgba(0, 145, 255, 0.18)" },
                        border: { color: "rgba(0, 145, 255, 0.4)" },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.55)",
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: "rgba(0, 145, 255, 0.18)" },
                        border: { color: "rgba(0, 145, 255, 0.4)" },
                        ticks: {
                            color: "rgba(255, 255, 255, 0.55)",
                            precision: 0,
                        },
                    },
                },
            },
        });
    });

    onDestroy(() => chart?.destroy());
</script>

<Section {title}>
    {#if history?.series?.length}
        <div class="grid min-h-90 min-w-0 w-full grid-cols-1 gap-3 md:grid-cols-[8rem_minmax(0,1fr)_5rem]">
            <div class="flex min-w-0 gap-2 overflow-x-auto md:flex-col md:overflow-visible" aria-label="Legenda das rádios">
                {#each history.series as series (series.uuid)}
                    <div
                        class="flex h-12 min-w-28 items-center justify-end overflow-hidden rounded-sm bg-blue-ocean"
                        title={series.name}
                    >
                        <span class="sr-only">{series.name}</span>
                        <span class="flex min-w-0 flex-1 items-center justify-center px-2">
                            <img
                                src={resolvePlaceholderImage(series.logo, "placeholder")}
                                alt={`Logo da ${series.name}`}
                                loading="lazy"
                                class="max-h-8 max-w-full object-contain"
                            />
                        </span>
                        <span class="h-full w-3" style={`background-color: ${series.color}`}></span>
                    </div>
                {/each}
            </div>

            <div class="relative min-h-80 min-w-0 overflow-hidden rounded-lg border border-blue-skywave/50 bg-blue-ocean/45 p-3">
                <canvas class="block max-w-full" bind:this={canvas} aria-label="Gráfico do histórico de audiência"></canvas>
                {#if loading}
                    <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-blue-marinho/65 text-sm font-extrabold uppercase italic text-orange-citric">
                        Atualizando…
                    </div>
                {/if}
            </div>

            <div class="grid min-w-0 grid-cols-2 gap-1 sm:grid-cols-4 md:flex md:flex-col" aria-label="Período do histórico">
                {#each periods as period}
                    <button
                        type="button"
                        disabled={loading}
                        aria-pressed={history.period === period.value}
                        class={[
                            "h-8 min-w-0 cursor-pointer rounded-md px-3 font-noto-sans text-xs font-black uppercase italic transition disabled:cursor-wait disabled:opacity-60 md:w-full",
                            history.period === period.value
                                ? "bg-orange-citric text-blue-marinho"
                                : "bg-blue-cerulean text-blue-marinho hover:bg-blue-skywave",
                        ]}
                        on:click={() => selectPeriod(period.value)}
                    >
                        {period.label}
                    </button>
                {/each}
            </div>
        </div>
    {:else}
        <EmptyState
            title="Histórico ainda indisponível"
            description="O gráfico aparecerá após as primeiras coletas de audiência."
        />
    {/if}
</Section>
