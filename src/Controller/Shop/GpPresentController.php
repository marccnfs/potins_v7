<?php


namespace App\Controller\Shop;

use App\Classe\MemberSession;
use App\Entity\Module\GpRessources;
use App\Form\DeleteType;
use App\Form\GpRessourceType;
use App\Module\GpRessourcator;
use App\Repository\GpRessourcesRepository;
use App\Repository\PostRepository;
use App\Service\Search\SearchRessources;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_MEMBER')]
#[Route('/member/gppresentss')]

class GpPresentController extends AbstractController
{
    use MemberSession;

    #[Route('/new-group_presents/{id}', name:"new_group_presents")]
    public function newGroupPresent(Request $request, SearchRessources $searchcarte, GpRessourcator $ressourcator, $id=null): RedirectResponse|Response
    {
        $tabcarte=$searchcarte->findCarte($this->board->getCodesite());

        $gpressources = new GpRessources();
        $form=$this->createForm(GpRessourceType::class, $gpressources);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $articles=json_decode($request->request->get('formules')['listarticle']);
            $ressourcator->newGpRessources($gpressources, $id, $articles, $this->board->getCodesite());
            $this->addFlash('infoprovider', 'nouvelle formule créée.');
            return $this->redirectToRoute('module_ressources', ['board'=>$this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->templatingadmin(
            'new',
            $this->board->getNameboard(),
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>false,
            'form' => $form->createView(),
            'tabcarte'=>$tabcarte,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
        ]);

    }


    #[Route('/edit-group-present/{id}', name:"group_present_edit")]
    public function editFormule(Request $request, PostRepository $postRepository,SearchRessources $searchRessources, GpRessourcesRepository $formulesRepository, GpRessourcator $ressourcator, $id): RedirectResponse|Response
    {
        $potins=$postRepository->find($id);
        if(!$potins->getGpressources()){
            $gpressources = new GpRessources();
            $potins->setGpressources($gpressources);

        }else {
            $tabRessources = $searchRessources->findGroupeRessourcesOfPotins($id);
            $gpressources=$searchRessources->findGpRessource($potins->getGpressources()->getId());
        }

        $tabRessources=$searchRessources->findAllCartes();


        $form=$this->createForm(GpRessourceType::class, $gpressources);

       // $form->get('listarticle')->setData(json_encode($formulator->getlistarticles($formule)));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $articles=json_decode($request->request->get('formules')['listarticle']);
            $ressourcator->editFormule($form, $gpressources,  $articles, $this->board);
            $this->addFlash('infoprovider', 'mise à jour effectuée.');
            return $this->redirectToRoute('module_found', ['board' => $this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->templatingadmin(
            'edit',
            "edition ressources",
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'gpressources',
            'replacejs'=>false,
            'form' => $form->createView(),
            'board'=>$this->board,
            'tabcarte'=>$tabRessources,
            'formule'=>$gpressources,
            'vartwig'=>$vartwig,
            'admin'=>[true,[1,1,1]],
            'city'=>null,
            'locatecity'=>0,
            'back'=> $this->generateUrl('module_found',['board' => $this->board->getSlug()]),
            'member'=>$this->member,
            'customer'=>$this->customer
        ]);
    }



    #[Route('/form-delete-gppresent/{id}', name:"form-delete_gppresents")]
    public function deleteFormule(Request $request, GpRessourcesRepository $formulesRepository, GpRessourcator $ressourcator, $id): RedirectResponse|Response
    {
        if(!$formule=$formulesRepository->findFormuleById($id)) throw new Exception('formule introuvable');

        $form = $this->createForm(DeleteType::class, $formule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $ressourcator->removeFormule($formule);
            return $this->redirectToRoute('module_found', ['board' => $this->board->getSlug()]);
        }
        $vartwig = $this->menuNav->templatingadmin(
        'delete',
        'delete formule',
            $this->board,5);


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
        'directory'=>'ressources',
        'replacejs'=>false,
        'form' => $form->createView(),
        'board'=>$this->board,
        'vartwig' => $vartwig,
        'author'=> true,
        'admin'=>[true,[1,1,1]],
        'city'=>null,
        'locatecity'=>0
        ]);
    }

}