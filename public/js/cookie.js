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

    function getCookie() {
        return document.cookie.includes(COOKIE_NAME + "=accepted");
    }

    function enableCookies() {
        console.log("Cookies activés");
        // loadGoogleAnalytics();
    }

    function disableCookies() {
        console.log("Cookies refusés");

        // Supprime cookies analytics si présents
        deleteCookie("_ga");
        deleteCookie("_gid");
        deleteCookie("_gat");
    }

    // Vérification au chargement
    if (getCookie()) {
        banner.style.display = 'none';
        enableCookies();
    } else {
        banner.style.display = 'block';
    }

    // Accepter
    acceptBtn?.addEventListener('click', function() {
        setCookie("accepted");
        banner.style.display = 'none';
        enableCookies();
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