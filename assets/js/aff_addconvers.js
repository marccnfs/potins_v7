$("document").ready(function() {
    let add=$('#convers-go');
    let $msg=$('#messg');
    let contentmsg=$('#content');

    let route ="/wb/msg/innermsg/add-convers-wb";
    let redirect="/wb/msg/innermsg/msg-read/"+$msg.attr('data-wb')+"/"+$msg.attr('data-msg');

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
                    slug:$msg.attr("data-wb")
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

