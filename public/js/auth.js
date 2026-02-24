document.addEventListener('DOMContentLoaded', () => {

    // ================================
    // TOGGLE MOT DE PASSE
    // ================================
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = btn.closest('.input-password');
            const input   = wrapper ? wrapper.querySelector('input') : null;
            if (!input) return;

            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('show'); // bascule eye-open / eye-closed
        });
    });

    // ================================
    // FORCE DU MOT DE PASSE
    // ================================
    const passwordInput = document.querySelector('input[id$="plainPassword_first"], input#password');
    const strengthBar   = document.getElementById('password-strength');

    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let score = 0;

            if (val.length >= 8)          score++;
            if (/[A-Z]/.test(val))        score++;
            if (/[0-9]/.test(val))        score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            strengthBar.className = 'password-strength';
            if (val.length === 0) return;

            if (score <= 1)      strengthBar.classList.add('weak');
            else if (score <= 2) strengthBar.classList.add('medium');
            else                 strengthBar.classList.add('strong');
        });
    }

});