$(document).ready(function() {
    var perio =$("#add_post_event_periodicity");
    var periodicity =$("#add_post_event_typerepete"),
        callB =$("#rappel_automatique"),
        fieldsmonth =$(".fields-month"),
        fieldsweek =$(".fields-week"),
        fieldsrepete =$(".fields-repete"),
        along=$('.alongchoice'),
        fieldalong=$("#add_post_event_alongchoice"),
        fieldsalongdate=$(".alongdate"),
        onedate=$("#alongdate"),
        oneperiode=$("#oneperiode"),
        item=$('nav-item'),
        typeperiode=$('#add_post_event_periodicity');


    typeperiode.val('1')


    /*perio.change(function(){
      var numperio=perio.val();
        if(numperio ==="5"){
          fieldsrepete.show();
        }
        else{
          fieldsrepete.hide();
        }
      });*/

    /* $('li').on('click', function(e){
       if(!$(this).attr('class','active')){
         $('li>nav-link').removeClass('active');
         e.addClass('active');
         onedate.toogle();
         oneperiode.toogle();
       }
     })

*/

    $("#tab-nav a").click(function(e) {
        e.preventDefault();
        var tab = $(this).data("tab");
        if(tab==='onglet2'){
            along.show();
            typeperiode.val('2')
        }else{
            along.hide();
            typeperiode.val('1')
        }
        $(".tab").removeClass("tab-active");
        $("#" + tab).addClass("tab-active");
        $("#tab-nav a").removeClass("tab-nav-active");
        $(this).addClass("tab-nav-active");
    });


    fieldalong.change(function(){
        var numalong=fieldalong.val();
        if(numalong ==="1"){
            fieldsalongdate.show();
        }
        else{
            fieldsalongdate.hide();
        }
    });

    periodicity.change(function(){
        var numfieldsrepete=periodicity.val();
        if(numfieldsrepete ==="1"){
            fieldsmonth.hide();
            fieldsweek.hide();
        }
        if(numfieldsrepete ==="2"){
            fieldsmonth.hide();
            fieldsweek.show();
        }
        if(numfieldsrepete ==="3"){
            fieldsmonth.show();
            fieldsweek.hide();
        }
    });

    /*callB.click(function(){
      $(".select-callback").show();
       });
    */
});