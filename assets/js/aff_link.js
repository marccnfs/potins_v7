$(document).ready(function() {


    let btnpost = $('#post-news'),
        postar=$('#postar'),
        post=postar.data('post');


    let  routeaddlink = '/admin/add-link-ajx',
        redirect = '/tableau-de-bord';

    let tabfield={
            link: $('#link')
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
            //tabfield.contentOne.focus();
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

    tabfield.link.on('change', function () {
        if (tabfield.link.val()) {
            propp(true)
        }
    });

    btnpost.on('click', function (event){
        event.preventDefault();
        event.stopPropagation();
        if (validateform(tabfield)) {
            startloadernews();
        }
    });

    function validateform(tab){
        let cpt=0;
        //console.log(tab)
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
        fd.append('link', tabfield.link.val());
        fd.append('post', post);

        $.ajax({
            type: 'post',
            url: routeaddlink,
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