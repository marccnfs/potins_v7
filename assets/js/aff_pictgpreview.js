$(document).ready(function() {


    /* upload image--------------------------------------------------------------------------
        via resizor.js
    // aff_review.js-----------------------------------------------------------------*/

    let addpict = $('#addpict'),
        id_gpreview=$('#gpreview'),
        form_add=$('.gp-form-save'),
        gpreview=id_gpreview.data('gp'),
        post=id_gpreview.data('post'),
        datatoAjax=false;


    let  routeaddpict ='/ajax/addpict/gpreview/'+gpreview+'/'+post,
        redirect = '/member/gpreview/manage-group_review/'+post;


    function showErrorAlert (reason, detail) {
        var msg='';
        if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
        else {
            console.log("error uploading file", reason, detail);
        }
        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
            '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
    }

    let propp =function(p){
        if(p){
            form_add.show()
            addpict.css({
                'background': '#0093c4',
                'box-shadow': '0 20px 67px 0 rgba(0,0,0,.12), 0 5px 14px 0 rgba(0,0,0,.2)!important',
                'opacity': '1'
            });
            addpict.prop("disabled", false);
            $('#media-img').prop("disabled", false);
        }else{
            form_add.hide()
            $('#media-img').prop("disabled", false);
            addpict.css({
                'background': 'grey',
                'box-shadow': 'none',
                'opacity': '0.5'
            });
            addpict.prop("disabled", true);
        }
    };
/*
    if(post!==0){
        propp(true)
    }else{
        propp(false)
    }
*/

    document.getElementById("uploadImage").addEventListener("change",function(e){
        if (this.files.length === 0) { return;}
        let oFile = this.files[0];
        if ($.testgif(oFile)){
            datatoAjax=oFile;
        }else{
            if (!$.testfile(oFile))return;
            $.resizor({l: 600, h: 600, f: oFile, p: true}).then((thetumb) => {
                datatoAjax=thetumb.src
            })
        }
        propp(true)
    });

    addpict.on('click', function (event){
        event.preventDefault();
        event.stopPropagation();
        startloadernews();
    });


    let startloadernews = function () {
        let fd = new FormData();
        fd.append('post', post);
        fd.append('gpreview', gpreview);
        fd.append('file', datatoAjax);
        fd.append('typefile', datatoAjax);

        $.ajax({
            type: 'post',
            url: routeaddpict,
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
                $('.iner-bt').hide();
                window.location.replace(redirect);
            }else{
                console.log(data.msg)
            }
        });
    }
});