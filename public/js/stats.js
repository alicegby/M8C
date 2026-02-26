document.addEventListener('DOMContentLoaded', function () {

    const mpDropdown = document.getElementById('mp-dropdown');
    const mpTagsContainer = document.getElementById('mp-tags');
    const applyFiltersBtn = document.getElementById('apply-filters');

    let selectedMPs = [];
    let charts = {};

    // Flatpickr
    flatpickr('#period-start', { dateFormat: "Y-m-d" });
    flatpickr('#period-end', { dateFormat: "Y-m-d" });

    const COLORS = {
        red: '#541826',
        lightRed: '#9D2137',
        grey: '#AAAAAA',
        dark: '#0E0E0E',
    };

    const CHART_DEFAULTS = {
        plugins: {
            legend: { labels: { color: '#F5F5F5', font: { family: 'Arimo' } } }
        },
        scales: {
            x: { ticks: { color: '#AAAAAA' }, grid: { color: '#2A2A2A' } },
            y: { beginAtZero: true, ticks: { color: '#AAAAAA' }, grid: { color: '#2A2A2A' } }
        }
    };

    function showEmpty(canvasId) {
        const canvas = document.getElementById(canvasId);
        canvas.style.display = 'none';
        if (!canvas.nextElementSibling?.classList.contains('chart-empty')) {
            canvas.insertAdjacentHTML('afterend', '<p class="chart-empty">Aucune donnée disponible.</p>');
        }
    }

    function hideEmpty(canvasId) {
        const canvas = document.getElementById(canvasId);
        canvas.style.display = '';
        canvas.nextElementSibling?.classList.contains('chart-empty') && canvas.nextElementSibling.remove();
    }

    // --- Chargement initial ---
    fetch('/admin/stats/data')
        .then(res => res.json())
        .then(data => {
            mpDropdown.innerHTML = '<option value="">-- Sélectionner une MP --</option>';
            data.murderParties.forEach(mp => {
                const option = document.createElement('option');
                option.value = mp.id;
                option.text = mp.title;
                mpDropdown.appendChild(option);
            });
            renderCharts(data);
        })
        .catch(err => console.error('Erreur chargement initial:', err));

    // --- Fetch avec filtres ---
    function fetchAndRender() {
        const start = document.getElementById('period-start').value;
        const end = document.getElementById('period-end').value;

        const params = new URLSearchParams();
        if (start) params.append('start', start);
        if (end) params.append('end', end);
        selectedMPs.forEach(mp => params.append('mp[]', mp.id));

        fetch(`/admin/stats/data?${params.toString()}`)
            .then(res => res.json())
            .then(data => renderCharts(data))
            .catch(err => console.error('Erreur fetchAndRender:', err));
    }

    function renderCharts(data) {
        renderSalesChart(data.sales);
        renderSuccessRateChart(data.success_rate);
        renderPromoVsFullChart(data.promo_vs_full);
        renderAvgBasketChart(data.avg_basket);
        renderPaymentMethodsChart(data.payment_methods);
        renderReturningPlayersChart(data.returning_players);
        renderRatedVsSoldChart(data.rated_vs_sold);
    }

    function destroyIfExists(key) {
        if (charts[key]) {
            charts[key].destroy();
            charts[key] = null;
        }
    }

    // 1. CA et ventes par MP
    function renderSalesChart(sales) {
        destroyIfExists('sales');
        if (!sales || sales.length === 0) { showEmpty('salesChart'); return; }
        hideEmpty('salesChart');
        charts.sales = new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: sales.map(s => s.title),
                datasets: [
                    { label: 'CA (€)', data: sales.map(s => s.totalRevenue), backgroundColor: COLORS.red },
                    { label: 'Ventes', data: sales.map(s => s.totalSales), backgroundColor: COLORS.lightRed }
                ]
            },
            options: { responsive: true, ...CHART_DEFAULTS }
        });
    }

    // 2. Coupable trouvé
    function renderSuccessRateChart(successRate) {
        destroyIfExists('successRate');
        if (!successRate || successRate.length === 0) { showEmpty('successRateChart'); return; }
        hideEmpty('successRateChart');
        charts.successRate = new Chart(document.getElementById('successRateChart'), {
            type: 'bar',
            data: {
                labels: successRate.map(s => s.title),
                datasets: [
                    { label: '% sessions gagnées', data: successRate.map(s => s.sessionSuccessPercent), backgroundColor: COLORS.red },
                    { label: '% joueurs ayant trouvé', data: successRate.map(s => s.playerSuccessPercent), backgroundColor: COLORS.lightRed }
                ]
            },
            options: {
                responsive: true,
                ...CHART_DEFAULTS,
                scales: {
                    ...CHART_DEFAULTS.scales,
                    y: { ...CHART_DEFAULTS.scales.y, max: 100 }
                }
            }
        });
    }

    // 3. Promo vs plein pot
    function renderPromoVsFullChart(promoVsFull) {
        destroyIfExists('promoVsFull');
        if (!promoVsFull || (promoVsFull.withPromo === 0 && promoVsFull.withoutPromo === 0)) {
            showEmpty('promoVsFullPriceChart'); return;
        }
        hideEmpty('promoVsFullPriceChart');
        charts.promoVsFull = new Chart(document.getElementById('promoVsFullPriceChart'), {
            type: 'doughnut',
            data: {
                labels: ['Avec code promo', 'Prix plein'],
                datasets: [{
                    data: [promoVsFull.withPromo, promoVsFull.withoutPromo],
                    backgroundColor: [COLORS.lightRed, COLORS.grey]
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#F5F5F5' } } }
            }
        });
    }

    // 4. Panier moyen
    function renderAvgBasketChart(avgBasket) {
        destroyIfExists('avgBasket');
        if (!avgBasket || avgBasket.length === 0) { showEmpty('avgBasketChart'); return; }
        hideEmpty('avgBasketChart');
        charts.avgBasket = new Chart(document.getElementById('avgBasketChart'), {
            type: 'bar',
            data: {
                labels: avgBasket.map(a => a.title),
                datasets: [{
                    label: 'Panier moyen (€)',
                    data: avgBasket.map(a => a.avgAmount),
                    backgroundColor: COLORS.red
                }]
            },
            options: { responsive: true, ...CHART_DEFAULTS }
        });
    }

    // 5. Répartition paiements
    function renderPaymentMethodsChart(paymentMethods) {
        destroyIfExists('paymentMethods');
        if (!paymentMethods || paymentMethods.length === 0) { showEmpty('paymentMethodsChart'); return; }
        hideEmpty('paymentMethodsChart');
        charts.paymentMethods = new Chart(document.getElementById('paymentMethodsChart'), {
            type: 'doughnut',
            data: {
                labels: paymentMethods.map(p => p.method),
                datasets: [{
                    data: paymentMethods.map(p => p.count),
                    backgroundColor: [COLORS.red, COLORS.lightRed, COLORS.grey, '#3a3a3a']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#F5F5F5' } } }
            }
        });
    }

    // 6. Taux de retour joueurs
    function renderReturningPlayersChart(returningPlayers) {
        destroyIfExists('returningPlayers');
        if (!returningPlayers || (returningPlayers.returning === 0 && returningPlayers.unique === 0)) {
            showEmpty('returningPlayersChart'); return;
        }
        hideEmpty('returningPlayersChart');
        charts.returningPlayers = new Chart(document.getElementById('returningPlayersChart'), {
            type: 'doughnut',
            data: {
                labels: ['Joueurs fidèles (2+ achats)', 'Joueurs uniques'],
                datasets: [{
                    data: [returningPlayers.returning, returningPlayers.unique],
                    backgroundColor: [COLORS.lightRed, COLORS.grey]
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#F5F5F5' } } }
            }
        });
    }

    // 7. MP notées vs vendues
    function renderRatedVsSoldChart(ratedVsSold) {
        destroyIfExists('ratedVsSold');
        if (!ratedVsSold || ratedVsSold.length === 0) { showEmpty('ratedVsSoldChart'); return; }
        hideEmpty('ratedVsSoldChart');
        charts.ratedVsSold = new Chart(document.getElementById('ratedVsSoldChart'), {
            type: 'bar',
            data: {
                labels: ratedVsSold.map(r => r.title),
                datasets: [
                    { label: 'Vendues', data: ratedVsSold.map(r => r.sold), backgroundColor: COLORS.red },
                    { label: 'Notées', data: ratedVsSold.map(r => r.rated), backgroundColor: COLORS.lightRed }
                ]
            },
            options: { responsive: true, ...CHART_DEFAULTS }
        });
    }

    // --- Dropdown → tags ---
    mpDropdown.addEventListener('change', () => {
        const selectedId = mpDropdown.value;
        if (!selectedId) return;
        const selectedText = mpDropdown.options[mpDropdown.selectedIndex].text;

        if (!selectedMPs.some(mp => mp.id === selectedId)) {
            selectedMPs.push({ id: selectedId, title: selectedText });

            const tag = document.createElement('div');
            tag.className = 'mp-tag';
            tag.dataset.id = selectedId;
            tag.innerHTML = `${selectedText} <span class="remove-tag">×</span>`;

            tag.querySelector('.remove-tag').addEventListener('click', () => {
                selectedMPs = selectedMPs.filter(mp => mp.id !== selectedId);
                tag.remove();
                fetchAndRender();
            });

            mpTagsContainer.appendChild(tag);
        }

        mpDropdown.value = '';
        fetchAndRender();
    });

    // --- Bouton appliquer ---
    applyFiltersBtn.addEventListener('click', fetchAndRender);
});