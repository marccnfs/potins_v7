import {formDataToObj} from "../functions/dom";

$("document").ready(function() {


    /*-------------------------------- read message ----------------------------------*/
    let add=$('#convers-go');
    let $msg=$('#messg');
    let contentread=$('#content');

    let routeread ="/wb/msg/innermsg/add-convers-wb";
    let redirectread="/wb/msg/innermsg/msg-read/"+$msg.attr('data-wb')+"/"+$msg.attr('data-msg');

    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }
    history.scrollRestoration = "manual";
    window.onload = scrollToBottom;

    function doneconvers(data) {
        if (data.success) {
            window.location.href=redirectread;
        }else{
            console.log(data)
        }
    }

    contentread.on('change',function(e) {
        e.stopPropagation();
    });

    add.on('click',function(event) {
        add.hide()
        event.stopPropagation();
        if (contentread.val()!=="") {
            $.ajax({
                type: 'post',
                url:routeread,
                data: {
                    content: contentread.val(),
                    msgwb:$msg.attr("data-msg"),
                    slug:$msg.attr("data-wb")
                },
                beforeSend: function () {
                    console.log(contentread.val())
                }
            }).done(function (data) {
                doneconvers(data);
            });
        }
    });

 /*-------------------------------------------------- conversation et register member conversation ---------------*/

    let routeconvers="/tools/jxrq/add-member-contactmail";
    let routetestmail= "/tools/jxrq/test-visitor-mail";
    let gocontact=$('#contact-go'),
        goregister=$('#register-go'),
        postor=$('#postform'),
        tgl12=$('#tgl_12'),
        tgl11=$('#tgl_11'),
        tgl10=$('#tgl_10'),
        tglmsg=$('#tgl_newmsg'),
        form=$('form[name="contact_form"]'),
        loadinger=$("#loading"),
        playcontact=$(".form-play-contact"),
        playregister=$(".form-play-register"),
        formtime=$('.msg-website'),
        emailExp = /^[^\s()<>@,;:\/]+@\w[\w\.-]+\.[a-z]{2,}$/i,
        statemember=$('#memberstate').attr('data-member');
    let fieldscontact=[$('#contact_name'),$('#contact_email')];
    let fieldsregister=[$('#register_name'),$('#register_password'),$('#register_email')];
    let contentmsg=$('#contact_form_content');
    let tabcontact={};
    let typeregister=$('#typeform').attr('data-type');
    let website=$('#register').attr("data-register");



    $('#addwbchoice').on('click', function(e){
        e.preventDefault();
        $('#contact_form_follow').val($("input[name=addwb]:checked").val());
        $('#choicead').hide();
        $('#aduserconvers').show();
    });

    //sorte de toogle avec id du bouttton et un data pour function() à faire et change le contenu html
    //==================================================================================================
    function btnew(b,d){
        $('.'+d).toggle();
        b.data('h',b.data('st') ?  b.data('h') : b.html());
        b.data('st',!b.data('st'));
        b.html(b.data('st') ? '<i class="fa fa-times-circle"></i>' : b.data('h'))
    }

    $('#bl-nw').on('click',function(e){
        e.stopPropagation();
        btnew($(this),$(this).attr('data-inf'))
    });

    //====================================================================================================

    $('.w_msg').on('click', function(e){
        $('#id-choicacces').hide();
        if($(this).attr('data-choice')==="log"){
            $('.msg_register').show();
        }else{
            $('.msg_contact').show();
        }
    });

    $('#andst-register').on('click',function(e){
        e.stopPropagation();
        $('.info-register').hide();
        tgl12.show();
        tgl11.hide();
        tgl10.hide();
    });

    if(statemember==='1'){ //todo a prioir ne srt a rien
        formtime.show();
        postor.show();
    }
    const verifmail=function(i){
        let ierror = false;
        if (!emailExp.test(i.val())) {
            ierror = true;
        }
        i.next('.validate').html((ierror ? (i.attr('data-msg') !== undefined ? i.attr('data-msg') : 'erreur') : '')).show('blind');
        return !ierror;
    };

    function donetestmail(data) {
        loadinger.hide();
        if (data.success === 'user') {
            if (window.confirm('cette adresse mail correspond à un espace membre. Souhaitez vous vous identifier?')) {
                let url = "{{ path('app_login') }}";
                //todo : a definir le retour
                window.location.replace(url); //fait une redirection vers login'
            } else {
                fieldscontact[1].val("");
                playcontact.hide()
            }
        }
        if (data.success === 'contact') {
            let jesus = JSON.parse(data.contact);
            $("#contact_form_id").val(jesus.id);
            $('#contact_form_type').val('contact')
            playcontact.show()
            gocontact.show().prop('disabled', false)
        }
        if (data.success === 'nomail') {
            $('#contact_form_type').val('new')
            playcontact.show()
            gocontact.show().prop('disabled', false)
        }
    }

    function doneresgistermail(data){
       loadinger.hide()
       if (data.success === 'user') {
           if (window.confirm('Cette adresse mail correspond à un espace membre. Souhaitez vous vous connecter?')) {
               let url = "{{ path('app_login') }}";
               //todo : a definir le retour
               window.location.replace(url); //fait une redirection vers l'article'
           } else {
               fieldsregister[2].val("")
           }
       }
       if (data.success === 'contact') {
           let jesus = JSON.parse(data.contact);
           tabcontact.id=jesus.id;
           tabcontact.state=true;
           playregister.show();
           goregister.show().prop('disabled', false)
       }
        if (data.success === 'nomail') {
            $('#contact_form_type').val('new')
            tabcontact.state=false;
            playregister.show()
            goregister.show().prop('disabled', false)
        }
   }

    function validateform(event,f,m,c,fields){
        let ferror = false;
        if(m){
        fields.forEach(function(i) {
            let rule = i.attr('data-rule');
            if (rule !== undefined) {
                let ierror = false; // error flag for current input
                let pos = rule.indexOf(':', 0);
                if (pos >= 0) {
                    var exp = rule.substr(pos + 1, rule.length);
                    rule = rule.substr(0, pos);
                } else {
                    rule = rule.substr(pos + 1, rule.length);
                }
                switch (rule) {
                    case 'required':
                        if (i.val() === '') {
                            ferror = ierror = true;
                        }
                        break;

                    case 'minlen':
                        if (i.val().length < parseInt(exp)) {
                            ferror = ierror = true;
                        }
                        break;

                    case 'email':
                        if (!emailExp.test(i.val())) {
                            ferror = ierror = true;
                        }
                        break;

                    case 'checked':
                        if (!i.is(':checked')) {
                            ferror = ierror = true;
                        }
                        break;

                    case 'regexp':
                        exp = new RegExp(exp);
                        if (!exp.test(i.val())) {
                            ferror = ierror = true;
                        }
                        break;
                }
                i.next('.validate').html((ierror ? (i.attr('data-msg') !== undefined ? i.attr('data-msg') : 'wrong Input') : '')).show('blind');
            }
        });
        }
        if(c){
            let rule = contentmsg.attr('data-rule');
            if (rule !== undefined) {
                let ierror = false; // error flag for current input
                let pos = rule.indexOf(':', 0);
                if (pos >= 0) {
                    var exp = rule.substr(pos + 1, rule.length);
                    rule = rule.substr(0, pos);
                } else {
                    rule = rule.substr(pos + 1, rule.length);
                }
                switch (rule) {
                    case 'required':
                        if (contentmsg.val() === '') {
                            ferror = ierror = true;
                        }
                        break;
                    case 'minlen':
                        if (contentmsg.val().length < parseInt(exp)) {
                            ferror = ierror = true;
                        }
                        break;
                }
                contentmsg.next('.validate').html((ierror ? (contentmsg.attr('data-msg') !== undefined ? contentmsg.attr('data-msg') : 'wrong Input') : '')).show('blind');
            }
        }
        if (ferror) {
            event.preventDefault();
            f.find('.sent-message').slideUp();
            f.find('.error-message').slideUp();
            f.find('.loading').slideDown();
            return false;
        } else {
            return true;
        }
    }

    goregister.on("click", function(event){
        let f=$('form[name="register_msg"]')
        if(validateform(event,f,true,false,fieldsregister)){
            addmember();
        }
    });
/*
    gocontact.on('click',function(event) {
        let f=$('form[name="contact_msg"]')
        if(validateform(event,f,true,false,fieldscontact)){
            $('#contact_form_username').val(fieldscontact[0].val())
            $('#contact_form_email').val(fieldscontact[1].val())
            $('.msg_contact').fadeOut('fast');
            formtime.fadeIn('fast');
            postor.show();
            tgl12.show();
            tgl11.hide();
            tgl10.hide();
        }
    });
*/
    gocontact.on('click',function(event) {
       // let f=$('form[name="contact_msg"]')
       // if(validateform(event,f,true,false,fieldscontact)){
//            $('#contact_form_email').val(fieldscontact[1].val())
            $('.msg_contact').fadeOut('fast');
       //     formtime.fadeIn('fast');
        //    postor.show();
            tgl12.show();
      //      tgl11.hide();
      //      tgl10.hide();
    //    }
    });

    postor.on('click',function(event) {
        let f=form;
        if(validateform(event,f,false,true,fieldscontact)){
            f.submit();
            f.fadeOut('fast');
            $('.wait').fadeIn('fast')
        }
    });


    fieldscontact[1].on('change, blur',function(e) {

        if (verifmail($(this))) {

            let formData = new FormData();
            formData.append('email', $(this).val())
            formData.append('typ', "contact")

            $.ajax({
                type: 'post',
                url:routetestmail,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    loadinger.show()
                }
            }).done(function (data) {
                loadinger.hide();
                donetestmail(data);
            });
        }
    });

    fieldsregister[2].on('change',function(e) {
        if (verifmail($(this))) {

            let formData = new FormData();
            formData.append('email', $(this).val())
            formData.append('typ', "register")

            $.ajax({
                type: 'post',
                url:routetestmail,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    loadinger.show()
                }
            }).done(function (data) {
                loadinger.hide();
                doneresgistermail(data);
            });
        }
    });

    contentmsg.on('input',function(e){
        e.stopPropagation();
        if (contentmsg.val().length > 2) {
            postor.show()
        }else{
            postor.hide()
        }
    });

    function addmember(){
        $.ajax({
            type: 'post',
            url: routeconvers,
            data: {
                mail: fieldsregister[2].val(),
                pass:fieldsregister[1].val(),
                name:fieldsregister[0].val(),
                idwebsite:website,
                state:tabcontact.state,
                typeregister:typeregister,
                id:tabcontact.id},
            beforeSend: function () {
                $('.msg_register').fadeOut('fast');
                $('#loadingregister').show();
            }
        }).done(function (data) {
            if(data.success){
                console.log(data)
                $("#contact_form_id").val(data.id);
                $('#contact_form_type').val('member')
                $('.msg_register').fadeOut('fast');
                $('#loadingregister').hide();
                formtime.show();
                $('#namemeber').html(data.name)
                $('.info-register').show();
                postor.show();
            }else{
                console.log("pas de retour")
            }
        });
    }

});

