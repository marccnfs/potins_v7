<?php


namespace App\Controller\BoardOffice;

use App\Classe\potinsession;
use App\Entity\HyperCom\TagAnalytic;
use App\Repository\BoardRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/hypercomanalytic/')]
class HyperComAnalyticController extends AbstractController
{
    use potinsession;


    public function Tagclic(Request $request, BoardRepository $repowebsite, UserPasswordEncoderInterface $passwordEncoder){

        if(!$request->isXmlHttpRequest()){
            $submit=$request->request->all();
            $wb=$submit['website'];
            $key=$submit['key']; //le code secret du site appellant (variable twig app_wbstite)
            $tag=$submit['tag'];
            $website=$repowebsite->findWebsiteCodesite($key);
            if($website->getId()==$wb){
                if($tag!=null){
                    $clic=new TagAnalytic();
                    $clic->setWebsite($website);
                    $clic->setTagname($tag);
                    $this->em->persist($clic);
                    $this->em->flush();
                    $response = new JsonResponse();
                    $response->setData(['succes' => true ]);
                    return $response;
                }
            }
        }
        $response = new JsonResponse();
        $response->setData(['succes' => false, 'error'=>'requete pas acceptée']);
        return $response;
    }

}