document.addEventListener('DOMContentLoaded', () => {
    const sujetSelect       = document.querySelector('select[name="contact[sujet]"]');
    const sousMenuContainer = document.getElementById('sous-menu-container');
    const nombrePersonnes   = document.querySelector('input[name="contact[nombrePersonnes]"]');
    const dateEvenement     = document.querySelector('input[name="contact[dateEvenement]"]');

    if (!sujetSelect || !sousMenuContainer) {
        console.error('Éléments introuvables — vérifier les sélecteurs');
        return;
    }

    function updateSousMenu() {
        if (sujetSelect.value === 'Demande de Murder Party personnalisée') {
            sousMenuContainer.style.display = 'block';
            if (nombrePersonnes) nombrePersonnes.required = true;
            if (dateEvenement)   dateEvenement.required   = true;
        } else {
            sousMenuContainer.style.display = 'none';
            if (nombrePersonnes) nombrePersonnes.required = false;
            if (dateEvenement)   dateEvenement.required   = false;
        }
    }

    updateSousMenu();
    sujetSelect.addEventListener('change', updateSousMenu);
});