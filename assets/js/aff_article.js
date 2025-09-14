$(document).ready(function() {

    let btnpost = $('#post-news'),
        postar=$('#postar'),
        post=postar.data('post'),
        art=postar.data('art'),
        contenthtml,
        incontenthtml=postar.data('content'),
        editor = $('#editor');

    if(incontenthtml===""){
        contenthtml=false;
    }else{
        contenthtml=true;
        editor.html(incontenthtml)
    }

    let  routeaddarticle = '/admin/add-article-potin_ajx',
        redirect = '/tableau-de-bord';

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

  let propp =function(p){
        if(p){
            btnpost.css({
                'background': '#0093c4',
                'box-shadow': '0 20px 67px 0 rgba(0,0,0,.12), 0 5px 14px 0 rgba(0,0,0,.2)!important',
                'opacity': '1'
            });
            btnpost.prop("disabled", false);
            $('#media-img').prop("disabled", false);
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

    propp(true)

    btnpost.on('click', function (event){
        event.preventDefault();
        event.stopPropagation();
        preparajax();
        startloadernews();
    });

    let preparajax = function () {     // pour creation de news
        if (editor.length) {
            editor.cleanHtml();
            contenthtml = editor.html();
        } else {
            contenthtml = false
        }
    };

    let startloadernews = function () {
        let fd = new FormData();
        fd.append('contenthtml', contenthtml);
        fd.append('post', post);
        fd.append('art', art);

        $.ajax({
            type: 'post',
            url: routeaddarticle,
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