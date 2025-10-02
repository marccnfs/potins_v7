<?php


namespace App\Controller\Shop;

use App\Classe\MemberSession;
use App\Form\DeleteType;
use App\Lib\MsgAjax;
use App\Module\Presentscator;
use App\Module\Ressourcecator;
use App\Repository\CategoriesRepository;
use App\Repository\OffresRepository;
use App\Repository\RessourcesRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_MEMBER')]
#[Route('/member/presents')]

class PresentsController extends AbstractController
{
    use MemberSession;


    #[Route('/manage-presents-ajx', name:"manage_presents_ajx")]
    public function ManagePresentAjax(Request $request, Presentscator $presentscator): JsonResponse
    {

        if($request->isXmlHttpRequest())
        {

            $key=$this->board->getCodesite();
            $result['titre']=$request->request->get('titre');
            $result['descriptif']=$request->request->get('descriptif');
            $result['compo']=$request->request->get('composition');
            $result['contenthtml']=$request->request->get('contenthtml');
            $result['offre']=$request->request->get('offre');
            $result['edit']= $request->request->get('edit');
            $result['pict']=$request->request->get('base64');
            $result['cat']=$request->request->get('cat');
            $issue=$presentscator->ManagePresentAjax($result, $key);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }


    /**
     * @throws NonUniqueResultException
     */
    #[Route('/new-present/{id}', name:"new_present")]
    public function newPresent(OffresRepository $offresRepository, CategoriesRepository $catrepository, $id): RedirectResponse|Response
    {

        $offre=$offresRepository->findOneOffre($id);
        $cats=$catrepository->findAll();

        $vartwig=$this->menuNav->templatingadmin(
            'newpresent',
            $this->board->getNameboard(),
            $this->board,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'present',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'offre'=>$offre,
            'edit'=>0,
            'vartwig'=>$vartwig,
            'cats'=>$cats
        ]);

    }

    #[Route('/edit-present/{id}', name:"edit_present")]
    public function editPresent(RessourcesRepository $ressourcerepo,CategoriesRepository $catrepository, $id): RedirectResponse|Response
    {

        $content="";
        $cats=$catrepository->findAll();
        if(!$rssc=$ressourcerepo->findForEdit($id)[0])return $this->redirectToRoute('api-error',['err'=>2]);

        if($rssc->getHtmlcontent()){
            if($rssc->getHtmlcontent()->getFileblob() && file_exists($rssc->getHtmlcontent()->getWebPathblob())){
                $content=file_get_contents($rssc->getHtmlcontent()->getWebPathblob());
            }
        }

        $vartwig=$this->menuNav->templatingadmin(
            'edit',
            "edition du cadeau",
            $this->board,5);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'present',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'rssc'=>$rssc,
            'content'=>$content,
            'vartwig'=>$vartwig,
            'cats'=>$cats
        ]);
    }


    #[Route('/form-delete-present/{id}', name:"delete_present")]
    public function deletePresent(Request $request, RessourcesRepository $ressourcerepo, Ressourcecator $ressourcecator, $id): RedirectResponse|Response
    {
        if(!$ressource = $ressourcerepo->find($id)) throw new Exception('carte introuvable');

        $form = $this->createForm(DeleteType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $ressourcecator->removeRessource($ressource);
            return $this->redirectToRoute('module_ressources', ['board'=>$this->board->getSlug()]);        }

        $vartwig = $this->menuNav->templatingadmin(
            'delete',
            'delete present',
            $this->board,5);


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'replacejs'=>false,
            'form' => $form->createView(),
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'carte'=>$ressource,
            'vartwig'=>$vartwig,
            'directory'=>'present',
            'admin'=>[true,[1,1,1]]
        ]);
    }

}
