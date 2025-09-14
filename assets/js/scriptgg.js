
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

    $('.bulletoggle').on('click', function(e){
        e.stopPropagation();
        bulletooglenav($(this))
    });

    function bulletooglenav(sel) {
        let tog =sel.data("affitgl")
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bulle-act');
        sel.removeClass('bt-of').addClass('_bulle-act');
        $('#'+tog).fadeIn(800)
    }

});
