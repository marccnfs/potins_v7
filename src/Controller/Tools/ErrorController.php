<?php


namespace App\Controller\Tools;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/toolsOld/error')]
class ErrorController extends AbstractController
{
    use UserSessionTrait;


    #[Route('/erreur_requete/{err}}', name:"api-error")]
    public function errorApi($err)
    {
        switch ($err){
            case 1:
                $msg="la clé de vérification n'est pas valide";
                break;

            default:
                $msg="erreur requete ajax";
                break;
        }
        $vartwig=$this->menuNav->templateControl(
            null,
            Links::CUSTOMER_LIST,
            'main_public/api/exception/msg',
            "",
            "messager");

        return $this->render('public/page/home.html.twig', [
            'agent'=>$this->useragent,
            'vartwig'=>$vartwig,
            "msg"=>$msg,
            'admin'=>[$this->admin,$this->permission]
        ]);
    }
}
