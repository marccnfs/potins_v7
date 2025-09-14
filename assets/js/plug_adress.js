(function($)
{
    /*
    curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=housenumber&autocomplete=1' adresse avec priorité au numero
    curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=street&autocomplete=1'    adresse avec priorité au nom de rue
    curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=municipality&autocomplete=1' selection de la commune

    curl 'https://api-adresse.data.gouv.fr/search/?q=Saint-Just-sur-Viaur&type=locality&autocomplete=1'  // lieu dit

     */

    var _host = (location.protocol === 'http:' ? 'http:' : 'https:') + '//api-adresse.data.gouv.fr/search/?';
    var _cache = {};
    let order=1;
    let inf=$('.wb-inf-');
    let idwb=inf.attr('data-idwb'); //id du website
    var limitsort=$('.limit'),
        adresselect;
    let validadress=$('#validadress');
    let newadress=$('#newadress');
    let editadress=$('#editadress');
    let modifadress=$('#modifadress');

    var _filter = function () {
        return true;
    };

    let _sort = function (a, b) { //tri
        return a.city - b.city;
    };

    validadress.show()
    let tabfeature=[];



    class featurelist{
        constructor(feature) {
            this.spaceweb=idwb;
            this.feature=feature;
            this.id=feature.properties.id;
            this.label=feature.properties.label;
            this.numero = feature.properties.housenumber;
            this.street = feature.properties.street;
            this.city = feature.properties.city;
            this.postcode = feature.properties.postcode;
            this.insee = feature.properties.citycode;
            this.context = feature.properties.context;
            this.lat = feature.geometry.coordinates[0];
            this.long = feature.geometry.coordinates[1];
        }

        dataprepare(){
            let fd = new FormData();
            fd.append('spaceweb',this.spaceweb);
            fd.append('num', this.numero);
            fd.append('rue', this.street);
            fd.append('city', this.city);
            fd.append('codp', this.numero);
            fd.append('insee', this.numero);
            return fd
        }

        datajson(){
            let fd = new FormData();
            fd.append('spaceweb', JSON.stringify(this));
        }
    }

    $.extend({
        spwbSort: function ($sort) {
            _sort = $sort;
        },
        spwbFilter: function ($filter) {
            _filter = $filter;
        },

        //La méthode filter() crée et retourne un nouveau tableau contenant tous les éléments
        // du tableau d'origine qui remplissent une condition déterminée par la fonction callback.

        //La méthode sort() trie les éléments d'un tableau, dans ce même tableau, et renvoie le tableau.
        // Par défaut, le tri s'effectue sur les éléments du tableau convertis en chaînes de caractères
        // et triées selon les valeurs des unités de code UTF-16 des caractères.

        spwbPrepare: function ($features) {
            $features = $features.filter(_filter);
            return $features.sort(_sort);
        },

        //La méthode trim() permet de retirer les blancs en début et fin de chaîne. Les blancs considérés
        // sont les caractères d'espacement (espace, tabulation, espace insécable, etc.)
        // ainsi que les caractères de fin de ligne (LF, CR, etc.).
        // regex /^\d+$/ si input est en chiffre  recherche code
        /* ancinne version
                    spwb: function (_input, _done) {
                        _input = _input.trim();
                        return this.getspwb(/^\d+$/.test(_input) ? 'code' : 'city', _input, _done);
                    },
        */
        spwbadresse: function (_input, _done) {
            _input = _input.trim();
            // _input=_input.replace(/ /,'+');
            return this.getSpwb( 'q', _input, _done); //done : la fonction d'ajout de resultat
        },

        // la requete ajax
        getSpwb: function (_name, _input, _done) {   //name = q
            if(_input.length > 10) { // demarre a partir de 10 caractères
                limitsort.show();
                _input = _input.trim();
                _cache[_name] = _cache[_name] || {};
                if(_cache[_name][_input]) { //if _check
                    _done(_input, $.spwbPrepare(_cache[_name][_input] || []), _name); //_check le 1er argument, $features le 2eme
                } else {
                    var _data = {};
                    _data[_name] = _input;
                    return $.getJSON(_host, _data, function (_answear) {
                        _cache[_name][_input] = _answear.features;
                        _done(_answear.query, $.spwbPrepare(_answear.features || []), _name);
                    });
                }
            } else {
                _done(_input, [], _name);
            }
        }
    });

    $.fn.extend({
        spwbClean: function () {
            return $(this).each(function () {
                var _removeList = [];
                for(var $next = $(this).next(); $next.hasClass('spwb-answear'); $next = $next.next()) {
                    _removeList.push($next[0]);
                }
                $(_removeList).remove();
            });
        },
        spwbTargets: function () {
            var _targets = [];
            $(this).each(function () {
                var $target = $(this);
                $('[data-spwb]').each(function () {
                    if($target.is($(this).data('spwb'))) {
                        _targets.push(this);
                    }
                });
            });
            return $(_targets);
        },
        spwbTarget: function () {
            return $(this).spwbTargets().first();
        },
        getspwb: function (_method, _done) {
            return $(this).keyup(function () {
                var $input = $(this);
                $[_method]($input.val(), function (_input, _features, _name) {
                    if(_input === $input.val()) {
                        _done(_features, _name, _input);
                    }
                });
            });
        },
        spwb: function (_done) {
            return $(this).getspwb('spwb', _done);
        },
        codePostal: function (_done) {
            return $(this).getspwb('codePostal', _done);
        },
        ville: function (_done) {
            return $(this).getspwb('ville', _done);
        }
    });

    var _fields = '#labeladress';// todo modiifer précisement sur le bon input sinon enclanche pour chaque input
    $(document).on('keyup change', _fields, function () {
        validadress.hide();
        var $target = $(this);
        var _input = $target.val();

        if($target.data('spwb-value') !== _input) {
            var _$targets = $target.data('spwb-value', _input)
                .spwbTargets().each(function () {
                    $(this).hide().spwbClean();
                });
            if(_$targets.length && _input.length) {
                $.spwbadresse(_input, function (_check, _features) { //argument 2 : _done en callback
                    //       console.log(_check,"_check linge 157")
                    if(_check === _input) {

                        _$targets.each(function () {
                            var $repeater = $(this).spwbClean();
                            var _$template = $repeater.clone();
                            _$template.show().removeAttr('data-spwb');
                            var _$features = [];
                            tabfeature=[];
                            $.each(_features, function () {
                                tabfeature[this.properties.id]=this;
                                var $feature = _$template.clone();
                                $feature.addClass('spwb-answear');
                                $feature.find('[data-spwb-adresse]').text(this.properties.label).data('id',this.properties.id);
                                $feature.find('[data-spwb-val-adresse]').text(this.properties.label);
                                _$features.push($feature);
                            });
                            $repeater.after(_$features);
                        });
                    }
                });
            }
        }
    });

    $(_fields).trigger('keyup');

    //let hcode=$('#localize_gps_codeok');
    //let hcity=$('#localize_gps_city');

    limitsort.on('click','.resuladress', function(){
        let adresse = $(".adresslist", this);
        adresselect=adresse.data('id');
        let hcode=adresse.text();
        let $target=$('#labeladress');
        $target.val(hcode);
        $target.data('spwb-value', hcode).spwbTargets().each(function () {
            $(this).hide().spwbClean();
        });
        limitsort.hide();
        validadress.show()
    });

    /*-------------- les actions boutons new, edit et modif ----- et validation ajax ------------*/

    editadress.on('click', function(e) { // dans le cas d'une nouvelle adresse
        e.stopPropagation();
        e.preventDefault();
        let adressid= $(this).attr('data-adress');
    });

    $('.modified').on('click', function(e) { // dans le cas d'une nouvelle adresse
        e.stopPropagation();
        e.preventDefault();
        $(this).parent('list-adress').hide();
        let idadress=$(this).attr('data-adress');
        let $target=$('#labeladress');
        $target.val($('#'+idadress).text());
        $('.new-adress').show();
        order=idadress;
    });

    newadress.on('click', function(e) { // dans le cas d'une nouvelle adresse
        e.stopPropagation();
        e.preventDefault();
        $('.new-adress').show();
        $('#listadressgp').hide();
    });

    $('.deleted').on('click', function(e){
        e.stopPropagation();
        e.preventDefault();
        let conf=confirm("attention, voulez vous vraiment supprimer cette adresse ?")
        if(conf){
            deletejax($(this).attr('data-adress'))
        }
    });

    validadress.on('click', function(e){ // dans le cas d'une nouvelle adresse
        e.stopPropagation();
        e.preventDefault();
        if(adresselect!=="") {
            let _featurelist = tabfeature[adresselect];
            let fd = new FormData();
            fd.append('adress', JSON.stringify(_featurelist));
            fd.append('idspwb',idwb);
            if(order===1){
                fd.append('order',1);
            }else{
                fd.append('order',order);
            }
            goajax(fd)
        }
    });

    modifadress.on('click', function(e){ // dans le cas d'une nouvelle adresse
        e.stopPropagation();
        e.preventDefault();
        if(adresselect!=="") {
            let _featurelist = tabfeature[adresselect];
            let fd = new FormData();
            fd.append('adress', JSON.stringify(_featurelist));
            fd.append('idspwb',idwb);
            fd.append('order',"1");
            goajax(fd)
        }
    });

    function deletejax(data) {
        $.ajax({
            method: 'DELETE',
            url: Routing.generate('deleteadress', {id: data}, true),
            datatype: 'json',
            success: function (reponse){
                console.log(reponse);
                document.location.reload();
            },
            error: function (reponse) {
                console.log(reponse.error)
            }
        })
    }
    let route="";
    if(idwb !==undefined){
        route="/geolocate/op/newadress"
    }else{
        route="/customer/profil/adress/newadress"
    }


    function goajax(data){
        $('.progress').show();
        let  ajaxgo = $.ajax({
            method: 'POST',
            url: route,
            data: data,
            contentType: false,
            cache: false,
            processData: false,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                //Upload progress, request sending to server
                xhr.upload.addEventListener("progress", function (evt) {
                    console.log("in Upload progress");
                    console.log("Upload Done");
                }, false);

                //Download progress, waiting for response from server
                xhr.addEventListener("progress", function (e, percentComplete) {
                    console.log("in Download progress");
                    if (e.lengthComputable) {
                        percentComplete = ((parseInt(e.loaded) / parseInt(e.total)) * 100), 10;
                        console.log(percentComplete);
                    } else {
                        console.log("Length not computable.");
                    }
                }, false);
                return xhr;
            }
        });

        ajaxgo.done(function (reponse) {
            if (reponse.success) {
                $('.progress').hide();
                //$('#adress').text(reponse.label)
                document.location.reload();  //todo avec reac pour modif dom
            }else{
                console.log(reponse.error);
            }
        });

    }
})(jQuery);