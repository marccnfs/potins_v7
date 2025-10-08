<?php

namespace App\Controller\BoardOffice;

use App\Classe\UserSessionTrait;
use App\Entity\Boards\Opendays;
use App\Event\WebsiteCreatedEvent;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Repository\BoardRepository;
use App\Repository\PostRepository;
use App\Repository\SectorsRepository;
use App\Service\SpaceWeb\BoardlistFactor;
use App\Service\SpaceWeb\Tagatot;
use App\Util\DefaultModules;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Form\WebsiteType;


//#[IsGranted('ROLE_MEDIA')]
#[Route('mediatheque/param/')]

class ParametresBoardController extends AbstractController
{
    use UserSessionTrait;

    #[Route('parameters', name:"parameters")]
    public function parametersWebsite(SectorsRepository $reposector, PostRepository $postationRepository): Response
    {

        $vartwig=$this->menuNav->admin(
            $this->board,
            'parameter',
            links::ADMIN,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'parameters',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->currentMember(),
            'test1'=>(bool)(($email = $this->board->getTemplate()->getEmailspaceweb())),
            'test2'=>(bool)(($reposector->findOneBy(['codesite'=>$this->board->getCodesite()]))),
            'email'=>$email,
            'posts'=>$postationRepository->countPost($this->board->getCodesite()),
          //  'shops'=>$offresRepository->countOffre($this->board->getCodesite()),
            'openday'=>$this->board->getTabopendays()??null,
            'vartwig'=>$vartwig,
            'admin'=>[true,[1,1,1]],
        ]);
    }


    #[Route('modules/{id}', name:"spaceweb_mod")]
    public function tabModulesWp($id, DefaultModules $defaultModules): RedirectResponse|Response
    {
       // if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        $moduletab=$defaultModules->selectModule($this->board);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'stateModules',
            links::ADMIN,
            2
        );


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'parameters',
            'replacejs'=>false,
            'vartwig' => $vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'tabmodule'=>$moduletab,
            'admin'=>[true,[1,1,1]],
        ]);
    }


    #[Route('edit-website/{id}', name:"website_edit")]
    public function editWebsite($id, Request $request, Tagatot $tagatot, BoardlistFactor $spaceWebtor, EventDispatcherInterface $dispatcher): RedirectResponse|Response
    {
      //  if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        $tags=$this->board->getTemplate()->getTagueries();
        $tx="";
        foreach ($tags as $tag){
            $tx.=html_entity_decode ($tag->getName()).",";
        }
        $form=$this->createForm(WebsiteType::class,$this->board);
        $form['template']['tagueries']->setData($tx);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $spaceWebtor->initTemplate($this->board, $form);
            $tagatot->majTagCat($this->board);
            //suprimé en dev // todo ne pas oublier de remettre actif en televersement
            $event= new WebsiteCreatedEvent($this->board);
            $dispatcher->dispatch($event, WebsiteCreatedEvent::MAJ);
            return $this->redirectToRoute('parameters',[
                'id'=>$this->board->getId()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'update',
            links::ADMIN,
            4
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'parameters',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->member,
            'vartwig'=>$vartwig,
            'form'=>$form->createView(),
            'admin'=>[true,[1,1,1]],
        ]);
    }


    #[Route('horaires/profil-opendays/{id}', name:"opendays_edit")]
    public function editOpenDays($id): RedirectResponse|Response //todo le reôsitory $id est un website
    {
      //  if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        $tabunique="";
        $tabconges="";
        if($opendays=$this->board->getTabopendays()){
            $tabunique=$opendays->getTabuniquejso()??[];
            $tabconges=$opendays->getCongesjso()??[];
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'openday',
            links::ADMIN,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'vartwig'=>$vartwig,
            'directory'=>'parameters',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->member,
            'twigtbunique'=>$tabunique,
            'twigconges'=>$tabconges,
            'admin'=>[true,[1,1,1]],
        ]);
    }



    #[Route('init-opendays/jx', name:"init-opendays-ajx")]
    public function majOpenDaysAjx(Request $request, BoardRepository $websiteRepository): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);

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
