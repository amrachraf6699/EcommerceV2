const initStorefrontHoverSurfaces = () => {
    document.querySelectorAll('[data-sf-surface]').forEach((surface) => {
        if (surface.dataset.enhanced === 'true') {
            return;
        }

        surface.addEventListener('pointermove', (event) => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const rect = surface.getBoundingClientRect();
            const offsetX = ((event.clientX - rect.left) / rect.width) * 100;
            const offsetY = ((event.clientY - rect.top) / rect.height) * 100;

            surface.style.setProperty('--sf-glow-x', `${offsetX}%`);
            surface.style.setProperty('--sf-glow-y', `${offsetY}%`);
        });

        surface.dataset.enhanced = 'true';
    });
};

const initStorefrontNavigation = () => {
    const drawer = document.querySelector('[data-sf-nav-drawer]');
    const overlay = document.querySelector('[data-sf-nav-overlay]');
    const toggles = document.querySelectorAll('[data-sf-nav-toggle]');
    const closes = document.querySelectorAll('[data-sf-nav-close], [data-sf-section-link]');

    if (!drawer || !overlay || !toggles.length) {
        return;
    }

    const openDrawer = () => {
        drawer.classList.add('is-open');
        overlay.classList.add('is-visible');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sf-nav-open');
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', 'true'));
    };

    const closeDrawer = () => {
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('sf-nav-open');
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));
    };

    toggles.forEach((toggle) => {
        if (toggle.dataset.enhanced === 'true') {
            return;
        }

        toggle.addEventListener('click', () => {
            if (drawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        toggle.dataset.enhanced = 'true';
    });

    closes.forEach((element) => {
        if (element.dataset.drawerEnhanced === 'true') {
            return;
        }

        element.addEventListener('click', () => {
            closeDrawer();
        });

        element.dataset.drawerEnhanced = 'true';
    });

    if (overlay.dataset.enhanced !== 'true') {
        overlay.addEventListener('click', closeDrawer);
        overlay.dataset.enhanced = 'true';
    }

    if (document.body.dataset.sfNavEscapeEnhanced !== 'true') {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        });

        document.body.dataset.sfNavEscapeEnhanced = 'true';
    }
};

const initSectionNavStates = () => {
    const sectionLinks = Array.from(document.querySelectorAll('[data-sf-section-link]'));
    const trackedIds = sectionLinks
        .map((link) => link.getAttribute('href'))
        .filter((href) => href?.startsWith('#'))
        .map((href) => href.slice(1));

    if (!trackedIds.length) {
        return;
    }

    const sections = trackedIds
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    const setActiveLink = (id) => {
        sectionLinks.forEach((link) => {
            const href = link.getAttribute('href');
            link.classList.toggle('is-active', href === `#${id}`);
        });
    };

    if (sections.length) {
        const observer = new IntersectionObserver((entries) => {
            const visibleEntry = entries
                .filter((entry) => entry.isIntersecting)
                .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];

            if (visibleEntry?.target?.id) {
                setActiveLink(visibleEntry.target.id);
            }
        }, {
            rootMargin: '-25% 0px -55% 0px',
            threshold: [0.2, 0.45, 0.7],
        });

        sections.forEach((section) => observer.observe(section));
    }

    const currentHash = window.location.hash.replace('#', '');

    if (currentHash) {
        setActiveLink(currentHash);
    }

    sectionLinks.forEach((link) => {
        if (link.dataset.sectionEnhanced === 'true') {
            return;
        }

        link.addEventListener('click', () => {
            const href = link.getAttribute('href');

            if (href?.startsWith('#')) {
                setActiveLink(href.slice(1));
            }
        });

        link.dataset.sectionEnhanced = 'true';
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initStorefrontHoverSurfaces();
    initStorefrontNavigation();
    initSectionNavStates();
});

window.StorefrontUi = {
    initStorefrontHoverSurfaces,
    initStorefrontNavigation,
    initSectionNavStates,
};
