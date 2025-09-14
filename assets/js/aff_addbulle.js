$("document").ready(function() {
    module=$('#buller')
    console.log(module)
    if (typeof module != 'undefined'){
        let idbulle=module.data('idbulle') //id de la bulle si elle exist pour le dispatch
        let entity=module.data('entity') //id du post, offre etc
        let typ=module.data('typ') //module_blog, event, shop, found...etc
        let nbblog=$("#nbcathblog").text();
        let nbevent=$("nbcathevent").text();
        console.log(nbblog, nbevent);

        function buller(response) {
            module.removeClass('tobulle').addClass('todebulle').html('Debullez')
            module.data('idbulle', response.bulle)
        }
        function debuller(response) {
            module.removeClass('todebulle').addClass('tobulle').html('Bullez')
            module.data('idbulle', "")
        }
        module.on('click',function(event) {
            event.stopPropagation();
            if(idbulle === ""){
                $.ajax({
                    type: 'post',
                    url:'/bulle/blowbubble-ajx',
                    data: {
                        entity: entity,
                        typ:typ,
                    },
                    beforeSend: function () {
                        console.log("catch en cours")
                    }
                }).done(function (response) {
                    if(response){
                        buller(response);
                    }else{
                        console.log(response)
                    }
                });
            }else{
                $.ajax({
                    type: 'post',
                    url:'/bulle/delete-bubble-ajx',
                    data: {
                        idbulle: idbulle,
                    },
                    beforeSend: function () {
                        console.log("catch en cours")
                    }
                }).done(function (response) {
                    if(response){
                        debuller(response);
                    }else{
                        console.log(response)
                    }
                });
            }
        });
    }
});

