function showCartToast(name, price) {
    const existing = document.getElementById('cart-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'cart-toast';
    toast.innerHTML = `
        <span class="cart-toast-title">Ajouté au panier</span>
        <span class="cart-toast-name">${name}</span>
        <span class="cart-toast-price">${price} €</span>
    `;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('cart-toast-visible'), 10);
    setTimeout(() => {
        toast.classList.remove('cart-toast-visible');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        const type = this.dataset.type || 'scenario';
        const name = this.dataset.name;
        const price = this.dataset.price;
        const url = type === 'pack'
            ? `/panier/ajouter/pack/${this.dataset.id}`
            : `/panier/ajouter/scenario/${this.dataset.slug}`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Erreur réseau');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    updateCartBadge(data.count);

                    this.textContent = 'Ajouté au panier';
                    this.disabled = true;
                    this.style.opacity = '0.7';

                    const btnBuyNow = document.getElementById('btn-buy-now');
                    if (btnBuyNow) btnBuyNow.style.display = 'inline-block';

                    showCartToast(name, price);
                }
            })
            .catch(err => {
                console.error('Erreur panier:', err);
            });
    });
});