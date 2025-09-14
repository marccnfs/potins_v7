$(document).ready(function () {

    /*
    let inf = $('.wb-inf-');
    let _pg = $('.ii_switch').attr('data-js') === 'cu';
    let agent = inf.attr("data-agent") !== "mobile/";  //ici agent true c'est desk/ ??
    let notices = null;
    */

    // navigation pop notif :
    document.addEventListener("DOMContentLoaded", function () {
        let btnNotif = document.getElementById("nav_notifs");
        let pnotif = document.getElementById("popnotif");

        if (btnNotif && pnotif) {
            btnNotif.addEventListener("click", () => {
                pnotif.classList.toggle("active"); // ✅ Utilisation d'une classe CSS pour le slide
            });
        }
    });

    // navigation pop menu horizontal :
    let btuser = $('#nav_user'), btclose = $('#nav_close'), puser = $('#popuser');
    btuser.on('click', function () {
        puser.data('state', !puser.data('state'));
        puser.toggle("slide:right");
    });
    btclose.on('click', function () {
        puser.data('state', !puser.data('state'));
        puser.toggle("slide:right");
    });


    //gestion de l'access avec code' :

    let gocode = $('#goaccess'), codeclose = $('#closecode'), codewind = $('#codeaccess');
    gocode.on('click', function () {
        codewind.data('state', !codewind.data('state'));
        codewind.toggle("slide:right");
    });
    codeclose.on('click', function () {
        codewind.data('state', !codewind.data('state'));
        codewind.toggle("slide:right");
    });



    document.addEventListener("DOMContentLoaded", function () {
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
    });


    // toogle menu :
    let accordion = $("[data-toogle-accordion]")
    if (accordion.length > 0) {
        accordion.on('click', function (e) {
            e.stopPropagation();
            let link = $(this).data('toogle-accordion')
            let lilink = $("[data-element-toogle=" + link + "]")
            if (lilink.data('gnav-action') === "open") {
                lilink.removeClass('AccordionList-collapsed')
                lilink.data('gnav-action', 'close')
            } else {
                lilink.addClass('AccordionList-collapsed')
                lilink.data('gnav-action', 'open')
            }
        });
    }

    // navigation pop kebabMenu-button :
    let kebab=$('.kebabMenu-button'),  kebabMenu=$('.kebabMenu-items');
    kebab.on('click', function(){
        kebabMenu.data('state',!kebabMenu.data('state'));
        kebabMenu.toggle("slide:right");
    })

    // navigation bullecargo :
    let btcargo=$('#shwlaff'),  cargobulle=$('#tgl_b');
    btcargo.on('click', function(){
        cargobulle.data('state',!cargobulle.data('state'));
        cargobulle.toggle();
    });

    // navigation bullecatch :
    let btcatch=$('#catchaffi'),  catchbulle=$('#tgl_catch');
    btcatch.on('click', function(){
        catchbulle.data('state',!catchbulle.data('state'));
        catchbulle.toggle();
    });

    // toogle board publication :
    let tbnav=$('.nav-bar')
    tbnav.on('click', function(){
        if(!$(this).hasClass('baract-on')){ //si off
            let tog=$(this).data('navbar')
            let bar=$('#bar-'+tog)
            tbnav.removeClass('baract-on').addClass('baract-of')
            $(this).removeClass('baract-of').addClass('baract-on')
            $('.onglaff-bar').hide()
            bar.show()
        }
    });

    //toogle shop bouton acheter
    $('.moregoa').on('click', function(){
        $('#morinf').fadeToggle(600)
    });

    window.addEventListener("scroll", function () {
        let posi = window.scrollY;
        let menuHO = document.getElementById('ptn_menu');
        let logo = document.getElementById('logotop');

        if (posi >= 70) {
            menuHO.classList.add('border_men');
            logo.classList.remove('zz_top-top');
            logo.classList.add('zz_top-top-fix');
        } else {
            menuHO.classList.remove('border_men');
            logo.classList.remove('zz_top-top-fix');
            logo.classList.add('zz_top-top');
        }
    }, { passive: true }); // ✅ Ajout du paramètre passive

    // toogle affi
    let tabtoggle="tgl_0";

    $('.bulletoggle').on('click', function(e){
        e.stopPropagation();
        bulletooglenav($(this))
    });

    $('.affitoggle').on('click', function(e){
        e.stopPropagation();
        affitooglenav($(this))
    });

    $('.buttontoggle').on('click', function(e){
        e.stopPropagation();
        buttontooglenav($(this))
    });

    // toogle button swith-tgll :
    let swiths = $("[data-switch]")
    if (swiths.length > 0) {
        swiths.on('click', function (e) {
            e.stopPropagation();
            let tog = $(this).data('switch')
            if ($(this).data('action') === "sh") {
                $('.tglaff').hide();
                swiths.removeClass('no-switch')
                swiths.data('action', "sh")
                $(this).addClass('no-switch')
                $(this).data('action', "nh")
                $('#'+tog).fadeIn(800)
            }
        });
    }

    // menu button swith-tgll :
    let textswiths = $("[data-textswitch]")
    if (textswiths.length > 0) {
        textswiths.on('click', function (e) {
            e.stopPropagation();
            let tog = $(this).data('textswitch')
            if ($(this).data('action') === "sh") {
                $('.tglaff').hide();
                textswiths.removeClass('shtext-switch')
                textswiths.data('action', "sh")
                $(this).addClass('shtext-switch')
                $(this).data('action', "nh")
                $('#'+tog).fadeIn(800)
            }
        });
    }
    // button va-et-viens avec close modal :
    let tglswitch = $("[data-tglswitch]")
    if (tglswitch.length > 0) {
        tglswitch.on('click', function (e) {
            e.stopPropagation();
            let tog = $(this).data('tglswitch')
            if ($(this).data('action') === "sh") {
                tglswitch.removeClass('no-switch')
                tglswitch.data('action', "sh")
                $(this).addClass('no-switch')
                $(this).data('action', "nh")
                $('#'+tog).toggle('slow');
            }
        });
    }

    function buttontooglenav(sel) {
        let tog =sel.data("affitgl")
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bulle-act');
        sel.removeClass('bt-of').addClass('_bulle-act');
        $('#'+tog).fadeIn(800)
    }

    function affitooglenav(sel) {
        let tog =sel.data("affitgl")
        $('.tglaff').hide();
        $('#'+tog).fadeIn(800)
    }

    function bulletooglenav(sel) {
        let tog =sel.data("affitgl")
        if(tog === "tgl_9"){ //conversation
            $('#'+tog).toggle('slow')
        }
        else if(tog === "tgl_7"){ //menu website (customer)
            $('#'+tog).toggle('slow')
        }
        else if(tog === "tgl_info"){ //menu website (customer)
            $('#'+tog).toggle('slow')
        }
        else{
            $('.tglaff').hide();
            $('[data-affitgl]').addClass('bt-of').removeClass('_bulle-act');
            if(tog === "tgl_10" || tog === "tgl_5"){
                sel.removeClass('bt-of');
            }else{
                sel.removeClass('bt-of').addClass('_bulle-act');
            }
            exectoogle(tog)
        }
    }

    function exectoogle(tgl){
        tabtoggle=tgl;
        if(tabtoggle ==="tgl_10") tabtoggle ="tgl_0"
        $('#'+tabtoggle).fadeIn(800)
    }

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

    document.querySelectorAll('.suiv_data').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.stopPropagation();
            let data = { id: button.getAttribute("data-suiv") };

            try {
                let response = await fetch('/sp/msg/notifications/change-state-read', {
                    method: 'POST',
                    body: JSON.stringify(data),
                    headers: { 'Content-Type': 'application/json' }
                });

                let result = await response.json();
                console.log("Notification mise à jour:", result);
            } catch (error) {
                console.error("Erreur lors de la mise à jour de la notification:", error);
            }
        });
    });


});