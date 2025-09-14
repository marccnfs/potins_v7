$(document).ready(function () {
    let hdiv;
    let head;
    let headerdk=$('.nav-afdk');
    let navdk=$('#af_navdk');
    let nav= $('#af_nav');
    let inf=$('.wb-inf-');
    let _pg=$('.ii_switch').attr('data-js')==='cu';
    let agent = inf.attr("data-agent") !== "mobile/";
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

    // variables pour le plugin placernotice :
    let panneau=$('.flex-notb_v6-fix');
    let replace = inf.attr("data-replacejs");
    let notices = $('.last-postall_v6-fix');

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


            if(msg.length !== 0) {
                let h2 = hdk+110;
                msg.scrollTop($('.hscrool').prop("scrollHeight"));
                msg.animate({opacity: 1}, 1000);
                if(convers){
                   // h2 += 150;// le conversall
                }
                msg.css('height', hauteur-h2 + 'px');
            }
            if (replace !== "no") panneau.placernotice(notices); // plugin sur panneau
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
        dk.scroll(function(){
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
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        scrl.removeClass('scrl_hdn')
    });

    let stat={
        1:false,
        2:false
    };
    let btnmenu=$('#menuaffi'),  btpop=$('#nav_pop'),  btuser=$('#nav_user'), btspace=$('#nav_space');
    let menu=$('#glassmenu'), pop=$('#popp'), puser=$('#popuser');

    btnmenu.on('mouseover', function(){
        btnpop(menu,1)
    });

    btuser.on('click', function(){
        puser.data('state',!puser.data('state'));
        puser.toggle("slide:right");
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


    function selectpg(pg){
        tooglenav($('[data-affitgl='+tabtoggle+']'))
    }

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
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bulle-act');
        sel.removeClass('bt-of').addClass('_bulle-act');
        exectoogle(sel.data("affitgl"))
    }

    $('.affitoggle').on('click', function(e){
        e.stopPropagation();
        tooglenav($(this))
    });

    function exectoogle(tgl){
        tabtoggle=tgl;
        if(agent){
            if(tabtoggle ==="tgl_0"){
                if(!headerdk.data("slide")){
                    headerslide(hdk,true)
                }
            }
            if(tabtoggle ==="tgl_2"){
                $('.cargo').css('background-color',' #175fad')
                initbull();
            }else {
                $('.cargo').css('background-color',' #e4eaf1')
            }
        }
        if(tabtoggle ==="tgl_newmsg") {
            $('#afftgl').hide("slow")
        }
        $('#'+tabtoggle).show()
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

});



