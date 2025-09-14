/* upload image------ via resizor.js  -----------------*/

$(document).ready(function() {


    $('.custom-file').each(function(i){
        let ht=$(this).html().replace(/custom-file-input/g,        'custom-file-input-affi')
            .replace(/custom-file-label/g,        'custom-file-label-affi fa fa-camera');
        $(this).html(ht)
    });


    let uploadpreview = function(f, art,pw) {
        if (f.files.length === 0) {
            return;
        }
        let oFile = f.files[0];
        if (!$.testfile(oFile)) {
            return;
        }
        $.resizor({l: 600, h: 600, f: oFile, p: false}).then((thetumb) => {
            f.value = "";


                console.log(thetumb)
                document.getElementById("uploadpreview").src = thetumb.src
                document.getElementById("ressource_base64").value = thetumb.src

        })
    }


    document.getElementById("ressource_pict_file").addEventListener("change",function(e) {
        uploadpreview(this,'ressource_pict_file',"uploadpreview");
    });


    $('#forumule-news').on('click', function(event){
        //event.preventDefault();
        event.stopPropagation();
        $('.baba').hide();
        $('.wait').show();
        /* setTimeout(()=>{
             document.forms["formules"].submit();
         },600);
         */
    });

});