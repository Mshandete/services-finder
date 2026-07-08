/* =========================================
   SERVICES FINDER - MAIN SCRIPT
========================================= */

/* =========================
   MOBILE MENU TOGGLE
========================= */

const menuBtn = document.querySelector(".menu-btn");
const nav = document.querySelector("nav");

menuBtn.addEventListener("click", () => {
    nav.classList.toggle("active");
});


/* =========================
   SCROLL REVEAL ANIMATION
========================= */

const revealElements = document.querySelectorAll(".reveal");

function revealOnScroll() {
    const windowHeight = window.innerHeight;

    revealElements.forEach((el) => {
        const elementTop = el.getBoundingClientRect().top;
        const showPoint = 120;

        if (elementTop < windowHeight - showPoint) {
            el.classList.add("active");
        }
    });
}

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("load", revealOnScroll);


/* =========================
   STICKY NAVBAR EFFECT
   (change style on scroll)
========================= */

const header = document.querySelector("header");

window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
});


/* =========================
   SMOOTH SCROLL (optional UX)
========================= */

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();

        const target = document.querySelector(this.getAttribute("href"));

        if (target) {
            target.scrollIntoView({
                behavior: "smooth"
            });
        }
    });
});


/* =========================
   SEARCH FORM HANDLING (UI ONLY)
========================= */

const searchForm = document.querySelector(".search-card form");

if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const service = this.querySelector("input[type='text']").value;
        const location = this.querySelectorAll("input")[1].value;

        if (!service || !location) {
            alert("Please enter service and location");
            return;
        }

        // temporary UI action (later connect backend)
        console.log("Searching:", service, location);

        alert(`Searching for ${service} in ${location}`);
    });
}


/* =========================
   PROVIDER CARD HOVER EFFECT (extra polish)
========================= */

const providerCards = document.querySelectorAll(".provider-card");

providerCards.forEach(card => {
    card.addEventListener("mouseenter", () => {
        card.style.transform = "translateY(-10px)";
    });

    card.addEventListener("mouseleave", () => {
        card.style.transform = "translateY(0)";
    });
});