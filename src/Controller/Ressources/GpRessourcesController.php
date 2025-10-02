<?php


namespace App\Controller\Ressources;

use App\Classe\MemberSession;
use App\Entity\Module\GpRessources;
use App\Form\DeleteType;
use App\Form\DuplicateType;
use App\Form\GpRessourceType;
use App\Lib\Links;
use App\Module\GpRessourcator;
use App\Repository\GpRessourcesRepository;
use App\Repository\PostRepository;
use App\Service\Search\SearchRessources;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_MEMBER')]
#[Route('/member/gpressources')]

class GpRessourcesController extends AbstractController
{
    use MemberSession;


    #[Route('/new-group_ressources/{id}', name:"new_group_ressources")]
    public function newGroupRessources(Request $request, SearchRessources $searchcarte, GpRessourcator $ressourcator, $id=null): RedirectResponse|Response
    {
        $tabcarte=$searchcarte->findCarte($this->board->getCodesite());

        $gpressources = new GpRessources();
        $form=$this->createForm(GpRessourceType::class, $gpressources);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $articles=json_decode($request->request->get('gp_ressource')['listarticle']);
            $ressourcator->newGpRessources($gpressources, $id, $articles, $this->board->getCodesite());
            $this->addFlash('infoprovider', 'nouvelle formule créée.');
            return $this->redirectToRoute('module_ressources', ['board'=>$this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'new',
            links::ADMIN,
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


    #[Route('/edit-ressources/{id}', name:"group_ressources")]
    public function editFormule(Request $request, PostRepository $postRepository,SearchRessources $searchRessources, GpRessourcator $gpRessourcator, $id): RedirectResponse|Response
    {
        $potins=$postRepository->find($id);
        if(!$potins->getGpressources()){
            $gpressources = new GpRessources();
            $potins->setGpressources($gpressources);

        }else {
           // $tabRessources = $searchRessources->findGroupeRessourcesOfPotins($id);
            $gpressources=$searchRessources->findGpRessource($potins->getGpressources()->getId());
        }
        //$ressourcespotins=$searchRessources->findRessourcesOfPotins($gpressources->getId());
        //$listressources=$gpressources->getArticles();
        //$tabRessources = $searchRessources->findGroupeRessourcesOfPotins($id);
        $tabRessources=$searchRessources->findAllCartes();

        $form=$this->createForm(GpRessourceType::class, $gpressources);
        $form->get('listarticle')->setData(json_encode($gpRessourcator->getlistarticles($gpressources)));
       // $form->get('listarticle')->setData(json_encode($formulator->getlistarticles($formule)));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $data=$request->request->all();
            if($data['gp_ressource']['listarticle']){
                $articles = json_decode((string) $data['gp_ressource']['listarticle'], true);
            }else{
                $articles=[];
            }
            $gpRessourcator->editPotinRessources($form, $gpressources,  $articles, $potins);
            $this->addFlash('infoprovider', 'mise à jour effectuée.');
            return $this->redirectToRoute('office_member');
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'edit',
            links::ADMIN,
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
            //'admin'=>[true,[1,1,1]],
            //'city'=>null,
            'post'=>$potins,
            //'back'=> $this->generateUrl('module_found',['board' => $this->board->getSlug()]),
            'member'=>$this->member,
            'customer'=>$this->customer
        ]);
    }



    #[Route('/form-delete-formule/{id}', name:"form-delete_formule")]
    public function deleteFormule(Request $request, GpRessourcesRepository $formulesRepository, GpRessourcator $ressourcator, $id): RedirectResponse|Response
    {
        if(!$formule=$formulesRepository->findFormuleById($id)) throw new Exception('formule introuvable');

        $form = $this->createForm(DeleteType::class, $formule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $ressourcator->removeFormule($formule);
            return $this->redirectToRoute('module_found', ['board' => $this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'delete',
            links::ADMIN,
            5
        );
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


    #[Route('/form-duplicate-formule/{id}', name:"form-duplicate_formule")]
    public function duplicateFormule(Request $request, GpRessourcesRepository $formulesRepository, GpRessourcator $ressourcator, $id): RedirectResponse|Response
    {
        if(!$formule=$formulesRepository->findFormuleById($id)) throw new Exception('formule introuvable');
        $form = $this->createForm(DuplicateType::class, $formule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ressourcator->duplicateFormule($this->board,$formule->getKeymodule(), $formule);
            return $this->redirectToRoute('module_found', ['board' => $this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'duplicate',
            links::ADMIN,
            5
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'replacejs'=>false,
            'form' => $form->createView(),
            'website' => $this->board,
            'vartwig' => $vartwig,
            'directory'=>'ressources',
            'admin'=>[true,[1,1,1]],
            'city'=>$this->board->getLocality()[0]->getCity(),
            'locatecity'=>0
        ]);
    }


    #[Route('/form-publied-formule/{id}', name:"form-publied_formule")]
    public function publiedFormule(GpRessourcesRepository $formulesRepository, GpRessourcator $ressourcator, $id): RedirectResponse|Response
    {
        if(!$formule=$formulesRepository->findFormuleById($id)) throw new Exception('formule introuvable');

        $ressourcator->publiedFormule($formule);
        return $this->redirectToRoute('module_found', ['board' => $this->board->getSlug()]);
    }

}
