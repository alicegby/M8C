document.addEventListener('DOMContentLoaded', function () {

    const mpDropdown = document.getElementById('mp-dropdown');
    const mpTagsContainer = document.getElementById('mp-tags');
    const applyFiltersBtn = document.getElementById('apply-filters');

    let selectedMPs = [];
    let statsData = {};

    // Flatpickr pour le calendrier
    flatpickr('#period-start', { dateFormat: "Y-m-d" });
    flatpickr('#period-end', { dateFormat: "Y-m-d" });

    fetch('/admin/stats/data')
        .then(res => res.json())
        .then(data => {
            statsData = data;

            // Remplir dropdown
            data.murderParties.forEach(mp => {
                const option = document.createElement('option');
                option.value = mp.id;
                option.text = mp.title;
                mpDropdown.appendChild(option);
            });

            initCharts(data);
        })
        .catch(err => console.error(err));

    // Gestion dropdown → tags
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
            });

            mpTagsContainer.appendChild(tag);
        }

        mpDropdown.value = '';
    });

    // Appliquer filtres
    applyFiltersBtn.addEventListener('click', () => {
        updateCharts(selectedMPs, statsData);
    });

    // --- Fonctions charts ---
    let salesChart, successRateChart, ratedVsSoldChart;

    function initCharts(data) {
        const ctxSales = document.getElementById('salesChart');
        salesChart = new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: data.murderParties.map(mp => mp.title),
                datasets: [
                    { label: 'CA (€)', data: data.sales.map(s => s.totalRevenue), backgroundColor: '#541826' },
                    { label: 'Ventes', data: data.sales.map(s => s.totalSales), backgroundColor: '#9D2137' }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        const ctxSuccess = document.getElementById('successRateChart');
        successRateChart = new Chart(ctxSuccess, {
            type: 'bar',
            data: {
                labels: data.murderParties.map(mp => mp.title),
                datasets: [{ label: '% joueurs trouvant le coupable', data: data.success_rate.map(s => s.successPercent), backgroundColor: '#9D2137' }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
        });

        const ctxRated = document.getElementById('ratedVsSoldChart');
        ratedVsSoldChart = new Chart(ctxRated, {
            type: 'bar',
            data: {
                labels: data.murderParties.map(mp => mp.title),
                datasets: [
                    { label: 'Vendues', data: data.rated_vs_sold.map(i => i.sold), backgroundColor: '#541826' },
                    { label: 'Notées', data: data.rated_vs_sold.map(i => i.rated), backgroundColor: '#9D2137' }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function updateCharts(selectedMPs, data) {
        if (selectedMPs.length === 0) return initCharts(data);

        const ids = selectedMPs.map(mp => parseInt(mp.id));

        const filteredSales = data.sales.filter(s => ids.includes(s.murderPartyId));
        salesChart.data.labels = filteredSales.map(s => s.title);
        salesChart.data.datasets[0].data = filteredSales.map(s => s.totalRevenue);
        salesChart.data.datasets[1].data = filteredSales.map(s => s.totalSales);
        salesChart.update();

        const filteredSuccess = data.success_rate.filter(s => ids.includes(s.murderPartyId));
        successRateChart.data.labels = filteredSuccess.map(s => s.title);
        successRateChart.data.datasets[0].data = filteredSuccess.map(s => s.successPercent);
        successRateChart.update();

        const filteredRated = data.rated_vs_sold.filter(s => ids.includes(s.murderPartyId));
        ratedVsSoldChart.data.labels = filteredRated.map(s => s.title);
        ratedVsSoldChart.data.datasets[0].data = filteredRated.map(s => s.sold);
        ratedVsSoldChart.data.datasets[1].data = filteredRated.map(s => s.rated);
        ratedVsSoldChart.update();
    }

});