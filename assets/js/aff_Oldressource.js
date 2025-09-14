$(document).ready(function() {


    /* upload image--------------------------------------------------------------------------
        via resizor.js
    // -----------------------------------------------------------------*/

    let btnpost = $('#post-news'),
        website=$('.wb-inf-'),
        postar=$('#postar'),
        post=postar.data('post'),
        slug= website.attr('data-slugwb'),
        contenthtml,
        incontenthtml=postar.data('content'),
        editor = $('#editor'),
        datatoAjax=false;


    if(incontenthtml===""){
        contenthtml=false;
    }else{
        contenthtml=true;
        editor.html(incontenthtml)
    }

    let  routeaddrss = '/member/ressources/manage-ressources-ajx',
        redirect = '/list-ressources';

  function initToolbarBootstrapBindings() {

        var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier','Courier New', 'Comic Sans MS', 'Helvetica','Impact','Lucida Grande','Lucida Sans','Tahoma','Times','Times New Roman','Verdana'],
             fontTarget = $('[title=Font]').siblings('.dropdown-menu');
         $.each(fonts, function (idx, fontName) {
             fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
             });
       //  jQuery('a[title]').tooltip({container:'body'});
         $('.dropdown-menu input').click(function() {return false;})
         .change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
        .keydown('esc', function () {this.value='';$(this).change();});

         $('[data-role=magic-overlay]').each(function () {
             var overlay = $(this), target = $(overlay.data('target'));
             overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
             });


        if ("onwebkitspeechchange"  in document.createElement("input")) {
            var editorOffset = editor.offset();
            $('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+editor.innerWidth()-35});
        } else {
            $('#voiceBtn').hide();
        }
    }


    function showErrorAlert (reason, detail) {
        var msg='';
        if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
        else {
            console.log("error uploading file", reason, detail);
        }
        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
            '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
    }



   initToolbarBootstrapBindings();
    editor.wysiwyg({ fileUploadError: showErrorAlert} );

    window.prettyPrint && prettyPrint();

    let tabfield={
        titrenews: $('#titrenews'),
        composition: $('#composition'),
        descriptif:$('#descriptif'),
        cat:$('#cat'),
        label: $('#label'),
        htmltitre: $('#htmltitre'),
    };

    let propp =function(p){
        if(p){
            btnpost.css({
                'background': '#0093c4',
                'box-shadow': '0 20px 67px 0 rgba(0,0,0,.12), 0 5px 14px 0 rgba(0,0,0,.2)!important',
                'opacity': '1'
            });
            btnpost.prop("disabled", false);
            $('#media-img').prop("disabled", false);
           // tabfield.contentOne.focus();
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

    if(post!==0){
        propp(true)
    }else{
        propp(false)
    }

    tabfield.titrenews.on('change', function () {
        if (tabfield.titrenews.val()) {
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

    btnpost.on('click', function (event){
        event.preventDefault();
        event.stopPropagation();
        preparajax();
        if (validateform(tabfield)) {
            startloadernews();
        }
    });

    let preparajax = function () {     // pour creation de news
        if (editor.length) {
            editor.cleanHtml();
            contenthtml = editor.html();
        } else {
            contenthtml = false
        }
    };

    function validateform(tab){
        let cpt=0;
        console.log(tab)
        for (let i in tab) {
            if (tab.hasOwnProperty(i)) {
                if(!tab[i][0].validity.valid){
                    tab[i][0].nextElementSibling.innerHTML="non renseigné"
                    tab[i][0].nextElementSibling.className='error active'
                    cpt++;
                }else{
                    console.log(tab[i][0])
                    tab[i][0].nextElementSibling.innerHTML="";
                    tab[i][0].nextElementSibling.className='error'
                }
            }
        }
        return cpt++ <= 0;
    }

    let startloadernews = function () {
        let fd = new FormData();
        fd.append('titre', tabfield.titrenews.val());
        fd.append('descriptif', tabfield.descriptif.val());
        fd.append('composition', tabfield.composition.val());
        fd.append('label', tabfield.label.val());
        fd.append('htmltitre', tabfield.htmltitre.val());
        /*fd.append('contenthtml', contenthtml);*/

        fd.append('post', post);
        fd.append('slug', slug);
        fd.append('base64', datatoAjax);
        fd.append('cat', tabfield.cat.val());

        $.ajax({
            type: 'post',
            url: routeaddrss,
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