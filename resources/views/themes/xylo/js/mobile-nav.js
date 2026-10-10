document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.getElementById('store-mobile-drawer');
    const overlay = document.getElementById('store-drawer-overlay');
    const openBtn = document.getElementById('store-menu-open');
    const closeBtn = document.getElementById('store-menu-close');
    const searchToggle = document.getElementById('store-search-toggle');
    const searchWrap = document.getElementById('store-search-wrap');

    const setNavOpen = (open) => {
        document.body.classList.toggle('store-nav-open', open);
        drawer?.classList.toggle('is-open', open);
        overlay?.classList.toggle('is-open', open);
    };

    openBtn?.addEventListener('click', () => setNavOpen(true));
    closeBtn?.addEventListener('click', () => setNavOpen(false));
    overlay?.addEventListener('click', () => setNavOpen(false));

    drawer?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setNavOpen(false));
    });

    searchToggle?.addEventListener('click', () => {
        if (!searchWrap) {
            return;
        }
        searchWrap.classList.toggle('is-collapsed');
        if (!searchWrap.classList.contains('is-collapsed')) {
            searchWrap.querySelector('input')?.focus();
        }
    });

    const mobileMq = window.matchMedia('(max-width: 991.98px)');
    const syncSearchVisibility = () => {
        if (!searchWrap) {
            return;
        }
        if (mobileMq.matches) {
            searchWrap.classList.add('is-collapsed');
        } else {
            searchWrap.classList.remove('is-collapsed');
        }
    };
    syncSearchVisibility();
    mobileMq.addEventListener('change', syncSearchVisibility);

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));
});
