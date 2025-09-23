$(document).ready(function () {

    function dsptime() {
        let heure;
        let date = new Date();
        h = date.getHours();
        let m = date.getMinutes();
        let s = date.getSeconds();
        let hh = date.getHours();
        h = h > 12 ? h % 12 : h;

        heure = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s + (hh < 12 ? ' am' : ' pm');
        document.querySelector('#time').innerHTML = heure;
        return heure;
    }
    if(document.querySelector('#time')!== null)dsptime();


        let gocode = $('#goaccess'), codeclose = $('#closecode'), codewind = $('#codeaccess');
        gocode.on('click', function () {
        codewind.data('state', !codewind.data('state'));
        codewind.toggle("slide:right");
    });
        codeclose.on('click', function () {
        codewind.data('state', !codewind.data('state'));
        codewind.toggle("slide:right");
    });


});


/*
document.addEventListener("DOMContentLoaded", function () {
    // ✅ Sélectionne les éléments du menu utilisateur
    let btUser = document.getElementById("nav_user");
    let btClose = document.getElementById("nav_close");
    let pUser = document.getElementById("popuser");
    //let popUser = document.getElementById("popuser");
    let menuHO = document.getElementById('ptn_menu');
    let logoTop = document.getElementById('logotop');
    let logoSmall = document.getElementById("logosmall");
    const btnOpen = document.getElementById("goaccess");
    const btnClose = document.getElementById("closecode");
    const popup = document.getElementById("codeaccess");


    if (btnOpen && btnClose && popup) {
        btnOpen.addEventListener("click", (event) => {
            event.stopPropagation(); // Empêche la propagation du clic
            if (popup.style.display === "none" || popup.style.display === "") {
                popup.style.display = "block";
            }
        });

        btnClose.addEventListener("click", (event) => {
            event.stopPropagation();
            popup.style.display = "none";
        });

        // Fermer en cliquant en dehors de la popup
        document.addEventListener("click", (event) => {
            if (!popup.contains(event.target) && event.target !== btnOpen) {
                popup.style.display = "none";
            }
        });
    }

    if (btUser && pUser) {
        btUser.addEventListener("click", function (event) {
            console.log('clic',pUser)
            event.stopPropagation();
            //pUser.classList.toggle("menu-open");
            if (pUser.style.display === "none" || pUser.style.display === "") {
                pUser.style.display = "block";
            } else {
                pUser.style.display = "none";
            }
        });

        btClose.addEventListener("click", function () {
            pUser.classList.remove("menu-open");
        });

        // ✅ Ferme si on clique en dehors
        document.addEventListener("click", function (event) {
            if (!pUser.contains(event.target) && event.target !== btUser) {
                pUser.classList.remove("menu-open");
            }
        });
    }
/*
    if (btUser && popUser) {
        btUser.addEventListener("click", function (event) {
            event.stopPropagation();
            popUser.style.display = (popUser.style.display === "none" || popUser.style.display === "") ? "block" : "none";
        });

        // Ferme si on clique en dehors
        document.addEventListener("click", function (event) {
            if (!popUser.contains(event.target) && event.target !== btUser) {
                popUser.style.display = "none";
            }
        });
    }

    // ✅ Ajoute `passive: true` au scroll pour éviter le warning
    window.addEventListener("scroll", function () {
        let posi = window.scrollY;

        if (posi >= 70) {
            menuHO.classList.add("fixed-header");
            logoTop.classList.add("small-logo");
            logoSmall.style.display = "block";  // Affiche le petit logo
        } else {
            menuHO.classList.remove("fixed-header");
            logoTop.classList.remove("small-logo");
            logoSmall.style.display = "none";  // Cache le petit logo
        }
    }, { passive: true });




});
*/
document.addEventListener("DOMContentLoaded", function () {
    let header = document.getElementById("main-header");
    let logo = document.getElementById("logo");
    let userButton = document.getElementById("nav_user");
    let userDropdown = document.getElementById("user-dropdown");


    // Effet de scroll pour réduire le header et le logo
    window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
            document.body.classList.add("scrolled");
        } else {
            document.body.classList.remove("scrolled");
        }
    });

    // Toggle du menu utilisateur
    if (userButton && userDropdown) {
        userButton.addEventListener("click", function (event) {
            event.stopPropagation();
            userDropdown.style.display = (userDropdown.style.display === "none" || userDropdown.style.display === "") ? "block" : "none";
        });

        document.addEventListener("click", function (event) {
            if (!userDropdown.contains(event.target) && event.target !== userButton) {
                userDropdown.style.display = "none";
            }
        });
    }

});
