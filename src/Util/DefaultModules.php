<?php

namespace App\Util;

class DefaultModules
{
    const MODULE_LIST=array('module_event','module_mail','module_blog');
    const TAB_MODULES=array(
        'module_event',
        'module_mail',
        'module_shop',
        'module_blog',
        'module_private',
        'module_reservation',
        'module_ressources',
        'module_offer'
    );
    const TAB_MODULES_NAME=array(
        'module_event'=>"Evenement",
        'module_mail'=>"Message",
        'module_shop'=>"Boutique",
        'module_blog'=>"Affiche",
        'module_private'=>"Conversation",
        'module_reservation'=>"reservation",
        'module_found'=>"Ressources",
        'module_offer'=>"Annonce"
    );
    const TAB_MODULES_URL=array(
        'module_event'=>"new_generic_event",
        'module_mail'=>"new_generic_mail",
        'module_shop'=>"new_generic_offre",
        'module_blog'=>"new_generic_postation",
        'module_private'=>"new_generic_mail_private",
        'module_reservation'=>"new_generic_resa",
        'module_found'=>"new_generic_menu",
        'module_offer'=>"new_generic_annonce",
    );
    const TAB_MODULES_URL_ID=array(
        'module_event'=>"new_event",
        'module_mail'=>"new_messages_wb",
        'module_shop'=>"new_offre",
        'module_blog'=>"new_postation",
        'module_private'=>"new_mail_private",
        'module_reservation'=>"new_resa",
        'module_found'=>"new_menu",
        'module_offer'=>"new_annonce",
    );

    const TAB_MODULES_INFO=array(
        'module_event'=>['link'=>'module_event','name'=>"module de gestion de vos évenements",'title'=>"Evenement - planning manifestation commercial(marchés)",'desc'=>"Ce module vous permet d'afficher en temps réel votre présence sur un marché et/ou de publier votre planning dans votre Space info",'price'=>"10,00€"],
        'module_mail'=>['link'=>'module_mail','name'=>"module messagerie locale",'title'=>"Conversations locales",'desc'=>"Ce module ouvre votre espace de communication. Astuce : utilisez le lien dans vos courriers électroniques, site web...et centralisez votre communication.",'price'=>"10,00€"],
        'module_shop'=>['link'=>'module_shop','name'=>"module boutique",'title'=>"Annonce - offres - promotions",'desc'=>"Ajoute la fonctionalité de publication d'offres, d'annonces.",'price'=>"10,00€"],
        'module_blog'=>['link'=>'module_blog','name'=>"module blog",'title'=>"Votre blog",'desc'=>"Affichez vos articles sur votre localité",'price'=>"free"],
        'module_private'=>['link'=>'module_private','name'=>"Module conversation privée",'title'=>"conversations privées",'desc'=>"votre messagerie sécurisée",'price'=>"10,00€"],
        'module_reservation'=>['link'=>'module_reservation','name'=>"module de réservation",'title'=>"réservations en ligne",'desc'=>"proposer un service de reéservation en ligne pour vos annons, menus, articles",'price'=>"10,00€"],
        'module_found'=>['link'=>'module_found','name'=>"module de gestion de menu en ligne",'title'=>"publiez vos ressources",'desc'=>"mettre en ligne vos astuces, mini tuto et répertoire technique sur le numérique",'price'=>"free"],
        'module_offer'=>['link'=>'module_offer','name'=>"module de petite annonce emploi",'title'=>"offre d'emploi",'desc'=>"petites annonces emploi",'price'=>"10,00€"],
    );

    public function selectModule($website): array
    {
        $moduletab=[];
        $tt=[];
        foreach ($website->getListmodules() as $module){
            $tt[]=$module->getClassmodule();
        }
        foreach (DefaultModules::TAB_MODULES as $key){
            $moduletab[$key]=in_array($key,$tt);
        }
        return $moduletab;
    }


    // todo ces function n'ont certaineùme t plus d'utilité a voir
    public function tabModulor($tabmodule): array
    {
        $listmodule=[];
        foreach ($tabmodule['listmodules'] as $module) {
            $listmodule[$module['classmodule']] = $module['keymodule'];
        }
        return $listmodule;
    }

    public function tabModulorObj($modulelist): array
    {
        $listmodule=[];
        foreach ($modulelist->getListmodules() as $module) {
            $listmodule[$module['classmodule']] = $module['keymodule'];
        }
        return $listmodule;
    }
}