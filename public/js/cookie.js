document.addEventListener('DOMContentLoaded', function() {

    const banner = document.getElementById('cookie-banner');
    const acceptBtn = document.getElementById('accept-cookies');
    const rejectBtn = document.getElementById('reject-cookies');
    const manageBtn = document.getElementById('manage-cookies');

    if (!banner) return;

    const COOKIE_NAME = "cookies_consent";

    function setCookie(value) {
        document.cookie = COOKIE_NAME + "=" + value + "; path=/; max-age=" + 60*60*24*365;
    }

    function deleteCookie(name) {
        document.cookie = name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
    }

    function getCookieValue() {
        const match = document.cookie.match(new RegExp('(^| )' + COOKIE_NAME + '=([^;]+)'));
        return match ? match[2] : null;
    }

    function disableCookies() {
        deleteCookie("_ga");
        deleteCookie("_gid");
        deleteCookie("_gat");
    }

    const cookieValue = getCookieValue();

    // Vérification au chargement
    if (cookieValue === 'accepted') {
        banner.style.display = 'none';
    } else if (cookieValue === 'rejected') {
        banner.style.display = 'none';
        disableCookies();
    } else {
        banner.style.display = 'block';
    }

    // Accepter
    acceptBtn?.addEventListener('click', function() {
        setCookie("accepted");
        banner.style.display = 'none';
        location.reload(); // Recharge pour activer GTM côté serveur
    });

    // Refuser
    rejectBtn?.addEventListener('click', function() {
        setCookie("rejected");
        disableCookies();
        banner.style.display = 'none';
    });

    // Gérer mes cookies
    manageBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        banner.style.display = 'block';
    });

});