$(document).ready(function() {
    let tabinf={
        inf1x24:"bulle d'info"
    };
    let  andestand=$('#andestand').data("st",false), publiq=$('#publicwebsite'), privat=$('#privatewebsite'), fieltypewebsite=$('#new_spw_typespaceweb'), submiter=$('#subform').prop('disabled', true).css('opacity',"0");
   publiq.data("select",true);
   privat.data("select",false);
    fieltypewebsite.val("public");

     function submiterft(){
        submiter.prop('disabled', false).css('opacity',"1");
        submiter.show();
    }

    $('#inf1x24').on('click',function(){
        let inf=$(this)
        let c=inf.attr("data-inf")
        inf.text(tabinf.c)
        $(this).closest('.space-text').toggleClass('tophgt')
    });

    andestand.on('click',function(e){
        e.stopPropagation()
        btnandestand($(this),$(this).attr('data-inf'))
     });

    function btnandestand(b,d){
        $('.'+d).toggle();
        b.data('h',b.data('st') ?  b.data('h') : b.html());
        b.data('st',!b.data('st'));
        b.html(b.data('st') ? '<i class="far fa-times-circle"></i>' : b.data('h'))
    }

    $('#botrelatif').on('click',function(){
        $('#charte_charte').val(1);
        $('#charte_typespaceweb').val("relatif");
        $(this).removeClass('btn-light').addClass('btn-info');
        $(this).closest('.space-glass').addClass('back_spacetxt');;
        submiterft();
    });

    privat.on('click',function(){
        if(publiq.data("select")){
            publiq.data("select",false);
            privat.data("select",true);
            TogSelect(privat, publiq);
            fieltypewebsite.val("private");
            submiterft();
        }
    });

    publiq.on('click',function(){
        if(!publiq.data("select")) {
            privat.data("select",false);
            publiq.data("select", true);
            TogSelect(publiq, privat);
            fieltypewebsite.val("public");
            submiterft();
        }else{
            TogSelect(publiq, privat);
            fieltypewebsite.val("public");
            submiterft();
        }
    });

    function TogSelect(...args){
        args.map(function (element) {
            element.toggleClass('btn-light').toggleClass('btn-info').closest('.space-glass').toggleClass('back_spacetxt');
        })
    }

    let taginput=$('._Ul-l01');

    taginput.on('click','._Il-l01', function(){
        let code = $(".codpo", this);
        let city = $(".city", this);
        $('#charte_idlocate').val(code.text()+" "+city.text())
        taginput.hide();
        $('.ipt_Se').removeClass('RNNXgb');
        $('._iner-Lst0').hide();
    });

});