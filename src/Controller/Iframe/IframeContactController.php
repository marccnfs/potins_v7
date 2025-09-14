<?php


namespace App\Controller\Iframe;

use App\Classe\potinsession;
use App\Lib\Links;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/msg/iframe/')]


class IframeContactController extends AbstractController
{
    use potinsession;


#[Route('newmasg/{key}', name:'contact_iframe_msg')]
public function newMessageIframeContact(WebsiteRepository $websiteRepository,$key): RedirectResponse|Response
    {

        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $uri=$_SERVER['REQUEST_URI'];
        $ref = $_SERVER['HTTP_REFERER'] ?? 'none';

       //$session->set('tabinfoip',['ip'=>$ip, 'uri'=>$uri,'ref'=>$ref]);

        /** @var Website $website */
        $this->board=$websiteRepository->findWbByKey($key);
        if(!isset($this->board))throw new Exception('board inconnu');
        $follow=true;
        $vartwig = $this->menuNav->websiteinfoObj($this->board, 'iframecontactWb', $this->board->getNamewebsite(), 'visitor');

        /*
        if($website->getContactation()->getKeycontactation()!==$key)return $this->redirectToRoute('api-error', ['err' => 1]);
       */

        return $this->render('aff_frame/home.html.twig', [
            'directory'=>'website',
            'dispatch'=>$this->dispatch??null,
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'isfollow'=>$follow,
            'website'=>$this->board,
            'board'=>$this->board,
            'infowb'=>true,
            'pw'=>$this->pw??false,
        ]);
    }


    #[Route('/message_iframe_website', name:'message_iframe_website')] //todo revoir ce retour
    public function reponseApi($spaceWeb, $contact=null, $id=null): Response
    {

        if($this->isGranted("ROLE_CUSTOMER")){
            $this->addFlash('newmsg', 'votre message a bien été adressé à : '.$spaceWeb.'.');
            return $this->redirectToRoute('index_customer',[],302);
        }else {
            $this->addFlash('newmsg', 'Merci '.$contact.' , votre message a bien été adressé à : '.$spaceWeb.'.');
            $vartwig = $this->menuNav->templateControl(
                null,
                Links::CUSTOMER_LIST,
                'customer/reponse',
                "customer",
                'customer');
            return $this->redirectToRoute('confirm-flash', [], 302);
        }
    }



}