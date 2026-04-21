/**
 * Graphiques du tableau de bord élevage (Chart.js).
 */
(function () {
    function readPayload() {
        const el = document.getElementById('animal-stats-chart-data');
        if (!el || !el.textContent.trim()) {
            return null;
        }
        try {
            return JSON.parse(el.textContent);
        } catch (e) {
            return null;
        }
    }

    const palette = [
        '#4B8B3B',
        '#7DBF6C',
        '#3498db',
        '#9b59b6',
        '#f39c12',
        '#1abc9c',
        '#e67e22',
        '#95a5a6',
        '#16a085',
        '#d35400',
        '#8e44ad',
        '#2ecc71',
    ];

    function colorsFor(count) {
        const out = [];
        for (let i = 0; i < count; i++) {
            out.push(palette[i % palette.length]);
        }
        return out;
    }

    function objectToChartData(obj) {
        const labels = [];
        const data = [];
        if (!obj || typeof obj !== 'object') {
            return { labels, data: [], colors: [] };
        }
        for (const [k, v] of Object.entries(obj)) {
            const label = k === '' ? 'Non renseigné' : String(k);
            labels.push(label);
            data.push(Number(v) || 0);
        }
        return { labels, data, colors: colorsFor(labels.length) };
    }

    function emptyMessage(canvasId, text) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !canvas.parentElement) {
            return;
        }
        const wrap = canvas.parentElement;
        canvas.style.display = 'none';
        const p = document.createElement('p');
        p.textContent = text;
        p.style.cssText =
            'margin:0;padding:2rem 1rem;text-align:center;color:rgba(255,255,255,0.55);font-size:0.95rem;';
        wrap.appendChild(p);
    }

    const legendBottom = {
        display: true,
        position: 'bottom',
        labels: {
            color: 'rgba(255,255,255,0.88)',
            boxWidth: 10,
            padding: 10,
            font: { size: 11 },
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            return;
        }
        const payload = readPayload();
        if (!payload) {
            return;
        }

        Chart.defaults.color = 'rgba(255,255,255,0.82)';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.12)';
        Chart.defaults.font.family = "'Outfit', 'Inter', sans-serif";

        const pctTooltip = {
            callbacks: {
                label(ctx) {
                    const raw = ctx.dataset.data;
                    const total = raw.reduce(function (a, b) {
                        return a + b;
                    }, 0) || 1;
                    const n = Number(raw[ctx.dataIndex]) || 0;
                    const pct = ((n / total) * 100).toFixed(1);
                    return ' ' + ctx.label + ': ' + n + ' (' + pct + '%)';
                },
            },
        };

        const species = objectToChartData(payload.par_espece);
        const health = objectToChartData(payload.par_sante);
        const breeds = objectToChartData(payload.par_race);
        const actifs = Number(payload.total_actifs) || 0;
        const archives = Number(payload.total_archives) || 0;

        const pieBorder = { borderColor: 'rgba(28, 45, 33, 0.9)', borderWidth: 1 };

        if (species.labels.length === 0) {
            emptyMessage('chart-espece', 'Aucune espèce à afficher (ajoutez des animaux actifs).');
        } else {
            new Chart(document.getElementById('chart-espece'), {
                type: 'pie',
                data: {
                    labels: species.labels,
                    datasets: [
                        {
                            data: species.data,
                            backgroundColor: species.colors,
                            ...pieBorder,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: legendBottom,
                        tooltip: pctTooltip,
                    },
                },
            });
        }

        if (health.labels.length === 0) {
            emptyMessage('chart-sante', 'Aucun état de santé renseigné.');
        } else {
            new Chart(document.getElementById('chart-sante'), {
                type: 'doughnut',
                data: {
                    labels: health.labels,
                    datasets: [
                        {
                            data: health.data,
                            backgroundColor: health.colors,
                            ...pieBorder,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: {
                        legend: legendBottom,
                        tooltip: pctTooltip,
                    },
                },
            });
        }

        if (actifs + archives === 0) {
            emptyMessage('chart-cheptel', 'Aucun animal enregistré.');
        } else {
            new Chart(document.getElementById('chart-cheptel'), {
                type: 'doughnut',
                data: {
                    labels: ['Actifs', 'Archives'],
                    datasets: [
                        {
                            data: [actifs, archives],
                            backgroundColor: ['#4B8B3B', 'rgba(149, 165, 166, 0.55)'],
                            ...pieBorder,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '52%',
                    plugins: {
                        legend: legendBottom,
                        tooltip: {
                            callbacks: {
                                label(ctx) {
                                    const n = Number(ctx.dataset.data[ctx.dataIndex]) || 0;
                                    return ' ' + ctx.label + ': ' + n;
                                },
                            },
                        },
                    },
                },
            });
        }

        if (breeds.labels.length === 0) {
            emptyMessage('chart-race', 'Aucune race renseignée.');
        } else {
            new Chart(document.getElementById('chart-race'), {
                type: 'bar',
                data: {
                    labels: breeds.labels,
                    datasets: [
                        {
                            label: 'Effectif',
                            data: breeds.data,
                            backgroundColor: breeds.colors,
                            borderColor: 'rgba(255,255,255,0.12)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label(ctx) {
                                    return ' ' + ctx.parsed.x + ' tête(s)';
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(255,255,255,0.06)' },
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } },
                        },
                    },
                },
            });
        }
    });
})();
