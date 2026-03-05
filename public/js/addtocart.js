const btnAddCart = document.getElementById('btn-add-cart');
const btnBuyNow = document.getElementById('btn-buy-now');

        if (btnAddCart) {
            btnAddCart.addEventListener('click', function () {
                const slug = this.dataset.slug;

                fetch(`/panier/ajouter/scenario/${slug}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Met à jour le badge
                            updateCartBadge(data.count);

                            // Change le bouton
                            btnAddCart.textContent = '✅ Ajouté au panier';
                            btnAddCart.disabled = true;
                            btnAddCart.style.opacity = '0.7';

                            // Affiche le bouton "Voir mon panier"
                            btnBuyNow.style.display = 'inline-block';
                        }
                    });
            });
        }