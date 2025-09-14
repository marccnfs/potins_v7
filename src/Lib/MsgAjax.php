<?php


namespace App\Lib;


class MsgAjax
{
    const MSG_COMLETED=['success' =>true,'msg' =>'la news à bien été créée','module'=>"", 'slug'=>""];
    const MSG_ERR1=['success' =>false,'msg' =>'la news est introuvable','idnews'=>""];
    const MSG_ERR2=['success' =>false,'msg' =>'le choix de typload inconnu','idnews'=>""];
    const MSG_ERR3=['success' =>false,'msg' =>'erreur sur le stape 3','idnews'=>""];
    const MSG_ERR4=['success' =>false,'msg' =>'erreur sur le stape 4','idnews'=>""];
    const MSG_ERR5=['success' =>false,'msg' =>'le comment est introuvable','idnews'=>""];
    const MSG_ERR6=['success' =>false,'msg' =>'erreur init news','idnews'=>""];
    const MSG_ERR7=['success' =>false,'msg' =>'erreur process','idnews'=>""];
    const MSG_OK=['success' =>true,'msg' =>'proccess reuissi','idnews'=>""];
    const MSG_BULL0=['success' =>false,'msg' =>'Init fouaré','idnews'=>""];
    const MSG_BULL1=['success' =>false,'msg' =>'Creation bulle non terminée','idnews'=>""];
    const MSG_BULL2=['success' =>false,'msg' =>'erreur bulle 2','idnews'=>""];
    const MSG_BULL3=['success' =>false,'msg' =>'erreur bulle 3','idnews'=>""];
    const MSG_BULLOK=['success' =>true,'msg' =>'proccess reuissi','idnews'=>""];
    const MSG_POST0=['success' =>false,'msg' =>'erreur init'];
    const MSG_POST1=['success' =>false,'msg' =>'erreur module'];
    const MSG_POST2=['success' =>false,'msg' =>'erreur file'];
    const MSG_POSTOK=['success' =>true,'msg' =>'post enregistré'];
    const MSG_SUCCESS=['success' =>true];
    const MSG_ERRORRQ=['success' =>false,'msg' =>'erreur requete'];
    const MSG_NOWB=['success' =>false,'msg' =>'website non trouvé'];
    const MSG_NOADMIN=['success' =>false,'msg' =>'ne dispose pas des droits admin sur ce website'];
    const MSG_INOS=['success' =>false,'msg' =>'in the baba no se'];

}

