const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);
let action="";
let listdat={};
let liselected=false;
let listinner=[];
let selectedd=false;
let lik=false;

jQuery(function($){
    var _url=(location.protocol==='http:'?'http:':'https:')+'//geo.api.gouv.fr/communes';
    var _3={};
    var _10=function(a,b){return a.city-b.city};
    var _11=function(){return true};

    $.extend({
        affiSort:function(s){_10=s},

        affiFilter:function(f){_11=f},

        affiPrepare:function(c){   //c= valinput
            c=c.filter(_11);
            return c.sort(_10)},

        affi:function(_valinput,_1){  // _1 le callback
            _valinput=_valinput.trim();
            return this.getAffi(/^\d+$/.test(_valinput)?'codePostal':'nom',_valinput,_1)},

        codePostal:function(_valinput,_1){return this.getAffi('codePostal',_valinput,_1)},
        ville:function(_valinput,_1){return this.getAffi('nom',_valinput,_1)},

        getAffi:function(_2,_valinput,_1){   //_2 code ou city
            if(_valinput.length>1){
                _valinput=_valinput.trim();
                _3[_2]=_3[_2]||{};
                if(_3[_2][_valinput]){
                    _1(_valinput,$.affiPrepare(_3[_2][_valinput]||[]),_2)   // parametre du callblack _12 : vicoprepare, _4 code ou city
                }else{
                    var _data={};
                    _data[_2]=_valinput;
                    _data['fields']='code,nom,centre,codesPostaux';
                    return $.getJSON(_url,_data,function(_reponse){     //ici le get
                     //   console.log (_reponse)
                        _3[_2][_valinput]=_reponse;
                        _1(_reponse.input,$.affiPrepare(_reponse||[]),_2)})}
            }else{
                _1(_valinput,[],_2)}
        }
    });

    $.fn.extend({
        affiClean:function(){
            return $(this).each(function(){
                var _8=[];
                for(var n=$(this).next();n.hasClass('affi-answear');n=n.next())
                {_8.push(n[0])}
                $(_8).remove()
                $('._iner-Lst0').hide();
            })
        },
        affiTargets:function(){      //sur $input ?? verifie si input est bien data vicopo #localisze_gps-codep et retourne tableau
            var _7=[];
            $(this).each(function(){
                var t=$(this);
                $('[data-affi]').each(function(){
                    if(t.is($(this).data('affi'))){  //test sur l'id de l'input;;;super fort !!
                        _7.push(this)}
                })
            });
            return $(_7)},
        affiTarget:function(){
            return $(this).affiTargets().first()},
        getAffi:function(_13,_1){
            return $(this).keyup(function(){
                var i=$(this);
                $[_13](i.val(),function(_valinput,_4,_2){
                    if(_valinput===i.val()){
                        _1(_4,_2,_valinput)}})})},
        affi:function(_1){return $(this).getAffi('affi',_1)},
        codePostal:function(_1){return $(this).getAffi('codePostal',_1)},
        ville:function(_1){
            return $(this).getAffi('ville',_1)}});

    var _input='._in-stp02';

    $(document).on('keyup change',_input,function(e){
        liselected=true
        var $input=$(this);
        var _valinput=$input.val();

        if($input.data('affi-value')!==_valinput && _valinput.length > 3){
            var _t=$input.data('affi-value',_valinput).affiTargets().each(function(){
                $(this).hide().affiClean()});
            if(_t.length&&_valinput.length){
                $.affi(_valinput,function(_12,_4){   //le calback _12 = valinput, -4 tableau des resultats pour ce valinput
                    listdat=_4;
                    _t.each(function(){
                        var r=$(this).affiClean();
                        var _t=r.clone();
                        _t.show().removeAttr('data-affi');
                        var _c=[];
                        $('._Ul-l01').css({"display":"block","width":"100%"});
                        //$('.ipt_Se').addClass('RNNXgb')
                        $.each(_4,function(index,value){
                            var c=_t.clone();
                            c.addClass('affi-answear');
                            c.attr('data-id',index)
                            //c.find('[data-affi-code-postal]').text(this.code);
                            c.find('[data-affi-ville]').text(value.nom);
                            _c.push(c)
                        });
                        r.after(_c)
                    })
                    $('._iner-Lst0').show();
                   listinner=$('li');
                })
            }
        }
    });
$(_input).trigger('keyup')});

$(document).ready(function() {
    let citydefault=$('#city').attr('data-citydflt');
    let hcity = $('#localize_gps_codep');
    let taginput = $('._Ul-l01');
    let golocate = $('.mini-loc');
    let routetest = "";
    let nochange=false;
    let thisliselected;
    let itemlist=0

    var eventer = new MouseEvent('mouseenter', {
        'view': window,
        'bubbles': true,
        'cancelable': true
    });

    taginput.on('mouseenter','._Il-l01',
            function() {
                $(this ).append( $( "<span>X</span>" ) );
                $(this).prop('selected', true);
                liselected=true
                thisliselected=this;
            }).on('mouseleave','._Il-l01',
                function() {
                    $(this).prop('selected', false);
                    liselected=false
                    $( this ).find( "span" ).last().remove();
        })


    taginput.on('click ', '._Il-l01', function (e) {

        e.stopPropagation();
        initfieldlocate(this)
        taginput.hide();
        $('._iner-Lst0').hide();
        $('.ipt_Se').removeClass('RNNXgb');


       /*
        let code = $(".codpo", this);
        let city = $(".city", this);

        thisliselected=listdat[$(this).attr('data-id')];
        hcity.val(city.text())
        $('#localize_gps_codep').val(city.text())
        taginput.hide();
        $('._iner-Lst0').hide();
        $('.ipt_Se').removeClass('RNNXgb');
        golocate.css('opcacity', '1');
        // todo routetest = Routing.generate('new_locate', {city: city.text(), code: code.text()}, true);

        */
    });

    function initfieldlocate(lik){
        nochange=true;
        let city = $(".city", lik);
        thisliselected=listdat[$(lik).attr('data-id')];
        hcity.val(city.text())
        $('#localize_gps_codep').val(city.text())
        okgolocate()
    }

    function okgolocate(){
        // les routes sont données depuis la var action (soit potins ou charte potins)
        if (thisliselected) {
            //route => 'locate_affi'   //window.location.href = Routing.generate('+action+', {lon: thisliselected.centre.coordinates[0], lat:thisliselected.centre.coordinates[1]});
            window.location.href = "/"+action+"/?lon="+ thisliselected.centre.coordinates[0]+"&&lat="+thisliselected.centre.coordinates[1]+"&&nom="+thisliselected.nom+"&&code="+thisliselected.codesPostaux[0]
        } else {
            return false;
        }
    }


   /*
   todo voir pour version mobile
   golocate.on('click', function (e) {
        e.preventDefault()
        okgolocate();
    });

    */

    function initbykey(item){
        listinner.removeClass('selectedd')
        lik=$('li:eq('+item+')').addClass('selectedd')
    }

    window.addEventListener("keydown", function(e) {
        if (e.defaultPrevented) {
            return; // Do nothing if event already handled
        }
        if( e.key==="Enter" && lik[0]) {
            initfieldlocate(lik[0])
        }

        let tablist=listinner.length;

        if(tablist > 0){
            switch(e.code) {
               // case "KeyS":
                case "ArrowDown":
                    itemlist++
                    initbykey(itemlist)
                    break;
               // case "KeyW":
                case "ArrowUp":
                    itemlist--
                    initbykey(itemlist)
                    break;
            }
        }

    })

    $('body').on('click', function(e){
        e.stopPropagation();
        if(nochange){
            resetsearch()
        }
    });

    $('.clear-button').on('click', function(e){
        e.stopPropagation();
            resetsearch()
    });
    function resetsearch(){
        itemlist=0;
        listinner=[];
        nochange=false;
        thisliselected=false;
        lik=false;
        $('._in-stp02').val(citydefault||"").affiTargets().each(function(){
            $(this).hide().affiClean()}
        );
    }

    function okgolocateold(){
        $.ajax({
            type: 'get',
            url: routetest,
            beforeSend: function () {
                //loadinger.css('display', 'block')
            },
            success: function (data) {
                //loadinger.css('display', 'none')
                if (data.success) {
                    let jesus = JSON.parse(data.locate);
                    window.location.href = Routing.generate('cargo_public', {lat: jesus.latloc, lon:jesus.lonloc});
                    // window.location.href=Routing.generate('cargo_public',{city:jesus.city, lat:jesus.latloc, long:jesus.lonloc});
                } else {
                    console.log(data.success)
                }
            }
        })
    }
});