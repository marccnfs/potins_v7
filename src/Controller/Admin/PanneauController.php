<?php


namespace App\Controller\Admin;


use App\Classe\adminsession;
use App\Entity\Boards\Opendays;
use App\Entity\Sector\Adresses;
use App\Event\WebsiteCreatedEvent;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Repository\BoardRepository;
use App\Service\Localisation\LocalisationServices;
use App\Service\SpaceWeb\BoardlistFactor;
use App\Service\SpaceWeb\Tagatot;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Form\WebsiteType;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[Route('/board-affilink/')]

class PanneauController extends AbstractController
{

    use adminsession;


    #[Route('new/', name:"new_panneau_admin")]
    public function newPanneauAdmin(Request $request, BoardlistFactor $spaceWebtor, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $form = $this->createFormBuilder()
        ->add('idcity', HiddenType::class)
        ->add('namewebsite', HiddenType::class)
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $website = $spaceWebtor->addWebsiteLocalityAdmin($this->dispatch,  $form);
            $key=$website->getSlug().bin2hex(random_bytes(16));
            $website->setCodesite($key);
            $em->persist($website);
            $em->flush();
            return $this->redirectToRoute('edit_panneau_admin',['id'=>$website->getId()]);
        }

        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'newpanneau',
            "",
            'all');

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'website',
            'customer'=>$this->dispatch,
            'form'=>$form->createView(),
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('edit/{id}', name:"edit_panneau_admin")]
    public function editPanneauAdmin(Request $request, EventDispatcherInterface $dispatcher, Tagatot $tagatot, BoardlistFactor $spaceWebtor, BoardRepository $websiteRepository, $id): Response
    {
        $website=$websiteRepository->find($id);
        $tags=$website->getTemplate()->getTagueries();
        $tx="";
        foreach ($tags as $tag){
            $tx.=html_entity_decode ($tag->getName()).",";
        }
        $form=$this->createForm(WebsiteType::class,$website);
        $form['template']['tagueries']->setData($tx);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $website=$spaceWebtor->initTemplate($website, $form);
            $tagatot->majTagCat($website);
            $event= new WebsiteCreatedEvent($website);
            $dispatcher->dispatch($event, WebsiteCreatedEvent::CREATE);
            return $this->redirectToRoute('back_admin_websites',['keyboard'=>'v5-12020test']);
        }
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'editpanneau',
            "",
            'all');

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'website',
            'customer'=>$this->dispatch,
            'form'=>$form->createView(),
            'board'=>$website,
            'website'=>$website,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('members/{id}', name:"members_admin")]
    public function membersWebsiteAdmin($id, Request $request, BoardlistFactor $spaceWebtor, BoardRepository $websiteRepository): RedirectResponse|Response
    {
        $website=$websiteRepository->find($id);
        $form=$this->createForm(MailType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $mail=$form['mail']->getData();
            if(!$dispatch=$this->getDispatchByEmail($mail)){ // todo : attention si email change c'est bien le bon ??
              /*  $tabmember=[
                    "contact"=>null,
                    "type"=>"member",
                    "board"=>$website,
                    "mail"=>$mail,
                    "pass"=>false,
                    "name"=>false];
              */
                $spaceWebtor->invitMailToAdmin($mail, $website); // dotation d'un website a un tiers (adresse mail, non contact) en tant que superadmin
            }else{
                $spaceWebtor->addwebsitedispatch($dispatch,$website);// dotation d'un website a un dispatch en tant que superadmin
            }
            return $this->redirectToRoute('members_admin', ['id'=>$website->getId()]);
        }

        $vartwig=$this->menuNav->templatingadmin(
            'members',
            'parametres du panneau',
            $website,1);

        return $this->render('aff_master/home.html.twig', [
            'form'=>$form->createView(),
            'board'=>$website,
            'website'=>$website,
            'dispatch'=>$this->dispatch,
            'directory'=>'website',
            'spwsites'=>$website->getBoardslist(),
            'vartwig'=>$vartwig,
        ]);
    }

    /**
     * imputation des adresses à un webspace
     */

    #[Route('localizeAdmin/{id}', name:"spaceweblocalize_init_admin")]
    public function localizeWebsiteAdmin(BoardRepository $websiteRepository, $id): Response
    {
        $board=$websiteRepository->find($id);

        $vartwig=$this->menuNav->templatingadmin(
            'localizer',
            'parametres du panneau',
            $board,3);

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'website',
            'vartwig' => $vartwig,
            'board'=>$board,
            'website'=>$board,
            'dispatch'=>$this->dispatch,
        ]);
    }


    #[Route('newadressAdmin', name:"newadress_admin")]
    public function newAdressWebsiteAdmin(Request $request, LocalisationServices $localisation, BoardRepository $websiteRepository): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $website=$websiteRepository->find($data['id']);
            if(!$website) return new JsonResponse(['success'=>false,'error'=>'merdum ici : id =>'.$data['id']]);
            $adress=$localisation->newAdress($data,  $website, 1);
            if($adress!=null){
                $website->setStatut(true);
                $this->em->persist($website);
                $this->em->flush();
                $responseCode = 200;
                http_response_code($responseCode);
                header('Content-Type: application/json');
                return new JsonResponse(['success'=>true, "label"=>$data['properties']['label']]);
            }
            return new JsonResponse(['success'=>false,"error"=>"adresse pas enregistrée"]);
        }
        return new JsonResponse(['success'=>false,"error"=>"requete erreur"]);
    }


    #[Route('deleteadressAdmin/{id}', name:"deleteadress_admin")]
    public function deleteAdressWebsiteAdmin(Request $request, BoardRepository $websiteRepository, $id): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $idwebsite=$request->request->get('website');
            $website=$websiteRepository->find($idwebsite);
            if(!$website)  return new JsonResponse(['success'=>false,"error"=>"id spaceweb non reconnu"]);
            $adresses=$website->getTemplate()->getSector()->getAdresse();
            /** @var Adresses $adress */
            foreach ($adresses as $adress) {
                if ($adress->getId() == $id) {
                    $this->em->remove($adress);
                    $this->em->flush();
                    $responseCode = 200;
                    http_response_code($responseCode);
                    header('Content-Type: application/json');
                    return new JsonResponse(['success'=>true]);
                }
            }
        }
        return new JsonResponse(['success'=>false,"error"=>"requete ajax non reconnue"]);
    }


    #[Route('horaires/profil-opendaysAdmin/{id}', name:"opendays_edit_admin")]
    public function editOpenDaysAdmin($id,BoardRepository $websiteRepository): RedirectResponse|Response //todo le reôsitory $id est un website
    {
        $board=$websiteRepository->find($id);
        $tabunique="";
        $tabconges="";
        if($opendays=$board->getTabopendays()){
            $tabunique=$opendays->getTabuniquejso()??[];
            $tabconges=$opendays->getCongesjso()??[];
        }

        $vartwig=$this->menuNav->templatingadmin(
            'openday',
            'parametres du panneau',
            $board,5);

        return $this->render('aff_master/home.html.twig', [
            'vartwig'=>$vartwig,
            'directory'=>'website',
            'board'=>$board,
            'website'=>$board,
            'twigtbunique'=>$tabunique,
            'twigconges'=>$tabconges,
        ]);
    }


    #[Route('init-opendays/jx', name:"init-opendays-ajx")] // todo a revoir ? this->board
    public function majOpenDaysAjx(Request $request, BoardRepository $websiteRepository): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            if(!$this->getUserspwsiteOfWebsiteSlug($data['slug']) || !$this->admin) return new JsonResponse(MsgAjax::MSG_ERR3);

            $opendays=$this->board->getTabopendays();
            if(!$opendays){
                $opendays=New Opendays();
                $this->board->setTabopendays($opendays);
            }
            $opendays->setTabunique($data['tabunique']);
            $opendays->setConges($data['conges']);
            $opendays->setCongesjso(json_decode($data['conges'], true));
            $opendays->setTabuniquejso(json_decode($data['tabunique'], true));
            //$result['notify']=$data['notify'];
            //$result['spaceweb']=$data['order'];
            //$result['order']=$data['spaceweb'];
            $this->em->persist($this->board);
            $this->em->flush();
            $issue=MsgAjax::MSG_COMLETED; // TODO pas sur que necessaire
            $issue['openday']=$opendays->getId();
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERR4);
        }
    }



}
