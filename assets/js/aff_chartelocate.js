const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

jQuery(function($){
    //var _14=(location.protocol==='http:'?'http:':'https:')+'//vicopo.selfbuild.fr';
    var _url14=(location.protocol==='http:'?'http:':'https:')+'//geo.api.gouv.fr/communes';
    var _3={};
    var _10=function(a,b){return a.city-b.city};
    var _11=function(){return true};

    $.extend({
        vicopoSort:function(s){_10=s},
        vicopoFilter:function(f){_11=f},
        vicopoPrepare:function(c){   //c= valinput
            c=c.filter(_11);
            return c.sort(_10)},

        vicopo:function(_valinput,_1){  // _1 le callback
            _valinput=_valinput.trim();
            return this.getVicopo(/^\d+$/.test(_valinput)?'codePostal':'nom',_valinput,_1)},

       // curl 'https://geo.api.gouv.fr/communes?nom=Versailles&fields=code,nom,centre,codesPostaux'

        codePostal:function(_valinput,_1){return this.getVicopo('codePostal',_valinput,_1)},

        ville:function(_valinput,_1){return this.getVicopo('nom',_valinput,_1)},

        getVicopo:function(_2,_valinput,_1){   //_2 code ou city
            if(_valinput.length>1){
                _valinput=_valinput.trim();
                _3[_2]=_3[_2]||{};
                if(_3[_2][_valinput]){
                    _1(_valinput,$.vicopoPrepare(_3[_2][_valinput]||[]),_2)   // parametre du callblack _12 : vicoprepare, _4 code ou city
                }else{
                    var _data9={};
                    _data9[_2]=_valinput;
                    _data9['fields']='code,nom,centre,codesPostaux';
                    return $.getJSON(_url14,_data9,function(_data5){     //ici le get
                console.log(_data5)
                        _3[_2][_valinput]=_data5;//.cities;
                        //_1(_data5.input,$.vicopoPrepare(_data5.cities||[]),_2)})}
                        _1(_data5.input,$.vicopoPrepare(_data5||[]),_2)})}
            }else{
                _1(_valinput,[],_2)}
        }
    });

    $.fn.extend({
        vicopoClean:function(){
            return $(this).each(function(){
                var _8=[];
                for(var n=$(this).next();n.hasClass('vicopo-answear');n=n.next())
                {_8.push(n[0])}
                $(_8).remove()
                $('._iner-Lst0').hide();
            })
        },
        vicopoTargets:function(){      //sur $input ?? verifie si input est bien data vicopo #localisze_gps-codep et retourne tableau
            var _7=[];
            $(this).each(function(){
                var t=$(this);
                $('[data-vicopo]').each(function(){
                    if(t.is($(this).data('vicopo'))){  //test sur l'id de l'input;;;super fort !!
                        _7.push(this)}
                })
            });
            return $(_7)},
        vicopoTarget:function(){
            return $(this).vicopoTargets().first()},
        getVicopo:function(_13,_1){
            return $(this).keyup(function(){
                var i=$(this);
                $[_13](i.val(),function(_valinput,_4,_2){
                    if(_valinput===i.val()){
                        _1(_4,_2,_valinput)}})})},
        vicopo:function(_1){return $(this).getVicopo('vicopo',_1)},
        codePostal:function(_1){return $(this).getVicopo('codePostal',_1)},
        ville:function(_1){
            return $(this).getVicopo('ville',_1)}});

    var _input='._in-stp02';

    $(document).on('keyup change',_input,function(){
        var $input=$(this);
        var _valinput=$input.val();

        if($input.data('vicopo-value')!==_valinput && _valinput.length > 3){
            var _t=$input.data('vicopo-value',_valinput).vicopoTargets().each(function(){
                $(this).hide().vicopoClean()});

            if(_t.length&&_valinput.length){
                $.vicopo(_valinput,function(_12,_4){   //le calback _12 = valinput, -4 tableau des resultats pour ce valinput
                    //if(_12===_valinput){
                        _t.each(function(){
                            var r=$(this).vicopoClean();
                            var _t=r.clone();
                            _t.show().removeAttr('data-vicopo');
                            var _c=[];
                            $('._Ul-l01').css({"display":"block","width":"100%"});
                            //$('.ipt_Se').addClass('RNNXgb')
                            $.each(_4,function(){
                                var c=_t.clone();
                                c.addClass('vicopo-answear');
                                c.find('[data-vicopo-code-postal]').text(this.code);
                                c.find('[data-vicopo-ville]').text(this.nom);
                                //c.find('[data-vicopo-val-code-postal]').val(this.code);
                                //c.find('[data-vicopo-val-ville]').val(this.city);
                                _c.push(c)
                            });
                            r.after(_c)
                        })
                        $('._iner-Lst0').show();
                    //}
                })
            }}
    });
$(_input).trigger('keyup')});


$(document).ready(function() {
    let citydefault=$('#city').attr('data-citydflt');
    //let hcode = $('#localize_gps_codep');
    let hcity = $('#localize_gps_codep');
    let taginput = $('._Ul-l01');
    let golocate = $('.mini-loc');
    let routetest = "";
    let nochange=false;

    taginput.on('click', '._Il-l01', function (e) {
        nochange=true;
        e.stopPropagation();
        let code = $(".codpo", this);
        let city = $(".city", this);
        //hcode.val(code.text());
        hcity.val(city.text())
        $('#charte_city').val(city.text())
        $('#charte_codep').val(code.text())
        taginput.hide();
        $('._iner-Lst0').hide();
        $('.ipt_Se').removeClass('RNNXgb');
       // golocate.css('opcacity', '1');
        //btnpost.prop('disabled', false);
        //routetest = Routing.generate('new_locate', {city: city.text(), code: code.text()}, true);
    });

   /* golocate.on('click', function (e) {
        console.log('clik')
        e.preventDefault()
        $.ajax({
            type: 'get',
            url: routetest,
            beforeSend: function () {
                //loadinger.css('display', 'block')
            },
            success: function (data) {
                //loadinger.css('display', 'none')
                if (data.success) {
                    let jesus = JSON.parse(data.potins);
                    window.location.href = Routing.generate('cargo_public', {lat: jesus.latloc, lon:jesus.lonloc});
                    // window.location.href=Routing.generate('cargo_public',{city:jesus.city, lat:jesus.latloc, long:jesus.lonloc});
                } else {
                    console.log(data.success)
                }
            }
        })
    });

    */
    /*
    $('body').on('click', function(e){
        e.stopPropagation();
        if(nochange){
            resetsearch()
        }
    });

     */

    $('.clear-button').on('click', function(e){
        e.stopPropagation();
            resetsearch()
    });
    function resetsearch(){
        $('._in-stp02').val(citydefault||"entrez une ville...").vicopoTargets().each(function(){
            $(this).hide().vicopoClean()}
        );
    }
});