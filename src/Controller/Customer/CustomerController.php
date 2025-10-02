<?php


namespace App\Controller\Customer;

use App\Classe\customersession;
use App\Classe\UserSessionTraitOld;
use App\Lib\Links;
use App\Repository\OrdersRepository;
use App\Repository\PostRepository;
use App\Repository\SuiviNotifRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_CUSTOMER')]


class CustomerController extends AbstractController
{
    use UserSessionTraitOld;

    #[Route('/espace-personnel/usager', name:"customer_space")] //TODO liste des activités (faites et a faire
    public function spaceCustomer(): Response
    {

        $vartwig=$this->menuNav->templatepotins('listpotins', Links::PUBLIC);

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>"main",
            'potins'=>[],
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'customer'=>$this->customer,
            'locatecity'=>0
        ]);
    }

    #[Route('/espace-personnel/agenda', name:"customer_agenda")] // todo liste agenda des inscriptions
    public function agendaCustomer(OrdersRepository $orderrepo): Response
    {
        $agenda=$orderrepo->findResaCustomer($this->customer->getNumclient());


        $vartwig=$this->menuNav->templatepotins('agendaresa', Links::PUBLIC);

        return $this->render($this->useragentP.'ptn_customer/home.html.twig', [
            'directory'=>"main",
            'potins'=>[],
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'customer'=>$this->customer,
            'orders'=>$agenda
        ]);
    }



    #[Route('/board/potins/{usg}', name:"list_potins_usg")] // todo show detail potins réalisé
    public function ospacePotins(SuiviNotifRepository $notifRepository, PostRepository $postationRepository, $city=null, $nameboard=null, $id=null ): Response
    {
        if($id){
            if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        }else{
            if(!$this->member) throw new Exception('dispatch inconnu');
            $this->activeBoard($nameboard);
        }
        $notifs=$notifRepository->findBy([
            "member"=>$this->member->getId()
        ]);
        $posts=$postationRepository->findPstKey($this->board->getCodesite());
        $vartwig=$this->menuNav->templatingadmin(
            'ospaceblog',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render('aff_customer/home.html.twig', [
            'directory'=>"board",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'dispatch'=>$this->member,
            'posts'=>array_reverse($posts),
            'locatecity'=>0
        ]);
    }




    #[Route('/board/shop/potins/{usg}', name:"shop_potins_usg")] //todo detail inscription
    public function ospaceShop(SuiviNotifRepository $notifRepository, OffresRepository $offresRepository, $city=null, $nameboard=null, $id=null ): Response
    {
        if($id){
            if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');
        }else{
            if(!$this->dispatch) throw new Exception('dispatch inconnu');
            $this->activeBoard($nameboard);
        }
        $notifs=$notifRepository->findBy([
            "member"=>$this->dispatch->getId()
        ]);
        $shops=$offresRepository->findOffreKey($this->board->getCodesite());

        $vartwig=$this->menuNav->templatingadmin(
            'ospaceshop',
            $this->board->getNamewebsite(),
            $this->board,
            4
        );
        return $this->render('aff_customer/home.html.twig', [
            'directory'=>"board",
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'dispatch'=>$this->dispatch,
            'shops'=>array_reverse($shops),
            'locatecity'=>0
        ]);
    }

}
