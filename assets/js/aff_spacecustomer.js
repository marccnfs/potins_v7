$(document).ready(function () {
    let hdiv;
    let head;
    let headerdk=$('.nav-afdk');
    let navdk=$('#af_navdk');
    let nav= $('#af_nav');
    let inf=$('.wb-inf-');
    let _pg=$('.ii_switch').attr('data-js')==='cu';
    let agent = inf.attr("data-agent") !== "mobile/";  //ici agent true c'est desk/ ??
    let scrl = $('._scrollflow');
    let mb = $('._mb');
    let dk = $('._dk');
    let msg = $('._msg');
    let convers = $('._convers').length !== 0;;
    let h;
    let hdk;
    let tabtoggle="tgl_0";
    let hauteur;
    let largeur;
    let navbulle =$('.space_grid_tags').data("slide", true);
    let headbull=$('.head-bull');
    // variables pour le plugin placernotice :
    let panneau=$('.flex-notb_v6-fix');
    let replace = inf.attr("data-replacejs");
    let notices = $('.last-postall_v6-fix');

    /* local storage */
    const rememberDiv = document.querySelector('.pop_backgange');
    function helpDisplayCheck() {
        if(localStorage.getItem('help')) {
            let help = localStorage.getItem('help');
            rememberDiv.style.display = 'none';
        } else {
            rememberDiv.style.display = 'block';
        }
    }
    if(rememberDiv) document.body.onload = helpDisplayCheck;
    $('#check-help').on('click', function (e){
        if($(this).is(":checked")){
            localStorage.setItem('help','yes');
            rememberDiv.style.display = 'none';
        }
    })

    $('.abs-croos').on('click', function (e) {
        let pop=$(this).parent().hide();
    })

    $('#buller').on('click', function (e){
        rememberDiv.style.display = 'block';
        if(localStorage.getItem('help')) {
            let help = localStorage.removeItem('help');
        }
    })

    if (replace !== "no") panneau.placernotice(notices); // plugin sur panneau

    function calwindows() {
        hauteur = window.innerHeight;
        largeur = window.innerWidth;

        if (!agent){
            head = $('.nav-af').height() || 66;
            nav.data("slide", false);
            if(_pg) hdiv=50;
            if (nav.length!==0) hdiv = nav.height() || 0;
            h = head + hdiv;
            mb.css('width', largeur + 'px');
            calscrollmb(h)
            if(msg.length !== 0) {
                calscrollmb(h)
                msg.scrollTop($('.hscrool').prop("scrollHeight"));
                msg.animate({opacity: 1}, 1000);
            }
        } else {
            headerdk.data("slide", true);
            head = headerdk.height() || 0;
            hdiv = navdk.height() || 0;
            //hdk=pg==="hm"? head + hdiv + (hauteur*10/100) +20: head + hdiv + 20;   // todo revoir
            //calscrolldk(hdk)  // todo revoir

            if (replace !== "no") panneau.placernotice(notices); // plugin sur panneau

            if(msg.length !== 0) {
                let h2 = hdk+110;
                msg.scrollTop($('.hscrool').prop("scrollHeight"));
                msg.animate({opacity: 1}, 1000);
                if(convers){
                   // h2 += 150;// le conversall
                }
                msg.css('height', hauteur-h2 + 'px');
            }
        }
    }

    function calscrolldk(h) {
        dk.css('height', (hauteur-h) + 'px');
    }

    function calscrollmb(h) {
        mb.css('height', hauteur - h + 'px');
    }

    calwindows();

    // suppression de la barre d'actions colcenter mobile (openday...) ou nav center
    if(!agent){
        mb.scroll(function(){
            let scrolllimit=mb.scrollTop();
            if(scrolllimit>= hdiv){
                if (!nav.data("slide"))coldata(true)
            }
            if(scrolllimit=== 0)coldata(false)
        });
    }

    // suppression de la barre d'actions colcenter mobile (openday...) ou nav center
    if(agent){
        window.addEventListener('resize', calwindows);
       /* dk.scroll(function(){
            if(tabtoggle!=="tgl_0"){
                let scrolllimit=dk.scrollTop();
                if(scrolllimit>=150 && headerdk.data("slide")){
                    headerslide(hdiv+25,false)
                }
                if(scrolllimit<150){
                    if(!headerdk.data("slide")){
                        headerslide(hdk,true)
                    }
                }
            }
        });

        */

        /*
        $(window).scroll(function(){
            let scrolllimit2=$(window).scrollTop();
            if(scrolllimit2>=50 && navbulle.data("slide")){
                navbulle.data("slide", false)
                navbulle.slideToggle();
            }
            if(scrolllimit2<50){
                if(!navbulle.data("slide")){
                    navbulle.data("slide", true)
                    navbulle.slideToggle();
                }
            }
        });
         */
        $(window).scroll(function(){
            let scrolllimit=$(window).scrollTop();
            if(scrolllimit>=80){
                headbull.hide();
            }
            if(scrolllimit<80){
                headbull.show();
            }
        });

    }

    function headerslide(h,state){
        headerdk.data("slide", state)
        calscrolldk(h)
        headerdk.slideToggle("swing");
    }

    function coldata(state){
        if(!nav.data("slide") ===state){
            nav.slideToggle("swing");
            nav.data("slide", state)
        }
    }

    if(tabtoggle!=="tgl_0")selectpg(tabtoggle);

    $('textarea').each(function () {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;');
    }).on('input', function () {
        console.log("input texte")
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        scrl.removeClass('scrl_hdn')
    });

    let stat={
        1:false,
        2:false
    };
    let btnmenu=$('#menuaffi'), btpop=$('#nav_pop'), btspace=$('#nav_space');
    let menu=$('#glassmenu'), pop=$('#popp');

    btnmenu.on('mouseover', function(){
        btnpop(menu,1)
    });

    btpop.on('click mouseover', function(){
        btnpop(pop,2)
    });

    let btnpop=function(m,st){
        m.toggle()
        if(st===1){
            if(stat[st]){
                if(!agent)pg.toogle()
                btnmenu.css({
                    transform: 'rotate(180deg)',
                    color: 'white'
                })
                stat[st]=false
            }else{
                if(!agent)pg.toogle()
                btnmenu.css({
                    transform: 'rotate(-180deg)',
                    color: 'green'
                })
                stat[st]=true
            }
        }else{
            stat[st] = !stat[st];
        }
    };


    $('.eva_buble').on('mouseover', function(){
        $('.hov-eva').css("opacity","1")
        setTimeout(function(){
            $('.hov-eva').css("opacity","0");
        }, 1000);
    });

    function selectpg(pg){
        tooglenav($('[data-affitgl='+tabtoggle+']'))
    }

    $('.poptoggle').on('click', function(e){
        e.stopPropagation();
        $('.poptglaff').hide();
        $('[data-poptgl]').addClass('bt-ofpop').removeClass('_bta-actpop');
        $(this).removeClass('bt-ofpop').addClass('_bta-actpop');
        let tabpop = $(this).data("poptgl")
        puser.toggle()
        $('#'+tabpop).show()
    });

    function tooglenav(sel) {
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bta-act');
        sel.removeClass('bt-of').addClass('_bta-act');
        exectoogle(sel.data("affitgl"))
    }

    $('.bulletoggle').on('click', function(e){
        e.stopPropagation();
        bulletooglenav($(this))
    });

    function bulletooglenav(sel) {
        let tog =sel.data("affitgl")
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bulle-act');
        if(tog === "tgl_10" || tog === "tgl_5"){
            sel.removeClass('bt-of');
        }else{
            sel.removeClass('bt-of').addClass('_bulle-act');
        }
        exectoogle(tog)
    }

    $('.affitoggle').on('click', function(e){
        e.stopPropagation();
        tooglenav($(this))
    });

    function exectoogle(tgl){
        tabtoggle=tgl;
        if(tabtoggle ==="tgl_10") tabtoggle ="tgl_0"
        if(agent){
            if(tabtoggle ==="tgl_0"){
                if(!headerdk.data("slide")){
                    headerslide(hdk,true)
                }
            }
            if(tabtoggle ==="tgl_2"){
                $('.cargo').css('background-color',' #175fad')
                //initbull() todo
            }else {
                $('.cargo').css('background-color',' #060662')
            }
            if(tabtoggle ==="tgl_5"){
                $('.head-bull').css('opacity',' 0')
                //initbull() todo
            }else {
                $('.head-bull').css('opacity',' 1')
            }
        }
        if(tabtoggle ==="tgl_newmsg") {
            $('#afftgl').hide("slow")

        }
        $('#'+tabtoggle).fadeIn(800)
    }




});



