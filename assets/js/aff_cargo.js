$(document).ready(function() {
    let sizediv=0;
    let gargodiv = document.querySelector(".cargobulle_mb");
    let cpt=0;
    let cargo;
    let tabpos=[], tabhypo=[];
    let listbulle;
    var demibulle;
    var classecss;

    if(gargodiv){
        classecss="bulle_cargo_aleat_js_mb";
        demibulle=37.5;
        cargo=$('.cargo_mb');
        sizediv=gargodiv.offsetWidth-5;
        cargo.css({'width': +sizediv+'px','height':+sizediv+'px'}).fadeIn('fast');
        /*
        let elements = document.querySelectorAll(".bulle_cargo_aleat");
        elements.forEach(function (item) {
            item.style.setProperty("--bullcargo", (sizediv /4)+'px');
        })

         */
    }else{
        cargo=$('.cargobulle');
        classecss="bulle_cargo_aleat_js";
        demibulle=40;
    }

    listbulle=cargo.data('cargo');
    cargo.data('cargo',"")
    let sizecargo=cargo.height();
    if(!sizecargo)sizecargo=sizediv-5; // $('.cargo_mb').width()
    let rayoncargo=sizecargo/2;
    class position {
        constructor(bul) {
            this.tb=bul;
            this.name=bul.name;
            this.url=bul.url;
            this.x=40;
            this.y=40;
            this.hypopo=0;
            this.cos=0;
            this.sin=0;
        }

        aleatposition() {
            let max = Math.floor(rayoncargo-demibulle);
            let r = Math.floor(Math.random() * (max - 1)) + 1;
            let degres = Math.random() * (360 - 1) + 1;
            this.cos=Math.cos(degres*(Math.PI/180));
            this.sin=Math.sin(degres*(Math.PI/180));
            this.xoa =  r * this.cos;
            this.yoa =  r * this.sin;
        }

        //calcul l'hypotenus entre les bulles en référence
        calperimetre(xob,yob){
            return Math.sqrt(Math.pow(Math.abs(this.xoa - xob), 2) + Math.pow(Math.abs(this.yoa - yob), 2));
        }

        //init le tableau des perimetre de toutes les bulles
        inittabhypo() {
            let i=1;
            tabhypo=[];
            for(let el in tabpos){
                if( tabpos.hasOwnProperty( el ) ) {
                    if(i < cpt){
                        let xob = tabpos[el].xoa;
                        let yob = tabpos[el].yoa;
                        tabhypo.push(this.calperimetre(xob, yob))
                        i++;
                    }
                }
            }
        }

        mouve(){
            this.aleatposition();
            if(cpt>1){
                this.inittabhypo();
                let ok = true, compteur=0;
                while (ok) {
                    compteur++;
                    if(compteur>70)break;
                    if (tabhypo.every(function (vl) {
                        return( vl > (demibulle*2)+10)
                    })){
                        ok = false
                    }else{
                        this.aleatposition();
                        this.inittabhypo();
                    }
                }
            }
        }

        addposition(){

            if(this.cos>0){
                this.x=(this.xoa-demibulle)+rayoncargo
            }else{
                this.x=rayoncargo-(Math.abs(this.xoa-demibulle))
            }
            if(this.sin<0){
                this.y=Math.abs((this.yoa+demibulle)-rayoncargo)
            }else{
                this.y=rayoncargo-(this.yoa+demibulle)
            }
            let html="<a class='"+classecss+" _omb_3' href='"+this.url+"' style='top:"+this.y +"px; left:"+this.x + "px'>";
            html+= "<div class='op_bulle'>";
            html+= "<div class='link_bulle'>"+this.name+"</div>";
            html+= "</div></a>";
            $(html).appendTo('#tgl_b');
        }
    }

    function initbull(){
    for(let bul in listbulle){
        if( listbulle.hasOwnProperty( bul ) ) {
            cpt++;
            tabpos[cpt]=new position(listbulle[bul])
            tabpos[cpt].mouve()
            tabpos[cpt].addposition()
        }
    }
    }

    initbull();
});
