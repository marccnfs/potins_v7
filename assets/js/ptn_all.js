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


document.addEventListener("DOMContentLoaded", function () {
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
