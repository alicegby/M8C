document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.admin-btn');
    const content = document.getElementById('admin-content');

    // FONCTION AJAX
    function loadAjax(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                initAjaxContent();
            })
            .catch(err => console.error('Erreur AJAX load:', err));
    }

    // INITIALISATION CONTENU AJAX 
    function initAjaxContent() {
        initDynamicCollections();
        initDynamicClues();
        initUserFilters();
        initReviewFilters();
        initMPFilters();
        initPromoFilters();
        initDeleteConfirmations();
        initAjaxBackButtons();
        initAjaxShowLinks();
        initAvatarButtons();
        initPurchaseFilters();
        initRefundButtons();
    }

    // BOUTONS RETOUR 
    function initAjaxBackButtons() {
        content.querySelectorAll('.btn-back[data-url]').forEach(btn => {
            btn.addEventListener('click', () => loadAjax(btn.dataset.url));
        });
    }

    // LIENS AJAX SHOW 
    function initAjaxShowLinks() {
        content.querySelectorAll('a.ajax-link-show').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                loadAjax(link.href);
            });
        });
    }

    // BOUTONS AVATARS 
    function initAvatarButtons() {
        const createBtn = document.getElementById('create-avatar-btn');
        createBtn?.addEventListener('click', () => loadAjax(createBtn.dataset.url));

        content.querySelectorAll('.btn-show').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                loadAjax(link.href);
            });
        });

        content.querySelectorAll('form[data-action="delete-avatar"]').forEach(f => {
            f.addEventListener('submit', e => {
                if (!confirm('Voulez-vous vraiment supprimer cet avatar ?')) e.preventDefault();
            });
        });
    }

    // CONFIRM DELETE 
    function initDeleteConfirmations() {
        content.querySelectorAll('form[data-action="delete"]').forEach(f => {
            f.addEventListener('submit', e => {
                if (!confirm('Êtes-vous sûr(e) ? Cette action est irréversible.')) e.preventDefault();
            });
        });
    }

    // COLLECTIONS DYNAMIQUES (PERSONNAGES) 
    function initDynamicCollections() {
        const container = document.getElementById('characters-list');
        if (!container) return;

        let characterIndex = container.querySelectorAll('.mp-character-item').length || 0;
        const prototype = container.dataset.prototype;
        const addBtn = document.getElementById('add-character-btn');

        // Fonction pour activer les toggles de tous les personnages
        function attachCharacterToggles() {
            container.querySelectorAll('.mp-character-item').forEach(wrapper => {
                const header = wrapper.querySelector('.character-header');
                const body = wrapper.querySelector('.character-body');
                const toggleBtn = wrapper.querySelector('.btn-toggle-character');

                // On enlève les anciens events pour éviter les doublons
                header.replaceWith(header.cloneNode(true));
                const newHeader = wrapper.querySelector('.character-header');

                newHeader.addEventListener('click', () => {
                    body.classList.toggle('hidden');
                    toggleBtn.textContent = body.classList.contains('hidden') ? '▼' : '▲';
                });
            });
        }

        // Initialisation des personnages existants
        attachCharacterToggles();

        // Ajouter un nouveau personnage
        addBtn?.addEventListener('click', () => {
            if (!prototype) return;

            const html = prototype.replace(/__name__/g, characterIndex);
            const number = characterIndex + 1;

            const wrapper = document.createElement('div');
            wrapper.classList.add('mp-character-item');
            wrapper.innerHTML = `
                <div class="character-header">
                    <h4>Personnage ${number}</h4>
                    <button type="button" class="btn-toggle-character">▼</button>
                </div>
                <div class="character-body hidden">
                    ${html}
                    <button type="button" class="btn btn-remove">Supprimer</button>
                </div>
            `;

            container.appendChild(wrapper);
            container.querySelector('.empty-message')?.remove();

            // Suppression du personnage
            wrapper.querySelector('.btn-remove')?.addEventListener('click', () => {
                wrapper.remove();
                container.querySelectorAll('.mp-character-item h4').forEach((h4, i) => {
                    h4.textContent = `Personnage ${i + 1}`;
                });
                if (container.querySelectorAll('.mp-character-item').length === 0) {
                    container.innerHTML = '<p class="empty-message">Aucun personnage ajouté.</p>';
                }
            });

            // Mettre à jour les toggles
            attachCharacterToggles();

            characterIndex++;
        });
    }

    // COLLECTIONS DYNAMIQUES (INDICES) 
   function initDynamicClues() {
        const container = document.getElementById('clues-list');
        if (!container) return;

        let clueIndex = container.querySelectorAll('.mp-clue-item').length || 0;
        const prototype = container.dataset.prototype;
        const addBtn = document.getElementById('add-clue-btn');

        // Fonction pour activer les toggles de tous les indices
        function attachClueToggles() {
            container.querySelectorAll('.mp-clue-item').forEach(wrapper => {
                const header = wrapper.querySelector('.clue-header');
                const body = wrapper.querySelector('.clue-body');
                const toggleBtn = wrapper.querySelector('.btn-toggle-clue');

                header.replaceWith(header.cloneNode(true));
                const newHeader = wrapper.querySelector('.clue-header');

                newHeader.addEventListener('click', () => {
                    body.classList.toggle('hidden');
                    toggleBtn.textContent = body.classList.contains('hidden') ? '▼' : '▲';
                });
            });
        }

        // Initialisation des indices existants
        attachClueToggles();

        // Ajouter un nouvel indice
        addBtn?.addEventListener('click', () => {
            if (!prototype) return;

            const html = prototype.replace(/__name__/g, clueIndex);
            const number = clueIndex + 1;

            const wrapper = document.createElement('div');
            wrapper.classList.add('mp-clue-item');
            wrapper.innerHTML = `
                <div class="clue-header">
                    <h4>Indice ${number}</h4>
                    <button type="button" class="btn-toggle-clue">▼</button>
                </div>
                <div class="clue-body hidden">
                    ${html}
                    <button type="button" class="btn btn-remove">Supprimer</button>
                </div>
            `;

            container.appendChild(wrapper);
            container.querySelector('.empty-message')?.remove();

            // Suppression de l’indice
            wrapper.querySelector('.btn-remove')?.addEventListener('click', () => {
                wrapper.remove();
                reindexClues();
            });

            // Mettre à jour les toggles
            attachClueToggles();

            clueIndex++;
        });

        function reindexClues() {
            container.querySelectorAll('.mp-clue-item .clue-header h4').forEach((h4, i) => {
                h4.textContent = `Indice ${i + 1}`;
            });
            if (container.querySelectorAll('.mp-clue-item').length === 0) {
                container.innerHTML = '<p class="empty-message">Aucun indice ajouté.</p>';
            }
        }
    }

    // FILTRES UTILISATEURS 
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
            let visibleCount = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;
                const prenom = row.children[0].textContent.toLowerCase();
                const nom = row.children[1].textContent.toLowerCase();
                const email = row.children[2].textContent.toLowerCase();
                const newsletterVal = row.children[3].textContent.toLowerCase();
                const matchesSearch = prenom.includes(search) || nom.includes(search) || email.includes(search);
                const matchesNewsletter = newsletter === '' || (newsletter === '1' && newsletterVal === 'oui') || (newsletter === '0' && newsletterVal === 'non');
                const visible = matchesSearch && matchesNewsletter;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const counter = document.getElementById('user-count');
            if (counter) counter.textContent = visibleCount;
        }

        searchInput.addEventListener('input', filterUsers);
        newsletterSelect.addEventListener('change', filterUsers);
    }

    // FILTRES AVIS 
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
            let visibleCount = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;
                const visible = status === '' || row.dataset.status === status;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const counter = document.getElementById('review-count');
            if (counter) counter.textContent = visibleCount;
        });
    }

    // FILTRES MURDER PARTIES 
    function initMPFilters() {
        const keywordInput = document.getElementById('mp-keyword-filter');
        const minPlayersInput = document.getElementById('mp-min-players-filter');
        const minDurationInput = document.getElementById('mp-min-duration-filter');
        const tableBody = document.getElementById('mp-table-body');
        const filterToggle = document.getElementById('mp-filter-toggle');
        const filterMenu = document.getElementById('mp-filter-menu');

        if (!keywordInput || !minPlayersInput || !minDurationInput || !tableBody) return;

        if (filterToggle && filterMenu) {
            filterToggle.addEventListener('click', () => {
                filterMenu.classList.toggle('hidden');
            });
        }

        function filterMPs() {
            const keyword = keywordInput.value.toLowerCase();
            const minPlayers = parseInt(minPlayersInput.value) || 0;
            const minDuration = parseInt(minDurationInput.value) || 0;
            let visibleCount = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;
                const title = row.children[0].textContent.toLowerCase();
                const players = parseInt(row.dataset.players);
                const duration = parseInt(row.dataset.duration);
                const show = title.includes(keyword) && players >= minPlayers && duration >= minDuration;
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            const counter = document.getElementById('mp-count');
            if (counter) counter.textContent = visibleCount;
        }

        keywordInput.addEventListener('input', filterMPs);
        minPlayersInput.addEventListener('input', filterMPs);
        minDurationInput.addEventListener('input', filterMPs);
    }

    // FILTRES PROMOS 
    function initPromoFilters() {
        const filterToggle = document.getElementById('promo-filter-toggle');
        const filterMenu = document.getElementById('promo-filter-menu');
        const usesSelect = document.getElementById('promo-uses-filter');
        const validitySelect = document.getElementById('promo-validity-filter');
        const activeSelect = document.getElementById('promo-active-filter');
        const tableBody = document.getElementById('promo-table-body');

        if (!filterToggle || !filterMenu || !tableBody) return;

        filterToggle.addEventListener('click', () => {
            filterMenu.classList.toggle('hidden');
        });

        function filterPromos() {
            const uses = usesSelect.value;
            const validity = validitySelect.value;
            const active = activeSelect.value;
            const today = new Date().toISOString().split('T')[0];
            let visibleCount = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.children.length === 1) return;

                const currentUses = parseInt(row.dataset.currentUses) || 0;
                const maxUses = row.dataset.maxUses !== '' ? parseInt(row.dataset.maxUses) : null;
                const validUntil = row.dataset.validUntil;
                const rowActive = row.dataset.active;

                let matchUses = true;
                if (uses === 'unused') matchUses = currentUses === 0;
                else if (uses === 'used') matchUses = currentUses > 0 && (maxUses === null || currentUses < maxUses);
                else if (uses === 'full') matchUses = maxUses !== null && currentUses >= maxUses;

                let matchValidity = true;
                if (validity === 'valid') {
                    matchValidity = validUntil === '' || validUntil >= today;
                } else if (validity === 'expired') {
                    matchValidity = validUntil !== '' && validUntil < today;
                } else if (validity === 'unlimited') {
                    matchValidity = validUntil === '';
                }

                const matchActive = active === '' || rowActive === active;

                const visible = matchUses && matchValidity && matchActive;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const counter = document.getElementById('promo-count');
            if (counter) counter.textContent = visibleCount;
        }

        usesSelect.addEventListener('change', filterPromos);
        validitySelect.addEventListener('change', filterPromos);
        activeSelect.addEventListener('change', filterPromos);
    }

    // FILTRES ACHATS 
    function initPurchaseFilters() {
        const filterToggle = document.getElementById('purchase-filter-toggle');
        const filterMenu = document.getElementById('purchase-filter-menu');
        const searchInput = document.getElementById('purchase-search');
        const statusSelect = document.getElementById('purchase-status-filter');
        const typeSelect = document.getElementById('purchase-type-filter');
        const refundableSelect = document.getElementById('purchase-refundable-filter');
        const tableBody = document.getElementById('purchase-table-body');

        if (!tableBody) return;

        filterToggle?.addEventListener('click', () => {
            filterMenu.classList.toggle('hidden');
        });

        function filterPurchases() {
            const search = searchInput?.value.toLowerCase() || '';
            const status = statusSelect?.value || '';
            const type = typeSelect?.value || '';
            const refundable = refundableSelect?.value || '';
            let visibleCount = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                const matchSearch = !search || row.dataset.client.includes(search);
                const matchStatus = !status || row.dataset.status === status;
                const matchType = !type || row.dataset.type === type;
                const matchRefundable = !refundable || row.dataset.refundable === refundable;

                const visible = matchSearch && matchStatus && matchType && matchRefundable;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const counter = document.getElementById('purchase-count');
            if (counter) counter.textContent = visibleCount;
        }

        searchInput?.addEventListener('input', filterPurchases);
        statusSelect?.addEventListener('change', filterPurchases);
        typeSelect?.addEventListener('change', filterPurchases);
        refundableSelect?.addEventListener('change', filterPurchases);
    }

    // REMBOURSEMENTS 
    function initRefundButtons() {
        content.querySelectorAll('.refund-form').forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();

                if (!confirm('Confirmer le remboursement ? Cette action est irréversible.')) return;

                const url = form.dataset.url;
                const token = form.querySelector('input[name="_token"]').value;
                const purchaseId = form.dataset.purchaseId;

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `_token=${encodeURIComponent(token)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`purchase-row-${purchaseId}`);
                        if (row) {
                            row.querySelector('.purchase-status').innerHTML = '↩️ Remboursé';
                            row.querySelector('.purchase-status').className = 'purchase-status purchase-status--refunded';
                            row.querySelector('td:last-child').innerHTML = '<span class="text-muted">—</span>';
                        }
                    } else {
                        alert('Erreur : ' + data.error);
                    }
                })
                .catch(() => alert('Erreur lors du remboursement.'));
            });
        });
    }

    // SIDEBAR 
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const url = btn.dataset.url;
            if (url) loadAjax(url);
            else content.innerHTML = `<p>Module à développer.</p>`;
        });
    });

    // BOUTONS CREATION PAGE FULL 
    document.addEventListener('click', e => {
        if (e.target?.id === 'create-mp-btn') window.location.href = e.target.dataset.url;
        if (e.target?.id === 'create-pack-btn') window.location.href = e.target.dataset.url;
        if (e.target?.id === 'create-promo-btn') window.location.href = e.target.dataset.url;
        if (e.target?.id === 'create-avatar-btn') window.location.href = e.target.dataset.url;
    });

    // INITIALISATION AU CHARGEMENT 
    initUserFilters();
    initReviewFilters();
    initMPFilters();
    initDynamicCollections();
    initDynamicClues();
    initPromoFilters();
    initAvatarButtons();
    initPurchaseFilters();
    initRefundButtons();
});