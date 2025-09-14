document.addEventListener("DOMContentLoaded", function () {
    let menuHeader = document.getElementById("main-header");
    let logoTop = document.getElementById("logotop");
    let logoSmall = document.getElementById("logosmall");
    let userButton = document.getElementById("nav_user");
    let userDropdown = document.getElementById("popuser");

    // Toggle du menu utilisateur
    userButton.addEventListener("click", function (event) {
        event.stopPropagation();
        userDropdown.style.display = (userDropdown.style.display === "none" || userDropdown.style.display === "") ? "block" : "none";
    });

    // Ferme le menu utilisateur en cliquant en dehors
    document.addEventListener("click", function (event) {
        if (!userDropdown.contains(event.target) && event.target !== userButton) {
            userDropdown.style.display = "none";
        }
    });

    // Gestion du scroll pour redimensionner le logo
    window.addEventListener("scroll", function () {
        if (window.scrollY >= 70) {
            menuHeader.classList.add("fixed-header");
            logoTop.style.display = "none";
            logoSmall.style.display = "block";
        } else {
            menuHeader.classList.remove("fixed-header");
            logoTop.style.display = "block";
            logoSmall.style.display = "none";
        }
    });
});
