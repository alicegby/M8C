const btnApplyPromo = document.getElementById('btn-apply-promo');
        const btnRemovePromo = document.getElementById('btn-remove-promo');
        const promoError = document.getElementById('promo-error');

        if (btnApplyPromo) {
            btnApplyPromo.addEventListener('click', function () {
                const code = document.getElementById('promo-input').value.trim();
                if (!code) return;

                fetch('/panier/promo/appliquer', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `code=${encodeURIComponent(code)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        promoError.textContent = data.error;
                        promoError.style.display = 'block';
                    }
                });
            });
        }

        if (btnRemovePromo) {
            btnRemovePromo.addEventListener('click', function () {
                fetch('/panier/promo/supprimer', { method: 'POST' })
                    .then(() => location.reload());
            });
        }