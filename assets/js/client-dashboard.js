document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.client-sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const menuToggle = document.querySelector('.mobile-menu-button');

    function openSidebar() {
        if (!sidebar) return;

        sidebar.classList.add('is-open');

        if (sidebarOverlay) {
            sidebarOverlay.classList.add('is-visible');
        }

        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (!sidebar) return;

        sidebar.classList.remove('is-open');

        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('is-visible');
        }

        document.body.classList.remove('sidebar-open');
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if (sidebar && sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 920) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 920) {
            closeSidebar();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Scroll Reveal Animation
    |--------------------------------------------------------------------------
    */

    const animationElements = [];

    const hero = document.querySelector('.client-hero');

    if (hero) {
        animationElements.push({
            element: hero,
            animation: 'reveal-up'
        });
    }

    const serviceSection = document.querySelector('.services-section');

    if (serviceSection) {
        animationElements.push({
            element: serviceSection,
            animation: 'reveal-left'
        });
    }

    const providerSection = document.querySelector('.providers-section');

    if (providerSection) {
        animationElements.push({
            element: providerSection,
            animation: 'reveal-right'
        });
    }

    animationElements.forEach(function (item) {
        item.element.classList.add('scroll-reveal');
        item.element.classList.add(item.animation);
    });

    /*
    |--------------------------------------------------------------------------
    | Service Cards Stagger
    |--------------------------------------------------------------------------
    */

    const serviceCards = document.querySelectorAll('.service-card');

    serviceCards.forEach(function (card, index) {
        card.classList.add('scroll-reveal');
        card.classList.add('reveal-up');
        card.style.setProperty(
            '--animation-delay',
            `${index * 80}ms`
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Provider Cards Stagger
    |--------------------------------------------------------------------------
    */

    const providerCards = document.querySelectorAll('.provider-card');

    providerCards.forEach(function (card, index) {
        card.classList.add('scroll-reveal');

        if (index % 3 === 0) {
            card.classList.add('reveal-left');
        } else if (index % 3 === 1) {
            card.classList.add('reveal-up');
        } else {
            card.classList.add('reveal-right');
        }

        card.style.setProperty(
            '--animation-delay',
            `${index * 110}ms`
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Intersection Observer
    |--------------------------------------------------------------------------
    */

    const revealElements = document.querySelectorAll('.scroll-reveal');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            function (entries, observerInstance) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const element = entry.target;

                    const delay = element.style.getPropertyValue(
                        '--animation-delay'
                    );

                    if (delay) {
                        setTimeout(function () {
                            element.classList.add('is-visible');
                        }, parseInt(delay));
                    } else {
                        element.classList.add('is-visible');
                    }

                    observerInstance.unobserve(element);
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            }
        );

        revealElements.forEach(function (element) {
            observer.observe(element);
        });
    } else {
        revealElements.forEach(function (element) {
            element.classList.add('is-visible');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Favorite Button UI
    |--------------------------------------------------------------------------
    */

    const favoriteButtons = document.querySelectorAll(
        '.favorite-provider'
    );

    favoriteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const icon = button.querySelector('i');

            if (!icon) return;

            if (icon.classList.contains('fa-regular')) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');

                button.classList.add('is-favorite');
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');

                button.classList.remove('is-favorite');
            }
        });
    });
});