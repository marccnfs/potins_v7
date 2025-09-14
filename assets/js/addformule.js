$(document).ready(function() {


    let listarticle=$('#formules_listarticle');


    class Compo {
        constructor(){
            this.list= {
                1:[],
                2:[],
                3:[],
                4:[]
            };
        }
        addcarte(carte){
            let cat=carte.attr("ctg");
            let art=carte.attr("carte");
            let index = this.list[cat].indexOf(art);
            if (index === -1) {
            this.list[cat].push(art);
            }
            this.changevalue()
        }
        deletecarte(carte){
            let cat=carte.attr("ctg");
            let art=carte.attr("carte");
            let index = this.list[cat].indexOf(art);
            if (index > -1) {
                this.list[cat].splice(index, 1);
            }
            this.changevalue()
        }

        changevalue(){
            listarticle.val(JSON.stringify(this.list))
           console.log(JSON.stringify(this.list))
            console.log(listarticle.val())
        }
    }

    let compo =new Compo();

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
        console.log(compo.list)
    })

});