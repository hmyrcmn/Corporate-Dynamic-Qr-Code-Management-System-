import './bootstrap';

const storageKey = 'dynamicqr-theme';

const applyTheme = (theme) => {
    const root = document.documentElement;
    const isDark = theme === 'dark';
    const themeColor = isDark ? '#071316' : '#f4f7f8';

    root.dataset.theme = isDark ? 'dark' : 'light';
    root.classList.toggle('dark', isDark);
    root.style.colorScheme = isDark ? 'dark' : 'light';

    document.querySelectorAll('[data-theme-label]').forEach((label) => {
        label.textContent = isDark ? 'Acik tema' : 'Koyu tema';
    });

    document.querySelectorAll('[data-theme-icon="light"]').forEach((icon) => {
        icon.classList.toggle('hidden', isDark);
    });

    document.querySelectorAll('[data-theme-icon="dark"]').forEach((icon) => {
        icon.classList.toggle('hidden', !isDark);
    });

    document.querySelectorAll('#theme-color-meta').forEach((meta) => {
        meta.setAttribute('content', themeColor);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem(storageKey);
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const initialTheme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : systemTheme;

    applyTheme(initialTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem(storageKey, nextTheme);
            applyTheme(nextTheme);
        });
    });

    const publicHeader = document.querySelector('[data-public-header]');

    if (publicHeader) {
        let lastScrollY = window.scrollY;
        let ticking = false;

        const syncHeader = () => {
            const currentScrollY = window.scrollY;
            const shouldHide = currentScrollY > 64 && currentScrollY > lastScrollY;

            publicHeader.classList.toggle('public-header-hidden', shouldHide);
            lastScrollY = currentScrollY;
            ticking = false;
        };

        window.addEventListener(
            'scroll',
            () => {
                if (!ticking) {
                    window.requestAnimationFrame(syncHeader);
                    ticking = true;
                }
            },
            { passive: true },
        );
    }

    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    if (sidebar && overlay) {
        const closeSidebar = () => {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-[120%]');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const openSidebar = () => {
            sidebar.classList.remove('-translate-x-[120%]');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                if (sidebar.classList.contains('translate-x-0')) {
                    closeSidebar();
                    return;
                }

                openSidebar();
            });
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    }
});
