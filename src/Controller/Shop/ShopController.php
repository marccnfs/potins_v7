<?php


namespace App\Controller\Shop;

use App\Classe\MemberSession;
use App\Entity\Marketplace\Offres;
use App\Form\DeleteType;
use App\Lib\MsgAjax;
use App\Module\Offrator;
use App\Module\Shopator;
use App\Repository\BoardRepository;
use App\Repository\OffresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/member/marketplace/shop/')]
#[IsGranted("ROLE_MEMBER")]

class ShopController extends AbstractController
{
    use MemberSession;

    #[Route('add-offre-ajx', name:"add_offre_ajx")]
    public function AddPromoAjx(Request $request, Shopator $shopator,BoardRepository $websiteRepository): JsonResponse
    {

        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $issue = $shopator->newOffre($this->member, $this->board, $data);
            return new JsonResponse($issue);
           // $submittedToken = $request->request->get('token');
           // if ($this->isCsrfTokenValid('edit-offre', $submittedToken)) {
           // }
        }
        return new JsonResponse(MsgAjax::MSG_ERRORRQ);
    }

    #[Route('/new-offre/', name:"new_generic_offre")]
    #[Route('new-offre/{id}/', name:"new_offre")]
    public function newOffre(Shopator $shopator, $id): Response
    {
        if($id!=$this->board->getId()) $this->redirectToRoute('list_board');
        $taboffre=$shopator->preNewOffre();

        $vartwig=$this->menuNav->templatingadmin(
            'newoffre',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'offre',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'taboffre'=>json_encode($taboffre),
            'offre'=>0,
            "content"=>"",
            'vartwig'=>$vartwig,
        ]);
    }



    #[Route('editoffre/{id}', name:"edit_offre")]
    public function editOffre(OffresRepository $offresRepository,Offrator $offrator, $id): RedirectResponse|Response
    {
        /** @var Offres $offre */ //todo a revoir je pense trop de requete
        if(!$offre=$offresRepository->findPstQ2($id))return $this->redirectToRoute('api-error',['err'=>2]);
        $this->initBoardByKey($offre->getKeymodule());
        $taboffre=$offrator->preEditOffre($offre);
        /** @var Spwsite $pw */

        $vartwig=$this->menuNav->templatingadmin(
            'edit',
            "offre",
            $this->board,4);
        return $this->render('aff_websiteadmin/home.html.twig', [
            'directory'=>'shop',
            'replacejs'=>false,
            'website'=>$this->board,
            'board'=>$this->board,
            'product'=>$offre->getProduct(),
            'offre'=>$offre,
            'tabunique'=>json_encode(explode(';',$offre->getTabunique())),
            'taboffre'=>json_encode($taboffre),
            'vartwig'=>$vartwig,
            'admin'=>[$this->admin,$this->permission],
            'locatecity'=>0,
            'back'=> $this->generateUrl('module_shop',['city'=>$this->board->getLocality()[0]->getCity(),'nameboard' => $this->board->getSlug()]),
        ]);
    }



    #[Route('form-delete-offre/{id}', name:"form-delete_offre")]
    public function formdeletet(Request $request, OffresRepository $offresRepository, $id): RedirectResponse|Response
    {
        /** @var Offres $offre */
        if(!$offre=$offresRepository->findPstQ2($id, $this->iddispatch))return $this->redirectToRoute('api-error',['err'=>2]);
        $this->initBoardByKey($offre->getKeymodule());
        $form = $this->createForm(DeleteType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offre->setDeleted(true);
            $this->em->persist($offre);
            $this->em->flush();
            $this->addFlash('info', 'offre supprimé.');
            return $this->redirectToRoute('show_shop', ['id' => $this->board->getId()]);
        }
        $vartwig = $this->menuNav->templatingadmin(
            'deleteoffre',
            'delte offre',
            $this->board,4);

        return $this->render('aff_websiteadmin/home.html.twig', [
            'directory'=>'shop',
            'replacejs'=>false,
            'form' => $form->createView(),
            'website' => $this->board,
            'board'=>$this->board,
            'vartwig' => $vartwig,
            'author' => $offre->getAuthor()->getId()==$this->iddispatch,
            'admin'=>[$this->admin,$this->permission],
            'locatecity'=>0
        ]);
    }


    #[Route('/publied-offre/{id}', name:"publied_offre")]
    public function publiedOffre(OffresRepository $offresRepository, Offrator $offrator, $id): RedirectResponse|Response
    {
        if(!$offre=$offresRepository->findPstQ2($id, $this->iddispatch))return $this->redirectToRoute('api-error',['err'=>2]);
        /** @var Spwsite $pw */
        $this->initBoardByKey($offre->getKeymodule());
        if(!$pw) return $this->redirectToRoute('cargo_public');
        $offrator->publiedOneOffre($offre);
        return $this->redirectToRoute('boutique', ['id' => $this->board->getId()]);
    }

}