document.addEventListener("DOMContentLoaded", () => {

    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");
    const closeSidebar = document.getElementById("closeSidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");


    function openSidebar() {

        if (!sidebar) return;

        sidebar.classList.add("is-open");

        if (sidebarOverlay) {
            sidebarOverlay.classList.add("is-visible");
        }

        document.body.classList.add("sidebar-open");

        if (menuToggle) {
            menuToggle.setAttribute("aria-expanded", "true");
        }

    }


    function closeSidebarMenu() {

        if (!sidebar) return;

        sidebar.classList.remove("is-open");

        if (sidebarOverlay) {
            sidebarOverlay.classList.remove("is-visible");
        }

        document.body.classList.remove("sidebar-open");

        if (menuToggle) {
            menuToggle.setAttribute("aria-expanded", "false");
        }

    }


    /* =========================================
       OPEN SIDEBAR
    ========================================= */

    if (menuToggle) {

        menuToggle.addEventListener("click", () => {

            if (sidebar.classList.contains("is-open")) {
                closeSidebarMenu();
            } else {
                openSidebar();
            }

        });

    }


    /* =========================================
       CLOSE BUTTON
    ========================================= */

    if (closeSidebar) {

        closeSidebar.addEventListener("click", () => {
            closeSidebarMenu();
        });

    }


    /* =========================================
       CLOSE OVERLAY
    ========================================= */

    if (sidebarOverlay) {

        sidebarOverlay.addEventListener("click", () => {
            closeSidebarMenu();
        });

    }


    /* =========================================
       CLOSE MENU AFTER CLICKING A LINK
       MOBILE ONLY
    ========================================= */

    if (sidebar) {

        const menuLinks =
            sidebar.querySelectorAll(".provider-nav-link");

        menuLinks.forEach((link) => {

            link.addEventListener("click", () => {

                if (window.innerWidth <= 680) {
                    closeSidebarMenu();
                }

            });

        });

    }


    /* =========================================
       CLOSE WITH ESCAPE
    ========================================= */

    document.addEventListener("keydown", (event) => {

        if (event.key === "Escape") {
            closeSidebarMenu();
        }

    });


    /* =========================================
       RESET WHEN RETURNING TO DESKTOP
    ========================================= */

    window.addEventListener("resize", () => {

        if (window.innerWidth > 680) {
            closeSidebarMenu();
        }

    });

});