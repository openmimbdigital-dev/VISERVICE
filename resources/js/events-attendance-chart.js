const BRAND = '#4f46e5';
const GRAY = '#94a3b8';

function normalizeSeries(labels, values) {
    const safeLabels = Array.isArray(labels) ? labels.map(String) : [];
    const safeValues = Array.isArray(values) ? values.map(Number) : [];

    return { labels: safeLabels, values: safeValues };
}

function buildOptions(labels, values) {
    return {
        chart: {
            type: 'bar',
            height: Math.max(280, 180 + labels.length * 28),
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, speed: 450 },
        },
        series: [{ name: 'Asistencia', data: values }],
        xaxis: {
            type: 'category',
            categories: labels,
            labels: {
                show: true,
                rotate: labels.length > 4 ? -25 : 0,
                rotateAlways: labels.length > 4,
                trim: false,
                style: {
                    fontSize: '11px',
                    colors: '#374151',
                    fontWeight: 600,
                },
            },
            tickPlacement: 'on',
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: { fontSize: '11px', colors: GRAY },
                formatter: (value) => Number(value).toLocaleString('es-CO', { maximumFractionDigits: 0 }),
            },
        },
        colors: [BRAND],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '55%',
                dataLabels: { position: 'top' },
            },
        },
        dataLabels: {
            enabled: true,
            offsetY: -18,
            style: { fontSize: '11px', colors: [BRAND] },
            formatter: (value) => Number(value).toLocaleString('es-CO', { maximumFractionDigits: 0 }),
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: (value, opts) => {
                    const label = labels[opts.dataPointIndex] || 'Tipo';

                    return `${label}: ${Number(value).toLocaleString('es-CO', { maximumFractionDigits: 0 })}`;
                },
            },
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        noData: {
            text: 'Sin datos de asistencia para graficar',
            style: { color: '#94a3b8', fontSize: '13px' },
        },
    };
}

window.renderEventAttendanceChart = function (chartEl, labels, values) {
    if (!chartEl || typeof window.ApexCharts !== 'function') {
        return;
    }

    const series = normalizeSeries(labels, values);

    if (chartEl._apexChart) {
        chartEl._apexChart.destroy();
        chartEl._apexChart = null;
    }

    chartEl._apexChart = new window.ApexCharts(
        chartEl,
        buildOptions(series.labels, series.values)
    );

    chartEl._apexChart.render();
};

window.updateEventAttendanceChart = function (chartEl, labels, values) {
    if (!chartEl || typeof window.ApexCharts !== 'function') {
        return;
    }

    const series = normalizeSeries(labels, values);

    if (!chartEl._apexChart) {
        window.renderEventAttendanceChart(chartEl, series.labels, series.values);

        return;
    }

    chartEl._apexChart.updateOptions(
        {
            xaxis: { categories: series.labels },
            chart: { height: Math.max(280, 180 + series.labels.length * 28) },
        },
        false,
        true
    );

    chartEl._apexChart.updateSeries([{ name: 'Asistencia', data: series.values }], true);
};
