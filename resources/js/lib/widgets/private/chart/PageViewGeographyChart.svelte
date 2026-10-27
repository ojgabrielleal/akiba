<script>
    import { onDestroy } from "svelte";
    import { router } from "@inertiajs/svelte";
    import isoCountries from "i18n-iso-countries";
    import pt from "i18n-iso-countries/langs/pt.json";

    import { EmptyState, Section } from "@/lib/components/private";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let geography = null;

    isoCountries.registerLocale(pt);

    const number = new Intl.NumberFormat("pt-BR");
    const percent = new Intl.NumberFormat("pt-BR", { style: "percent", maximumFractionDigits: 1 });
    const periods = [
        { value: "today", label: "Hoje" },
        { value: "30", label: "30 dias" },
        { value: "90", label: "90 dias" },
        { value: "365", label: "1 ano" },
        { value: "all", label: "Todo o histórico" },
    ];
    let canvas;
    let chart;
    let features = [];
    let activeRanking = "countries";
    let loading = false;
    let chartInitializing = false;

    $: countries = Array.isArray(geography?.countries) ? geography.countries : [];
    $: cities = Array.isArray(geography?.cities) ? geography.cities : [];
    $: viewers = Array.isArray(geography?.viewers) ? geography.viewers : [];
    $: sources = Array.isArray(geography?.sources) ? geography.sources : [];
    $: locatedViews = Number(geography?.located_views ?? 0);
    $: totalViews = Number(geography?.total_views ?? 0);
    $: coverage = totalViews > 0 ? locatedViews / totalViews : 0;
    $: selectedPeriod = geography?.period ? String(geography.period) : "today";
    $: ranking = activeRanking === "countries"
        ? countries.slice(0, 12).map((country) => ({
            key: country.code,
            label: countryName(country.code),
            detail: country.code,
            views: Number(country.views ?? 0),
        }))
        : activeRanking === "cities" ? cities.slice(0, 12).map((city) => ({
            key: `${city.country_code}-${city.region_code}-${city.city}`,
            label: city.city,
            detail: [city.region_code, countryName(city.country_code)].filter(Boolean).join(" · "),
            views: Number(city.views ?? 0),
        })) : viewers.slice(0, 12).map((viewer) => ({
            key: viewer.key,
            label: viewer.name,
            detail: viewer.last_view ? `Último acesso: ${viewer.last_view}` : "Sem data de acesso",
            location: [viewer.city, viewer.region_code, countryName(viewer.country_code)].filter(Boolean).join(" · "),
            avatar: viewer.avatar,
            views: Number(viewer.views ?? 0),
        }));
    $: rankingMaximum = Math.max(...ranking.map((item) => item.views), 1);
    $: sourceMaximum = Math.max(...sources.map((item) => Number(item.views ?? 0)), 1);
    $: if (chart && features.length) updateChart(countries);
    $: if (canvas && !chart && !chartInitializing) initializeChart();
    $: if (!canvas && chart) {
        chart.destroy();
        chart = null;
    }

    function countryName(code) {
        return isoCountries.getName(code, "pt", { select: "official" }) ?? code ?? "Desconhecido";
    }

    function countryCodeFromFeature(feature) {
        return isoCountries.numericToAlpha2(String(feature.id).padStart(3, "0"));
    }

    function chartData(currentCountries = countries) {
        const values = new Map(currentCountries.map((country) => [country.code, Number(country.views ?? 0)]));

        return {
            labels: features.map((feature) => countryName(countryCodeFromFeature(feature))),
            datasets: [{
                label: "Acessos",
                outline: features,
                data: features.map((feature) => ({
                    feature,
                    value: values.get(countryCodeFromFeature(feature)) ?? 0,
                })),
                borderColor: "rgba(0, 145, 255, 0.38)",
                borderWidth: 0.6,
            }],
        };
    }

    function updateChart(currentCountries) {
        chart.data = chartData(currentCountries);
        chart.update();
    }

    function applyFilter(event) {
        const { name, value } = event.currentTarget;
        const params = new URLSearchParams(window.location.search);

        value === "all" && name !== "geography_period"
            ? params.delete(name)
            : params.set(name, value);

        router.get(
            `${window.location.pathname}?${params.toString()}`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                only: ["geography"],
                onStart: () => loading = true,
                onFinish: () => loading = false,
            },
        );
    }

    async function initializeChart() {
        if (!canvas) return;

        chartInitializing = true;

        try {
            const [chartJs, chartGeo, topojson, atlasModule] = await Promise.all([
                import("chart.js"),
                import("chartjs-chart-geo"),
                import("topojson-client"),
                import("world-atlas/countries-110m.json"),
            ]);
            const atlas = atlasModule.default ?? atlasModule;

            features = topojson.feature(atlas, atlas.objects.countries).features;

            chartJs.Chart.register(
                chartGeo.ChoroplethController,
                chartGeo.ColorScale,
                chartGeo.GeoFeature,
                chartGeo.ProjectionScale,
                chartJs.Tooltip,
            );

            if (!canvas) return;

            chart = new chartJs.Chart(canvas, {
            type: "choropleth",
            data: chartData(),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                showOutline: true,
                showGraticule: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "#000036",
                        borderColor: "#0091ff",
                        borderWidth: 1,
                        titleColor: "#ffb000",
                        bodyColor: "#ffffff",
                        callbacks: {
                            title: (items) => {
                                const feature = items[0]?.raw?.feature;

                                return feature
                                    ? countryName(countryCodeFromFeature(feature))
                                    : "País desconhecido";
                            },
                            label: (context) => `${number.format(context.raw?.value ?? 0)} acessos`,
                        },
                    },
                },
                scales: {
                    projection: {
                        axis: "x",
                        projection: "equalEarth",
                    },
                    color: {
                        axis: "x",
                        interpolate: (value) => {
                            const amount = Math.max(0, Math.min(1, Number(value) || 0));
                            const red = 255;
                            const green = Math.round(224 - (48 * amount));
                            const blue = Math.round(163 - (163 * amount));

                            return `rgb(${red}, ${green}, ${blue})`;
                        },
                        legend: { display: false },
                    },
                },
            },
            });
        } finally {
            chartInitializing = false;
        }
    }

    onDestroy(() => chart?.destroy());
</script>

<Section title="Análises gerais do site" aria-label="Análises gerais do site">
    <div class="mb-4 max-w-xs">
        <label class="w-full text-xs font-extrabold uppercase italic text-suspense-aurora">
            Período
            <select
                name="geography_period"
                value={selectedPeriod}
                on:change={applyFilter}
                disabled={loading}
                class="mt-2 block h-10 w-full cursor-pointer rounded-md border border-blue-skywave bg-blue-ocean px-3 text-sm font-normal normal-case not-italic text-suspense-aurora outline-none disabled:cursor-wait disabled:opacity-60"
            >
                {#each periods as period}
                    <option value={period.value}>{period.label}</option>
                {/each}
            </select>
        </label>
    </div>

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-md border border-blue-skywave/35 bg-blue-ocean/55 p-4">
            <span class="text-xs font-extrabold uppercase italic text-suspense-aurora/60">Acessos no período</span>
            <strong class="mt-1 block text-2xl font-black tabular-nums text-orange-amber">{number.format(totalViews)}</strong>
        </div>
        <div class="rounded-md border border-blue-skywave/35 bg-blue-ocean/55 p-4">
            <span class="text-xs font-extrabold uppercase italic text-suspense-aurora/60">Com localização</span>
            <strong class="mt-1 block text-2xl font-black tabular-nums text-orange-amber">{number.format(locatedViews)}</strong>
        </div>
        <div class="rounded-md border border-blue-skywave/35 bg-blue-ocean/55 p-4">
            <span class="text-xs font-extrabold uppercase italic text-suspense-aurora/60">Cobertura</span>
            <strong class="mt-1 block text-2xl font-black tabular-nums text-orange-amber">{percent.format(coverage)}</strong>
        </div>
    </div>

    {#if countries.length || viewers.length}
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.65fr)_minmax(18rem,0.85fr)]">
            {#if countries.length}
                <div class="relative h-80 min-w-0 overflow-hidden rounded-lg border border-blue-skywave/40 bg-blue-ocean/35 p-2 sm:h-[28rem] sm:p-4">
                    <canvas bind:this={canvas} aria-label="Mapa-múndi de acessos por país"></canvas>
                    {#if loading}
                        <div class="absolute inset-0 flex items-center justify-center bg-blue-marinho/70 text-sm font-extrabold uppercase italic text-orange-morning">
                            Atualizando…
                        </div>
                    {/if}
                </div>
            {:else}
                <div class="flex h-80 min-w-0 items-center rounded-lg border border-blue-skywave/40 bg-blue-ocean/35 sm:h-[28rem]">
                    <EmptyState title="Mapa ainda indisponível" description="Esses acessos não possuem localização identificada." />
                </div>
            {/if}

            <div class="flex h-80 min-w-0 flex-col rounded-lg border border-blue-skywave/40 bg-blue-ocean/35 p-4 sm:h-[28rem]">
                <div class={`mb-4 grid shrink-0 gap-2 ${viewers.length ? "grid-cols-3" : "grid-cols-2"}`} role="tablist" aria-label="Ranking geográfico">
                    {#each [
                        { value: "countries", label: "Países" },
                        { value: "cities", label: "Cidades" },
                        ...(viewers.length ? [{ value: "viewers", label: "Com conta" }] : []),
                    ] as tab}
                        <button
                            type="button"
                            role="tab"
                            aria-selected={activeRanking === tab.value}
                            class={[
                                "h-10 cursor-pointer rounded-md px-3 text-sm font-black uppercase italic transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-citric motion-reduce:transition-none",
                                activeRanking === tab.value
                                    ? "bg-orange-citric text-blue-night"
                                    : "border border-orange-citric/40 text-orange-citric hover:bg-orange-citric/10",
                            ]}
                            on:click={() => activeRanking = tab.value}
                        >
                            {tab.label}
                        </button>
                    {/each}
                </div>

                {#if ranking.length}
                    <ol class="grid min-h-0 flex-1 content-start gap-1 overflow-y-auto pr-1">
                        {#each ranking as item, index (item.key)}
                            <li class={[
                                "grid items-center gap-2 border-b border-suspense-aurora/15 py-2.5 last:border-b-0",
                                activeRanking === "viewers"
                                    ? "grid-cols-[1.75rem_auto_minmax(0,1fr)_auto]"
                                    : "grid-cols-[1.75rem_minmax(0,1fr)_auto]",
                            ]}>
                                <span class="text-xs font-black tabular-nums text-suspense-aurora/45">{index + 1}</span>
                                {#if activeRanking === "viewers"}
                                    <img
                                        src={resolvePlaceholderImage(item.avatar, "avatar")}
                                        alt=""
                                        aria-hidden="true"
                                        class="size-9 rounded-full border border-blue-skywave/35 object-cover object-top"
                                        loading="lazy"
                                    />
                                {/if}
                                <div class="min-w-0">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span class="truncate text-sm font-bold text-suspense-aurora" title={item.label}>{item.label}</span>
                                    </div>
                                    {#if item.location}
                                        <span class="block truncate text-[0.68rem] uppercase text-suspense-aurora/65" title={item.location}>{item.location}</span>
                                    {/if}
                                    <span class="block truncate text-[0.68rem] uppercase text-suspense-aurora/50">{item.detail}</span>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-blue-night/65">
                                        <div class="h-full rounded-full bg-orange-amber" style={`width: ${Math.max(4, (item.views / rankingMaximum) * 100)}%`}></div>
                                    </div>
                                </div>
                                <strong class="text-sm font-black tabular-nums text-orange-amber">{number.format(item.views)}</strong>
                            </li>
                        {/each}
                    </ol>
                {:else}
                    <p class="flex min-h-0 flex-1 items-center justify-center text-center text-sm text-suspense-aurora/60">
                        {activeRanking === "viewers" ? "Nenhum acesso com conta neste período." : "Nenhuma cidade identificada neste período."}
                    </p>
                {/if}
            </div>
        </div>
        {#if geography.unknown_views > 0}
            <p class="mt-1 text-xs text-suspense-aurora/55">
                {number.format(geography.unknown_views)} visualizações sem localização não aparecem no mapa.
            </p>
        {/if}
    {:else}
        <EmptyState
            title="Localizações ainda indisponíveis"
            description="O mapa aparecerá quando novas visualizações com IP público forem registradas."
        />
    {/if}

    <div class="mt-6">
        <h3 class="mb-3 font-noto-sans text-base font-black uppercase italic text-orange-amber">Origem do tráfego</h3>
        {#if sources.length}
            <ol class="grid gap-x-6 md:grid-cols-2">
                {#each sources as source, index (source.source)}
                    <li class="grid grid-cols-[1.75rem_minmax(0,1fr)_auto] items-center gap-2 border-b border-suspense-aurora/15 py-3">
                        <span class="text-xs font-black tabular-nums text-suspense-aurora/45">{index + 1}</span>
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-bold text-suspense-aurora" title={source.label}>{source.label}</span>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-blue-night/65">
                                <div class="h-full rounded-full bg-orange-amber" style={`width: ${Math.max(4, (Number(source.views ?? 0) / sourceMaximum) * 100)}%`}></div>
                            </div>
                        </div>
                        <strong class="text-sm font-black tabular-nums text-orange-amber">{number.format(source.views ?? 0)}</strong>
                    </li>
                {/each}
            </ol>
        {:else}
            <p class="text-sm text-suspense-aurora/60">Nenhuma origem registrada neste período.</p>
        {/if}
    </div>
</Section>
