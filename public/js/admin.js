document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.admin-btn');
    const content = document.getElementById('admin-content');

    // --- FONCTION AJAX ---
    function loadAjax(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                initAjaxContent(); // ré-init JS
            })
            .catch(err => console.error('Erreur AJAX load:', err));
    }

    // --- INITIALISATION CONTENU AJAX ---
    function initAjaxContent() {
        initAjaxForms();
        initDynamicCollections();
        initUserFilters();
        initReviewFilters();
        initMPFilters();
        initDeleteConfirmations();
        initAjaxBackButtons();
        initAjaxShowLinks();
    }

    // --- FORMULAIRE AJAX (NEW & EDIT MP) ---
    function initAjaxForms() {
        const form = document.querySelector('#murder-party-form');
        if (!form) return;

        form.addEventListener('submit', e => {
            e.preventDefault();
            const data = new FormData(form);

            fetch(form.action, {
                method: form.method,
                body: data,
                headers: { 'X-Requested-With':'XMLHttpRequest' },
            })
            .then(async res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const json = await res.json();
                    if (json.success && json.redirect) window.location.href = json.redirect;
                } else {
                    const html = await res.text();
                    content.innerHTML = html;
                    initAjaxContent(); // ⚡ ré-init JS
                }
            })
            .catch(err => console.error('Erreur AJAX submit:', err));
        });
    }

    // --- COLLECTIONS DYNAMIQUES (PERSONNAGES) ---
    function initDynamicCollections() {
        const container = document.getElementById('characters-list');
        if (!container) return;

        let characterIndex = container.querySelectorAll('.mp-character-item').length || 0;
        const prototype = container.dataset.prototype;
        const addBtn = document.getElementById('add-character-btn');

        addBtn?.addEventListener('click', () => {
            if (!prototype) return;

            const html = prototype.replace(/__name__/g, characterIndex);
            const wrapper = document.createElement('div');
            wrapper.classList.add('mp-character-item');
            wrapper.innerHTML = `<h4>Personnage ${characterIndex + 1}</h4>${html}<button type="button" class="btn btn-remove">Supprimer</button>`;
            container.appendChild(wrapper);

            wrapper.querySelector('.btn-remove')?.addEventListener('click', () => wrapper.remove());
            container.querySelector('.empty-message')?.remove();

            characterIndex++;
        });
    }

    // --- CONFIRM DELETE ---
    function initDeleteConfirmations() {
        content.querySelectorAll('form[data-action="delete"]').forEach(f => {
            f.addEventListener('submit', e => {
                if (!confirm('Êtes-vous sûr(e) ? Cette action est irréversible.')) e.preventDefault();
            });
        });
    }

    // --- BOUTONS RETOUR AJAX ---
    function initAjaxBackButtons() {
        content.querySelectorAll('.ajax-btn.btn-back').forEach(btn => {
            btn.addEventListener('click', () => loadAjax(btn.dataset.url));
        });
    }

    // --- SHOW LINKS AJAX ---
    function initAjaxShowLinks() {
        content.querySelectorAll('.ajax-link-show').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                loadAjax(link.href);
            });
        });
    }

    // --- FILTRES UTILISATEURS ---
    function initUserFilters() {
        const filterToggle = document.getElementById('filter-toggle');
        const filterMenu = document.getElementById('filter-menu');
        const searchInput = document.getElementById('user-search');
        const newsletterSelect = document.getElementById('newsletter-filter');
        const tableBody = document.getElementById('user-table-body');
        if (!filterToggle || !filterMenu || !searchInput || !newsletterSelect || !tableBody) return;

        filterToggle.addEventListener('click', () => {
            filterMenu.style.display = filterMenu.style.display === 'block' ? 'none' : 'block';
        });

        function filterUsers() {
            const search = searchInput.value.toLowerCase();
            const newsletter = newsletterSelect.value;
            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;
                const prenom = row.children[0].textContent.toLowerCase();
                const nom = row.children[1].textContent.toLowerCase();
                const email = row.children[2].textContent.toLowerCase();
                const newsletterVal = row.children[3].textContent.toLowerCase();
                const matchesSearch = prenom.includes(search) || nom.includes(search) || email.includes(search);
                const matchesNewsletter = newsletter === '' || (newsletter === '1' && newsletterVal === 'oui') || (newsletter === '0' && newsletterVal === 'non');
                row.style.display = matchesSearch && matchesNewsletter ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterUsers);
        newsletterSelect.addEventListener('change', filterUsers);
    }

    // --- FILTRES AVIS ---
    function initReviewFilters() {
        const filterToggle = document.getElementById('review-filter-toggle');
        const filterMenu = document.getElementById('review-filter-menu');
        const statusSelect = document.getElementById('review-status-filter');
        const tableBody = document.getElementById('review-table-body');
        if (!filterToggle || !filterMenu || !statusSelect || !tableBody) return;

        filterToggle.addEventListener('click', () => {
            filterMenu.style.display = filterMenu.style.display === 'block' ? 'none' : 'block';
        });

        statusSelect.addEventListener('change', () => {
            const status = statusSelect.value;
            tableBody.querySelectorAll('tr').forEach(row => {
                row.style.display = (status === '' || row.dataset.status === status) ? '' : 'none';
            });
        });
    }

    // --- FILTRES MURDER PARTIES ---
    function initMPFilters() {
        const keywordInput = document.getElementById('mp-keyword-filter');
        const minPlayersInput = document.getElementById('mp-min-players-filter');
        const minDurationInput = document.getElementById('mp-min-duration-filter');
        const tableBody = document.getElementById('mp-table-body');
        const filterToggle = document.getElementById('mp-filter-toggle');
        const filterMenu = document.getElementById('mp-filter-menu');

        if (!keywordInput || !minPlayersInput || !minDurationInput || !tableBody) return;

        // toggle menu MP
        if (filterToggle && filterMenu) {
            filterToggle.addEventListener('click', () => {
                filterMenu.style.display = filterMenu.style.display === 'block' ? 'none' : 'block';
            });
        }

        function filterMPs() {
            const keyword = keywordInput.value.toLowerCase();
            const minPlayers = parseInt(minPlayersInput.value) || 0;
            const minDuration = parseInt(minDurationInput.value) || 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;
                const title = row.children[0].textContent.toLowerCase();
                const players = parseInt(row.dataset.players);
                const duration = parseInt(row.dataset.duration);
                const show = title.includes(keyword) && players >= minPlayers && duration >= minDuration;
                row.style.display = show ? '' : 'none';
            });
        }

        keywordInput.addEventListener('input', filterMPs);
        minPlayersInput.addEventListener('input', filterMPs);
        minDurationInput.addEventListener('input', filterMPs);
    }

    // --- SIDEBAR ---
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const url = btn.dataset.url;
            if (url) loadAjax(url);
            else content.innerHTML = `<p>Module à développer.</p>`;
        });
    });

    // --- BOUTON CREER MP INDEX ---
    document.addEventListener('click', e => {
        if (e.target && e.target.id === 'create-mp-btn') loadAjax(e.target.dataset.url);
    });

    // --- INITIALISATION DES FILTRES AU CHARGEMENT ---
    initUserFilters();
    initReviewFilters();
    initMPFilters();
});