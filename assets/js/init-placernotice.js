$(document).ready(function () {

    let inf = $('.wb-inf-');
    let _pg = $('.ii_switch').attr('data-js') === 'cu';
    let agent = inf.attr("data-agent") !== "mobile/";  //ici agent true c'est desk/ ??
    let notices = null;

    // init plugin placernotice :
    let panneau = $('.flex-notb_v6-fix');
    let replace = inf.attr("data-replacejs") !== "no";
    if (agent && replace) {
        notices = $('.last-postall_v6-fix');
    } else if (replace) {
        notices = $('.last-postall_mob');
    }
  //  if (agent && replace) panneau.placernotice(notices); // plugin sur panneau

    async function dodo() {
            await pause();
            if (agent && replace) panneau.placernotice(notices); // plugin sur panneau
    }

    function pause(id) {
        return new Promise(resolve => setTimeout(() => {
            console.log(`pause  is over`);
            resolve();
        }, 500));
    }

    dodo()

    window.onresize = recalpanneau

    function recalpanneau() {
        if (agent && replace) panneau.placernotice(notices);
    }
});