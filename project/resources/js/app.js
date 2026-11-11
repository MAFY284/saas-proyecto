document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = document.getElementById(toggle.dataset.passwordToggle);

            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const isVisible = input.type === 'password';
            input.type = isVisible ? 'text' : 'password';
            toggle.querySelector('[data-password-eye-slash]')?.classList.toggle('hidden', isVisible);
            toggle.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
            toggle.setAttribute('aria-label', isVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            toggle.setAttribute('title', isVisible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    const navbar = document.querySelector('#navbar');

    if (!navbar) {
        return;
    }

    const setSubmenuOpen = (menu, open) => {
        menu.classList.toggle('is-open', open);
        menu.querySelector(':scope > .submenu-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const closeSubmenus = (except = null) => {
        navbar.querySelectorAll('[data-submenu].is-open').forEach((menu) => {
            if (menu !== except) {
                setSubmenuOpen(menu, false);
            }
        });
    };

    navbar.querySelector('.navbar-toggle')?.addEventListener('click', (event) => {
        const toggle = event.currentTarget;
        const open = navbar.classList.toggle('is-open');

        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    navbar.addEventListener('click', (event) => {
        const toggle = event.target.closest('.submenu-toggle');

        if (!toggle || !navbar.contains(toggle)) {
            return;
        }

        const menu = toggle.closest('[data-submenu]');

        if (!menu) {
            return;
        }

        const open = !menu.classList.contains('is-open');

        closeSubmenus(menu);
        setSubmenuOpen(menu, open);
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('#navbar [data-submenu]')) {
            return;
        }

        closeSubmenus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSubmenus();
        }
    });
});
