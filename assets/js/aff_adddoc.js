$(document).ready(function() {


    /* upload image--------------------------------------------------------------------------
        via resizor.js
    // -----------------------------------------------------------------*/

    let btnadd = $('#add'),
        btnnoadd = $('#noadd'),
        doc=$('#adddoc'),
        eventid= doc.data('event'),
        docfile=false,
        datatoAjax=false;


    let urladdcoc ='/member/wb/event/add-doc-ajx',
        redirect = '/member/wb/event/details-event/'+eventid;


    let tabfield={
            titredoc: $('#fichier'),
            type: $('#typedoc')
    };

    let propp =function(p){
        if(p){
            btnadd.css({
                'background': '#0093c4',
                'box-shadow': '0 20px 67px 0 rgba(0,0,0,.12), 0 5px 14px 0 rgba(0,0,0,.2)!important',
                'opacity': '1'
            });
            btnadd.prop("disabled", false);
            $('#media-img').prop("disabled", false);
            //tabfield.contentOne.focus();
        }else{
            $('#media-img').prop("disabled", false);
            btnadd.css({
                'background': 'grey',
                'box-shadow': 'none',
                'opacity': '0.5'
            });
            btnadd.prop("disabled", true);
        }
    };

    if(eventid!==0){
        propp(true)
    }else{
        propp(false)
    }

    tabfield.titredoc.on('change', function () {
        if (tabfield.titredoc.val()) {
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

    document.getElementById("uploadDoc").addEventListener("change",function(e){
        if (this.files.length === 0) { return;}
        docfile = this.files[0];
        console.log(docfile);
    });

    btnadd.on('click', function (event){
        event.preventDefault();
        event.stopPropagation();
        startloadernews().then(r => console.log('retour startloardernews'));
        /*if (validateform(tabfield)) {

        }

         */
    });
/*

    function validateform(tab){
        let cpt=0;
        for (let i in tab) {
            if (tab.hasOwnProperty(i)) {
                if(!tab[i][0].validity.valid){
                    tab[i][0].nextElementSibling.innerHTML="non renseigné"
                    tab[i][0].nextElementSibling.className='error active'
                    cpt++;
                }else{
                    tab[i][0].nextElementSibling.innerHTML="";
                    tab[i][0].nextElementSibling.className='error'
                }
            }
        }
        return cpt++ <= 0;
    }


 */

    async function startloadernews(){
        let fd = new FormData();
        fd.append('titre', tabfield.titredoc.val());
        fd.append('type', tabfield.type.val());
        fd.append('event', eventid);
        fd.append('file64', datatoAjax);
        fd.append('docfile',docfile,'legif');
console.log(fd)
        let response = await fetch(urladdcoc, {
            method: 'POST',
            body: fd
        });

        let result = await response.json();
        console.log(fd,result);


        /*
        $.ajax({
            type: 'post',
            url: urladdcoc,
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

         */
    }
});