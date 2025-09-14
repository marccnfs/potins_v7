<?php


namespace App\Controller\Reviews;

use App\Classe\MemberSession;
use App\Form\DeleteType;
use App\Form\GpReviewType;
use App\Module\GpRessourcator;
use App\Module\Reviewscator;
use App\Repository\GpRessourcesRepository;
use App\Repository\GpReviewRepository;
use App\Repository\PostRepository;
use App\Service\Search\SearchRessources;
use App\Service\Search\SearchReviews;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[IsGranted('ROLE_MEMBER')]
#[Route('/member/gpreview')]

class GpReviewController extends AbstractController
{
    use MemberSession;


    #[Route('/manage-group_review/{id}', name:"manage_group_review")]
    public function createGroupRessources(PostRepository $postRepository,Request $request, SearchReviews $searchreviews, Reviewscator $reviewscator, $id): RedirectResponse|Response
    {
        $post=$postRepository->findPostAndGpById($id);
        $gpreview=$post->getGpreview();

        if(!$gpreview){
            $gpreview=$reviewscator->CreateGpReview($post, $this->member);
        }

        $tabreview=$gpreview->getReviews();

        $vartwig=$this->menuNav->templatingadmin(
            'ospacegpreview',
            $this->board->getNameboard(),
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'review',
            'replacejs'=>false,
            'tabreviews'=>$tabreview,
            'gpreview'=>$gpreview,
            'post'=>$post,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer
        ]);

    }


    #[Route('/edit-group-review/{id}', name:"edit_group_review")]
    public function editgroupereview(Request $request, GpReviewRepository $gpReviewRepository,SearchRessources $searchRessources,Reviewscator $reviewscator, $id): RedirectResponse|Response
    {
        $gpreveiew=$gpReviewRepository->find($id);
        $form=$this->createForm(GpReviewType::class, $gpreveiew);

       // $form->get('listarticle')->setData(json_encode($formulator->getlistarticles($formule)));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $reviewscator->majGpReview($form, $gpreveiew);
            return $this->redirectToRoute('manage_group_review', ['id' => $gpreveiew->getPotin()->getId()]);
        }

        $vartwig=$this->menuNav->templatingadmin(
            'editgpreview',
            "edition gpreview",
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'review',
            'replacejs'=>false,
            'board'=>$this->board,
            'form' => $form->createView(),
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer
        ]);
    }



    #[Route('/form-delete-review/{id}', name:"form-delete_review")]
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