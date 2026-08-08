// Function ya Toggle Mobile Menu
function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.toggle('active');
}

// Bottom-Up Scroll Animations
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.animate-bottom');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            }
        });
    }, observerOptions);

    animatedElements.forEach(el => scrollObserver.observe(el));
});