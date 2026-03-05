const burger = document.querySelector('.burger');
const menu = document.querySelector('.menu-links');
const closeMenu = document.querySelector('.menu-close');

burger.addEventListener('click', () => {
    menu.classList.add('active');    // ouvre le menu
    burger.classList.add('active');  // cache le burger
});

closeMenu.addEventListener('click', () => {
    menu.classList.remove('active'); // ferme le menu
    burger.classList.remove('active'); // réaffiche le burger
});

function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}