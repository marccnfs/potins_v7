
function geoFindMe() {

    const status = document.querySelector('#status');

    (function($)
    {
        var _3={};
        var _10=function(a,b){return a.city-b.city};
        var _11=function(){return true};
        var localitate={};
        var _url=(location.protocol==='http:'?'http:':'https:')+'//geo.api.gouv.fr/communes';
        var taginput=$('._Ul-l01');
        var hcity = $('#localize_gps_codep');

        class Localite {
            constructor(l_class) {
                this.l_class = l_class;
                this.l_citydefault = $('#city').attr('data-citydflt');
                this.l__input = '._in-stp02';
                this.l_nochange = false;
                this.l_thisliselected ="";
                this.l_itemlist = 0;
                this.l_listdat={};
                this.l_liselected=false;
                this.l_listinner=[];
                this.l_lik=false;
            }

            changing(ele) {

                this.l_liselected=true
                let $input=$(ele);
                let _valinput=$input.val();
                if($input.data('affi-value')!==_valinput && _valinput.length > 3){
                    let _t=$input.data('affi-value',_valinput).affiTargets().each(function(){
                        $(this).hide().affiClean()});
                    if(_t.length&&_valinput.length){
                        $.affi(_valinput,function(_12,_4){   //le calback _12 = valinput, -4 tableau des resultats pour ce valinput
                            localitate.l_listdat=_4;
                            _t.each(function(){
                                let r=$(this).affiClean();
                                let _t=r.clone();
                                _t.show().removeAttr('data-affi');
                                let _c=[];
                                $('._Ul-l01').css({"display":"block","width":"100%"});
                                $.each(_4,function(index,value){
                                    let c=_t.clone();
                                    c.addClass('affi-answear');
                                    c.attr('data-id',index)
                                    c.find('[data-affi-ville]').text(value.nom);
                                    _c.push(c)
                                });
                                r.after(_c)
                            })
                            $('._iner-Lst0').show();
                            localitate.l_listinner=$('li');
                        })
                    }
                }
            }

            selectetag(ele,e){

                e.stopPropagation();
                this.initfieldlocate(ele);
                taginput.hide();
                $('._iner-Lst0').hide();
                $('.ipt_Se').removeClass('RNNXgb');
            }
            initfieldlocate(lik){

                this.l_nochange=true;
                let city = $(".city", lik);
                this.l_thisliselected=this.l_listdat[$(lik).attr('data-id')];
                hcity.val(city.text())
                // $('#localize_gps_codep').val(city.text())
                localitate.okgolocate()
            }
            mouseentertag(ele){
                $(ele).append( $( "<span>X</span>" ) );
                $(ele).prop('selected', true);
                localitate.l_liselected=true
                localitate.l_thisliselected=ele;
            }
            mouseovertag(ele){
                $(ele).prop('selected', false);
                localitate.l_liselected=false
                $(ele).find( "span" ).last().remove();
            }
            okgolocate(){


                if (localitate.l_thisliselected) {

                    if(this.l_class ==="cargo") {

                        //window.location.href = Routing.generate('locate_affi', {lon: localitate.l_thisliselected.centre.coordinates[0], lat:localitate.l_thisliselected.centre.coordinates[1]});
                        //window.location.href = "http://localhost/affi-v5.2/public/locate/?lon=" + localitate.l_thisliselected.centre.coordinates[0] + "&&lat=" + localitate.l_thisliselected.centre.coordinates[1] + "&&nom=" + localitate.l_thisliselected.nom + "&&code=" + localitate.l_thisliselected.codesPostaux[0]
                    }
                    if(this.l_class ==="customer") {

                        // routetest = Routing.generate('new_locate', {city: city.text(), code: code.text()}, true);
                        //window.location.href = Routing.generate('locate_affi', {lon: localitate.l_thisliselected.centre.coordinates[0], lat:localitate.l_thisliselected.centre.coordinates[1]});
                        window.location.href = "/customer/profil/change-potins-customer/?lon=" + localitate.l_thisliselected.centre.coordinates[0] + "&&lat=" + localitate.l_thisliselected.centre.coordinates[1] + "&&nom=" + localitate.l_thisliselected.nom + "&&code=" + localitate.l_thisliselected.codesPostaux[0]
                    }
                } else {
                    return false;
                }
            }

            initbykey(item){
                localitate.l_listinner.removeClass('selectedd')
                localitate.l_lik=$('li:eq('+item+')').addClass('selectedd')
            }
            resetsearch(ele){
                this.l_itemlist=0;
                this.l_listinner=[];
                this.l_nochange=false;
                this.l_thisliselected=false;
                this.l_lik=false;
                $('._in-stp02').val(this.l_citydefault||"").affiTargets().each(function(){
                    $(ele).hide().affiClean()}
                );
            }
            keyaction(e){
                let tablist=localitate.l_listinner.length;
                if(tablist > 0){
                    switch(e.code) {
                        // case "KeyS":
                        case "ArrowDown":
                            this.l_itemlist++
                            localitate.initbykey(this.l_itemlist)
                            break;
                        // case "KeyW":
                        case "ArrowUp":
                            this.l_itemlist--
                            localitate.initbykey(this.l_itemlist)
                            break;
                    }
                }
            }
        }

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
                return $(this).getAffi('ville',_1)}
        });


        localitate=new Localite(hcity.data('lclass'))

        $(document).on('keyup change','._in-stp02',function(e){
            localitate.changing(this)
        });

        $(localitate.l__input).trigger('keyup')

        taginput.on('mouseenter','._Il-l01',
            function() {
                localitate.mouseentertag(this)
            }).on('mouseleave','._Il-l01',
            function() {
                localitate.mouseovertag(this)
            })

        taginput.on('click ', '._Il-l01', function (e) {
            localitate.selectetag(this,e)
        });


        window.addEventListener("keydown", function(e) {
            if(localitate !==null){
                if (e.defaultPrevented) {
                    return; // Do nothing if event already handled
                }
                if( e.key==="Enter" && localitate.l_lik[0]) {
                    localitate.initfieldlocate(localitate.l_lik[0])
                }
                localitate.keyaction(e)
            }
        })

        $('body').on('click', function(e){
            e.stopPropagation();
            if(localitate.l_nochange){
                localitate.resetsearch(this)
            }
        });

        $('.clear-button').on('click', function(e){
            e.stopPropagation();
            localitate.resetsearch(this)
        });

    })(jQuery);

}

export default class Geolocate extends HTMLElement {

    static get observedAttributes() {return ['prefix']; }

    constructor() {
        super();
        this.lattitude=0
        this.longitude=0
        this.redirect="/potins/?lon="+this.longitude+"&&lat="+this.lattitude;
        this.button=document.createElement('button')
        this.button.classList.add("bt-loc")
        this.incone =document.createElement('i')
        this.incone.classList.add("fa")
        this.incone.classList.add("fa-map-marker")
        this.appendChild(this.button)
        this.button.appendChild(this.incone)
    }

    connectedCallbach(){
        console.log('init geolocate');
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if(oldValue!== null ){
            geoFindMe();
        }
    }

    disconnectCalback(){
        this.destroy
    }
}





