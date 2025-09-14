const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

$("document").ready(function() {
    let add=$('#convers-go');
    let $msg=$('#messg');
    let contentmsg=$('#content');

    /*version prod ----------------------------*/
    //let route =Routing.generate('add_private-convers_dp', {}, true);
    //let redirect="https://affichange.com/member/messagery/private/read/"+$msg.attr('data-wb')+"/"+$msg.attr('data-msg');

    /*version dev ----------------------------*/
    let route ="http://localhost/affi-v5.2/public/member/messagery/private/add-convers";
    let redirect="http://localhost/affi-v5.2/public/member/messagery/private/read/"+$msg.attr('data-msg');

    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }
    history.scrollRestoration = "manual";
    window.onload = scrollToBottom;

    function doneconvers(data) {
        if (data.success) {
            window.location.href=redirect;
        }else{
            console.log(data)
        }
    }

    contentmsg.on('change',function(e) {
        e.stopPropagation();
    });

    add.on('click',function(event) {
        add.hide()
        event.stopPropagation();
        if (contentmsg.val()!=="") {
            $.ajax({
                type: 'post',
                url:route,
                data: {
                    content: contentmsg.val(),
                    msgwb:$msg.attr("data-msg"),
                },
                beforeSend: function () {
                    console.log(contentmsg.val())
                }
            }).done(function (data) {
                doneconvers(data);
            });
        }
    });
});

