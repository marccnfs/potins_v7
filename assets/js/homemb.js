const animateCSS = (element, animation, prefix = 'animate__') =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {
        const animationName = `${prefix}${animation}`;
        const node = document.querySelector(element);
        node.classList.add(`${prefix}animated`, animationName);
        function handleAnimationEnd() {
            node.classList.remove(`${prefix}animated`, animationName);
            node.removeEventListener('animationend', handleAnimationEnd);
            resolve('Animation ended');
        }
        node.addEventListener('animationend', handleAnimationEnd);
    });

$(document).ready(function(){
    var domaine=$('#_desktop_main');
    var menuflat=$('.user_menu2');
    var menubarre1=$('._header-main--cross_hatch');
    let ani1=0, ani2=0,ani3=0,ani4=0,ani5=0;
    var navsecondary=$('._header-nav--secondary')
    $('[data-menu]').on('click',function(event){
        navsecondary.css('left', '0px').addClass('active-ttr');
        event.stopPropagation();
    });
    $('#page').on('click',function(event){
        if(navsecondary.hasClass('active-ttr')){
            navsecondary.css('left', '-150%');
            navsecondary.removeClass('active-ttr');
            event.stopPropagation();
        }else{
        }
    });
    $(window).scroll(function() {
        let scrolllimit = ($(window).scrollTop());
        if(scrolllimit>50){
            domaine.css("opacity", 0);
            domaine.css("font-size", "1.2em");
            domaine.css("color", "#f11068");
            menuflat.css("height", "50px");
            menuflat.css("background-color", "transparent");
            menuflat.css("z-index", "1000");
            menuflat.removeClass('shadow-large');
            menubarre1.removeClass('_hatch1').addClass('_hatch2')
            menubarre1.css("background-color", "#f11068");
        }
        /*
        if(scrolllimit>832){
            if(ani1===0){
                ani1=1;
                animateCSS('.rub1','fadeInLeftBig').then((message) => {
                });
            }
        }
        if(scrolllimit>1358){
            if(ani2===0){
                animateCSS('.rub2','fadeInRightBig').then((message) => {
                    ani2=1;
                });
            }
        }
        if(scrolllimit>1820){
            if(ani3===0){
                animateCSS('.rub3','fadeInLeftBig').then((message) => {
                    ani3=1;
                });
            }
        }
        if(scrolllimit>2332){
            if(ani4===0){
                animateCSS('.rub4','fadeInRightBig').then((message) => {
                    ani4=1;
                });
            }
        }
        if(scrolllimit>2970){
            if(ani5===0){
                animateCSS('.footer-contact-title2','zoomIn').then((message) => {
                    ani5=1;
                });
            }
        }

         */
        if(scrolllimit<50){
            domaine.css("opacity", 1);
            domaine.css("font-size", "1.5em");
            domaine.css("color", "#f11068");
            menuflat.css("height", "60px");
            menuflat.css("background-color", "#fff");
            menuflat.css("z-index", "500");
            menuflat.addClass('shadow-large');
            menubarre1.css("background-color", "#f11068");
            menubarre1.removeClass('_hatch2').addClass('_hatch1')
        }
    });
});