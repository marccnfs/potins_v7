import tinymce from "tinymce";
import 'tinymce/themes/silver';
import 'tinymce/icons/default/icons';
import 'tinymce/plugins/code';
import 'tinymce/models/dom/model';
import 'tinymce/plugins/image';


$(document).ready(function() {

    let btnpost = $('#post-news'),
        infwb=$('.wb-inf-'),
        idwb=infwb.attr('data-idwb'),
        contenthtml,
        cpt=0,
        editor = $('#editor'),
        taboffre={tx:"",id:"",idwb:idwb,promostat:false,content:"",etat:"neuf",promonat:"choice", more:false,calendar:false,prod:false,link:false,promo:false, cat:""},
        collapseaffi=$('.colapsaffi'),
        offre=$('#initoffert').attr('data-taboffre'),
        datatoAjax=false;
    const form = document.querySelector("form");
    let tabfield={
        legende: $('#legende'),
        titreoffre: $('#titreoffre')
    };

    let  routeaddnews = '/member/marketplace/shop/add-workshop-ajx',
         redirect = '/board/sucess/shop/show/'+idwb;


    tinymce.init({
        selector: '#mytextarea',
        menubar: false,
        toolbar: 'styleselect bold italic alignleft aligncenter alignright bullist indent code image',
        plugins: ['code','image'],
        language:  'fr_FR',
    });
    let med = tinymce.get('mytextarea');

    let propp =function(p){
        if(p){
            btnpost.css({
                'background': '#0093c4',
                'box-shadow': '0 20px 67px 0 rgba(0,0,0,.12), 0 5px 14px 0 rgba(0,0,0,.2)!important',
                'opacity': '1'
            });
            btnpost.prop("disabled", false);
            $('#media-img').prop("disabled", false);
            tabfield.legende.focus();
        }else{
            $('#media-img').prop("disabled", false);
            btnpost.css({
                'background': 'grey',
                'box-shadow': 'none',
                'opacity': '0.5'
            });
            btnpost.prop("disabled", true);
        }
    };

    let erroradd= function(i){
        i.nextElementSibling.innerHTML="non renseigné";
        i.nextElementSibling.className='error active';
        cpt++;
    };

    let errorno= function(i){
        i.nextElementSibling.innerHTML="";
        i.nextElementSibling.className='error';
    };

    if(offre!=="") {
        let tab = JSON.parse(offre);
        for(const property in tab){
            if(tab.hasOwnProperty(property)){
                taboffre[property]=tab[property]
            }
        }

        $('#tagsprod').text(taboffre.tx)
        if(taboffre.promostat) $('#promonat option[value="'+taboffre.promonat+'"]').prop('selected', true);
        if( taboffre.etat==="neuf"){
            $('input:radio[name=etat]:nth(1)').attr('checked',true);
        }else{
            $('input:radio[name=etat]:nth(0)').attr('checked',true);
        }

        propp(true)

    }else{
        propp(false)
    }

    let calendara= $('.calendar-affi');
    calendara.affCalendar({});
    calendara.on('click', '#previoustmonth', function(e){
        e.stopPropagation();
        calendara.affCalendar("previousMonth",e)       //initpage(calendar.previousDate())
    });
    calendara.on('click', '#nextmonth', function(e){
        e.stopPropagation();
        calendara.affCalendar("nextMonth",e)  //initpage(calendar.calendar.nextDate())
    });
    calendara.on('click','.calendar__day', function(e) {
        e.stopPropagation();
        e.preventDefault();
        calendara.affCalendar("addday", e)
    });


    function showErrorAlert (reason, detail) {
        var msg='';
        if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
        else {
            console.log("error uploading file", reason, detail);
        }
        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
            '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
    }



    (function(){
        if(taboffre.content!==""){
            editor.html(taboffre.content);
        }
    })();

    collapseaffi.on('click', function(){
        let _a = $(this).attr('data-afficolapse')
        taboffre[_a]=!taboffre[_a];
        let propa=$('.'+_a);
        taboffre[_a]? propa.prop('required',true) : propa.prop('required',false);
        console.log(taboffre)
    });

    function updateTextareaHeight(input){
        input.style.height = 'auto';
        input.style.height = input.scrollHeight+'px';
    }

    tabfield.legende.on('input', function(){
        updateTextareaHeight(this)
        //$(this).css('height', 'auto')
        // $(this).css('height', this.scrollHeight+'px')
    });
    updateTextareaHeight(document.getElementById('legende'));

    tabfield.titreoffre.on('change', function () {
        if (tabfield.titreoffre.val()) {
            propp(true)
        }
    });

    document.getElementById("uploadImage").addEventListener("change",function(e){
        if (this.files.length === 0) { return;}
        let oFile = this.files[0];
        if (!$.testfile(oFile))return;
        $.resizor({l: 600, h: 600, f: oFile, p: true}).then((thetumb) => {
            datatoAjax=thetumb.src
        })
    });

    btnpost.on('click', function (e){
        e.stopPropagation();
        e.preventDefault();
        preparajax();
        let fd = new FormData(form);
        if (validateform(fd)) {
            startloadernews(fd);
        }else{
            console.log ('pas ok validation')
        }
    });

    let preparajax = function () {
        if (editor.length) {
            editor.cleanHtml();
            contenthtml = editor.html();
        } else {
            contenthtml = false
        }
    };


    $("select.natpromo").change(function(){
        taboffre.promonat = $(this).children("option:selected").val();
       // alert("Vous avez sélectionné le langage : " + promotype);
    });



    function validateform(fd){
        cpt=0;
        for (let i of form) {
            if(!i.validity.valid){
                i.nextElementSibling.innerHTML="non renseigné";
                i.nextElementSibling.className='error e_active';
                cpt++;

            }else{
                if( i.getAttribute('type') !== 'hidden'){
                    errorno(i)
                }
            }
        }
        taboffre.etat=$('input:radio[name=etat]:checked').val();
        fd.append('taboffre',JSON.stringify(taboffre));
        return cpt++ <= 0;
    }

    let startloadernews = function (fd) {
        fd.append('contenthtml', contenthtml);
        fd.append('file64', datatoAjax);
        fd.append('appointments', calendara.affCalendar('getdayselected')); // un array ?

        $.ajax({
            type: 'post',
            url: routeaddnews,
            data: fd,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('.iner-bt').hide();
                $('.progress').show();
            }
        }).done(function (data) {
            $('.progress').hide();
            if (data.success){
                window.location.replace(redirect);
            }else{
            console.log(data.msg)
            }
        });
    }
});