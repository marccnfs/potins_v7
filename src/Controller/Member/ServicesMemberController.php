<?php


namespace App\Controller\Member;


use App\Classe\MemberSession;
use App\Lib\Links;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_CUSTOMER')]
#[Route('/customer/services/directive/')]

class ServicesMemberController extends AbstractController
{
    use MemberSession;


    #[Route('list-module', name:"list_module_customer")]
    public function listModuleCustomer(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $tabservice=[];
        if(!$this->member) return $this->redirectToRoute('cargo_public');
        $this->activeBoard();

        $listservices=$this->member->getCustomer()->getServices();

        foreach ($listservices as $list) {
            $tabservice[]=$list->getNamemodule();
        }

        $vartwig=$this->menuNav->newtemplateControlCustomer(
            Links::CUSTOMER_LIST,
            'modules',
            "services actifs",
            5);

        return $this->render('aff_account/home.html.twig', [
            'directory'=>'profil',
            'replacejs'=>$replace??null,
            'board'=>$this->board,
            'services'=>$tabservice,
            'dispatch'=>$this->member,
            'vartwig'=>$vartwig,
            'permissions'=>[],
        ]);
    }
}