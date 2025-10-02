<?php


namespace App\Controller\Reviews;


use App\Classe\UserSessionTrait;
use App\Entity\Posts\Post;
use App\Form\DeleteType;
use App\Lib\Links;
use App\Repository\GpReviewRepository;
use App\Repository\PostRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/member/wb/post')]
#[IsGranted("ROLE_MEMBER")]

class ReviewController extends AbstractController
{
    use UserSessionTrait;

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/manageReviewResume/{id}/{gp}', name:"manage_review_resume")]
    public function ManageReviewResum(ReviewRepository $reviewRepository,GpReviewRepository $gpReviewRepository,$gp,$id): Response
    {
        $content='';
        if(!$gprw=$gpReviewRepository->findGpreviewsAll($gp))return $this->redirectToRoute('api-error',['err'=>2]);
        if($id!=='new'){
            $rw=$reviewRepository->findForEdit($id);
            if($rw->getFiche()->getFileblob() && file_exists($rw->getFiche()->getWebPathblob())){
                $content=file_get_contents($rw->getFiche()->getWebPathblob());
            }
        }else{
            $rw=['id'=>0,'titre'=>'','soustitre'=>'','type'=>'0'];  // 0 correspond a fiche resume
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'editreview',
            links::ADMIN,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'review',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>$gprw->getPotin()->getId(),
            'gprw'=>$gprw,
            'content'=>$content,
            'rw'=>$rw,
            'vartwig'=>$vartwig,
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/manageReviewTrame/{id}/{gp}', name:"manage_review_trame")]
    public function ManageReviewTrame(ReviewRepository $reviewRepository,GpReviewRepository $gpReviewRepository,$id,$gp): Response
    {
        $content='';
        if(!$gprw=$gpReviewRepository->findGpreviewsAll($gp))return $this->redirectToRoute('api-error',['err'=>2]);

        if($id!=='new'){
            $rw=$reviewRepository->findForEdit($id);
            if($rw->getFiche()->getFileblob() && file_exists($rw->getFiche()->getWebPathblob())){
                $content=file_get_contents($rw->getFiche()->getWebPathblob());
            }
        }else{
            $rw=['id'=>0,'titre'=>'','soustitre'=>'','type'=>'1'];  // 1 correspond a fiche trame
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'editreview',
            links::ADMIN,
            2
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'review',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>$gprw->getPotin()->getId(),
            'gprw'=>$gprw,
            'content'=>$content,
            'rw'=>$rw,
            'vartwig'=>$vartwig,
        ]);
    }


    #[Route('/form-delete/{id}', name:"form-delete_review")]  //todo
    public function deleteReview(Request $request, PostRepository $postRepository, $id): RedirectResponse|Response
    {
        /** @var Post $post */
        if(!$post=$postRepository->findPstQ0($id))return $this->redirectToRoute('api-error',['err'=>2]);
        $this->initBoardByKey($post->getKeymodule());
        $form = $this->createForm(DeleteType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setDeleted(true);
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('info', 'post supprimé.');
            return $this->redirectToRoute('module_blog', ['board'=>$this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'deletepost',
            links::ADMIN,
            2
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'form' => $form->createView(),
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig' => $vartwig,
            'author' => $post->getAuthor()->getId()==$this->member->getId(),
            'admin'=>[true,$this->member->getPermission()],
            'locatecity'=>0
        ]);
    }

}
