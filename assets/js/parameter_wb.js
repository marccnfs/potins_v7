$(document).ready(function() {



    if($('.inf_modul').data('edit')){

    let oFReader1 = new FileReader(), rFilter1 = /^(?:image\/jpg|image\/JPG|image\/jpeg|image\/png|image\/svg\+xml)$/i;
    oFReader1.onload = function (oFREvent) {
        document.getElementById("uploadPreview1").src =oFREvent.target.result
    };

    document.getElementById("website_template_logotemplate").addEventListener("change",function(e){
        if (this.files.length === 0) { return;}
        let oFile = this.files[0];
        if (!rFilter1.test(oFile.type)) {alert("You must select a valid image file!");return;}
        oFReader1.readAsDataURL(oFile);
    });

    let oFReader2 = new FileReader(), rFilter2 = /^(?:image\/jpg|image\/JPG|image\/jpeg|image\/png|image\/svg\+xml)$/i;
    oFReader2.onload = function (oFREvent) {
        document.getElementById("uploadPreview2").src =oFREvent.target.result
    };

    document.getElementById("website_template_background").addEventListener("change",function(e){
        if (this.files.length === 0) { return;}
        let oFile = this.files[0];
        if (!rFilter2.test(oFile.type)) {alert("You must select a valid image file!");return;}
        oFReader2.readAsDataURL(oFile);
    });
    }

    let website=$('.wb-inf-'),
        slug= website.attr('data-slugwb'),
        id= website.attr('data-idwb');

    $("#btnmodule").click(function() {
        let module = $(this).data('module')
        let fd = new FormData();
        fd.append('module', $(this).data('module'));
        fd.append('slug', slug);
        $.ajax({
            type: 'POST',
            url: '/module/website/add-module-ajx',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function (data) {
            $('.progress').hide();
            if (data){
                window.location.replace('/admin-website/param/modules/'+id);
            }else{
                console.log(data)
            }
        });
    })

});