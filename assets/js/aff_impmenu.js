/* upload image------ via resizor.js  -----------------*/

$(document).ready(function() {
    let $container = $('div#formules_catformules');
    let index = $container.find(':input').length;
    let tabarticle=[];

    class Compo {
        constructor(){
            this.list= {
                "1":[],
                "2":[],
                "3":[],
                "4":[]
            };
        }
        addcarte(carte){
            let cat=carte.attr("data-ctg");
            let art=carte.attr("data-carte");
            let index = this.list[cat].indexOf(art);
            if (index === -1) {
                this.list[cat].push(art);
            }
            this.changevalue()
        }
        deletecarte(carte){
            let cat=carte.attr("data-ctg");
            let art=carte.attr("data-carte");
            let index = this.list[cat].indexOf(art);
            if (index > -1) {
                this.list[cat].splice(index, 1);
            }
            this.changevalue()
        }

        init(tab){
            $('.pop-carte').each(function (index){
                for(const ele of tab){
                    let art =$(this).data('carte')
                    if (art === ele){
                        $(this).addClass("selectpop")
                        compo.addcarte($(this))
                    }
                }
            })
        }

        changevalue(){
            listarticle.val(JSON.stringify(this.list))
        }
    }

    let compo = new Compo();

    let listarticle=$('#formules_listarticle');

    if(listarticle.val()!==""){
        tabarticle =JSON.parse(listarticle.val());
        console.log(tabarticle)
        compo.init(tabarticle)
    }

    $('.pop-carte').on('click', function(e){
        e.stopPropagation();
        e.preventDefault();
        let carte=$(this)
        if(! carte.hasClass("selectpop")){
            carte.addClass("selectpop")
            compo.addcarte(carte)
        }else{
            carte.removeClass("selectpop")
            compo.deletecarte(carte)
        }
    })

    $('#add_formule').click(function(e) {
        addFormule($container);
        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });


    if (index === 0) {
        addFormule($container);
    } else {
        $container.children('div').each(function() {
            addDeleteLink($(this));
        });
    }

    function addFormule($container) {
        var template = $container.attr('data-prototype')
            .replace(/__name__label__/g, 'formule n°' + (index+1))
            .replace(/__name__/g,        index)
        ;

        var $prototype = $(template);
        if(index !== 0) addDeleteLink($prototype);
        $('#formules_catformules').append($prototype);
        index++;
    }

    function addDeleteLink($prototype) {
        var $deleteLink = $('<a href="#" class="btn btn-danger">Supprimer</a>');
        $prototype.append($deleteLink);
        $deleteLink.click(function(e) {
            $prototype.remove();
            e.preventDefault();
            return false;
        });
    }
});