$(document).ready(function () {
    let hdiv;
    let head;
    let headerdk=$('.nav-afdk');
    let navdk=$('#af_navdk');
    let nav= $('#af_nav');
    let navaf=$('.nav-af');
    let inf=$('.wb-inf-');
    let _pg=$('.ii_switch').attr('data-js')==='wb';
    let agent = inf.attr("data-agent") !== "mobile/";
    let scrl = $('._scrollflow');
    let mb = $('._mb');
    let dk = $('._dk');
    let msg = $('._msgid');
    let h;
    let hdk;
    let headbull=$('.head-bull');
    let tabtoggle="tgl_0";
    let hauteur;
    let largeur;
    let stat={
        1:false,
        2:false
    };
    let btnmenu=$('#menuaffi'),  btpop=$('#nav_pop'),  btuser=$('#nav_user'), btspace=$('#nav_space');
    let space1=$('#spc1'), menu=$('#glassmenu'), pop=$('#popp'), puser=$('#popuser');
    let shtoff=$('.sht'),shton=$('.shtf');
    let navwb=$('#nav_wb'),navwbmb=$('#nav_wb_mb'),navwBtmb=$('#navBt_mb'), apropo=$('#aprowb'), navwbBt=$('#genery'), contapropo=$('#bkapop');

    // variables pour le plugin placernotice :
    let panneau=$('.flex-notb_v6-fix');
    let replace = inf.attr("data-replacejs");
    let notices = $('.last-postall_v6-fix');

    if (replace !== "no") panneau.placernotice(notices); // plugin sur panneau

    function calwindows() {
        hauteur = window.innerHeight;
        largeur = window.innerWidth;

        if (!agent){
            head = navaf.height() || 70;
            nav.data("slide", false); //affiché
            navdk.data("slide", false);
            if(_pg)hdiv=50;
            if (nav.length!==0) hdiv = nav.height() || 0; //todo verfier su valable avec la consition d'avant si oui
            mb.css('width', largeur + 'px');
        } else {
            headerdk.data("slide", true);
            navdk.data("slide", true);
            head = headerdk.height() || 0;
            hdiv = navdk.height() || 0;

            if (replace !== "no") panneau.placernotice(notices); // plugin sur panneau
        }

        h = head + hdiv;

        if(msg.length !== 0) {
            if (msg.attr('data-answer')) {
                if (!agent) {
                    h = head + hdiv + $('#footmb').height() || 103;
                    $('.mh250').css('min-height', (hauteur - (h - 10)) + 'px') // le-10 pour le padding
                }else{
                    console.log('dk')
                    $('.mh250').css('min-height', (hauteur - (h+150)) + 'px') // le-10 pour le padding
                }
            } else if (msg.attr('data-public')) {
                h = head + hdiv + 80
            } else {
                h = head + hdiv
            }

            if (agent) {
              // h += 120;// le conversall
              //  calscrolldk(h)
            } else {
                calscrollmb(h)
            }
            msg.scrollTop($('.hscrool').prop("scrollHeight"));
            msg.animate({opacity: 1}, 1000);
        }else{
            if (agent) {
              //  hdk=pg==="hm"? head + hdiv + (hauteur*10/100) +20: head + hdiv + 20;   // todo revoir
              //  calscrolldk(hdk)
            }else{
                calscrollmb(h)
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

    $(window).scroll(function(){
        let scrolllimit2=$(window).scrollTop();
        if(scrolllimit2>=70){
           // $('.littlespacevague_lrg_v5').css('background-color','#f8f9fa')
          //  $('.goaff_v6').css('color','#175fad')
          //  $('.affibodyfixed_v5').css('height','70px')
        }
        if(scrolllimit2<70){
         //   $('.littlespacevague_lrg_v5').css('background-color','transparent')
         //   $('.goaff_v6').css('color','#ffffffed')
          //  $('.affibodyfixed_v5').css('height','0px')
        }
    });


    // suppression de la barre d'actions colcenter mobile (openday...) ou nav center
    if(!agent && msg.length === 0){
        mb.scroll(function(){
            let scrolllimit=mb.scrollTop();
            if(scrolllimit>= hdiv){   //scroll sup a hdiv
                if (!nav.data("slide"))coldata(true)  // si false -> envoie true(cache)
            }
            if(scrolllimit=== 0)coldata(false) // ->envoie false (affiche)
        });
    }

    // suppression de la barre d'actions colcenter mobile (openday...) ou nav center
    if(agent){
        window.addEventListener('resize', calwindows);
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
        if(!nav.data("slide") ===state){  // si different de l'etat actuel
            nav.slideToggle("swing");
            nav.data("slide")? calscrollmb(h+hdiv):calscrollmb(h);
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


    let togglebt =function(_bt){
        let child=$(this).children();
        navwbBt.fadeToggle(300)
        if(child.hasClass('fa-bars')) {
            child.removeClass('fa-bars').addClass('fa-minus-square')
        }else{
            child.removeClass('fa-minus-square').addClass('fa-bars')
        }
    }

    navwb.on('click', function(e){
        e.stopPropagation();
        togglebt(this);
    });

    navwbmb.on('click', function(e){
        e.stopPropagation();
        let child=$(this).children();
        navwBtmb.fadeToggle(600)
        navwbmb.data('on',true);
    });

    $('.affibody_mob').on('click', function(){
        if(navwbmb.data('on')){
            navwBtmb.fadeToggle(600)
            navwbmb.removeData();
        }
    });

    $('.moregoa').on('click', function(){
        //if($(this).data('on')){
            $('#morinf').fadeToggle(600)
            $('.forplus').toggle()
            $('.formoins').toggle()
          //  navwbmb.removeData();
       // }
    });

    apropo.on('click', function(){
        contapropo.fadeToggle(600)
    });

    space1.state=false;

    btspace.on('click', function(){ //clic sur globe
        console.log('clik')
        space1.fadeToggle(600)
        shtoff.toggle()
        shton.toggle()

    });

    btnmenu.on('click mouseover', function(){
        console.log('clik')
        btnpop(menu,1)
    });

    btuser.on('click', function(){
        console.log('clik')
        puser.slideToggle()
    });

    btpop.on('click mouseover', function(){
        console.log('clik')
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

    btnmenu.hover(
        function() {
            $( this ).css( 'color', 'rgb(255, 69, 0)');
        }, function() {
            $( this ).css( 'color', 'white');;
        }
    );

    $('.eva_buble').on('mouseover', function(){
        $('.hov-eva').css("opacity","1")
        setTimeout(function(){
            $('.hov-eva').css("opacity","0");
        }, 1000);
    });

    function selectpg(pg){
        tooglehome($('[data-affitglh=tgl_publi]'));
        tooglenav($('[data-affitgl='+tabtoggle+']'))
    }

    //toggle affi nav
    $('.affitoggle').on('click', function(e){
        e.stopPropagation();
        tooglenav($(this))
    });

    $('.affitoggle-h').on('click', function(e){
        e.stopPropagation();
        tooglehome($(this))
    });

    $('.affitoggle-foot').on('click', function(e){
        e.stopPropagation();
        tooglefoot($(this))
    });

    $('.affitoggle-left').on('click', function(e){
        e.stopPropagation();
        toogleleft($(this))
    });

    function tooglehome(sel){
        $('.tglaff-h').hide();
        $('[data-affitglh]').addClass('bt-of').removeClass('_bta-act');
        sel.removeClass('bt-of').addClass('_bta-act');
        let tgl =sel.data("affitglh")
        if(tgl === "tgl_home") coldata(false)
        $('#'+tgl).show()
    }

    function tooglefoot(sel){
        $('.tglaff-foot').hide();
        $('[data-affitglfoot]').addClass('nav-of').removeClass('_act-foot');
        sel.removeClass('nav-of').addClass('_act-foot');
        let tgl =sel.data("affitglfoot")
        $('#'+tgl).show()
    }

    function toogleleft(sel){
        $('.tglaff-left').hide();
        $('[data-affitglleft]').addClass('nav-of').removeClass('_act-left');
        sel.removeClass('nav-of').addClass('_act-left');
        let tgl =sel.data("affitglleft")
        $('#'+tgl).show()
    }

    function tooglenav(sel) {
        $('.tglaff').hide();
        $('[data-affitgl]').addClass('bt-of').removeClass('_bta-act');
        sel.removeClass('bt-of').addClass('_bta-act');
        exectoogle(sel.data("affitgl"))
    }

    function exectoogle(tgl){
        tabtoggle=tgl;
        if(agent){
            if(tabtoggle ==="tgl_0"){
                if(!headerdk.data("slide")){
                    headerslide(hdk,true)
                }
                //headerdk.hide("slow")
                //$('#full').show("slow");
                //$('#lit').hide();
               // calscrolldk(210+hdiv)
            }else{
               // headerdk.show("slow")
                //$('#full').hide();
               // $('#lit').show("slow");
                //calscrolldk(118+hdiv)
            }
        }
/*
        if(tabtoggle ==="tgl_newmsg") {
            $('#afftgl').hide("slow")
        }

 */
        $('#'+tabtoggle).fadeIn("slow")
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
