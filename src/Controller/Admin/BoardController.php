<?php


namespace App\Controller\Admin;


use App\Classe\UserSessionTrait;
use App\Entity\Customer\Services;
use App\Entity\Module\ModuleList;
use App\Entity\Boards\Board;
use App\Lib\Links;
use App\Repository\CustomersRepository;
use App\Repository\ModuleListRepository;
use App\Repository\ProductsRepository;
use App\Repository\WbordersRepository;
use App\Repository\BoardRepository;
use App\Service\backoffice\Initbacktor;
use App\Util\DefaultModules;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_SUPER_ADMIN')]
#[Route('/board-affilink/')]

class BoardController extends AbstractController // board-v5-1/back-admin/?keyboard=v5-12020test
{

    use UserSessionTrait;


    #[Route('back-admin', name:"admin_index")]
    public function backOffice(Initbacktor $initbacktor): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
       // if($keyboard!=$this->getParameter('key_admin')) $this->redirectToRoute('app_logout');

        $initback['fact']=$initbacktor->init();

        $vartwig=$this->menuNav->admin(
            $this->board,
            'boardadmin',
            Links::ADMIN,
            1);

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'admin',
            'init'=>$initback,
            'website'=>true,
            'customer'=>$this->currentCustomer(),
            'msgs'=>$msgs??[],
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('back-admin/maj-customer/function', name:"back_admin_maj_customer")]
    public function majCustomers(): Response
    {
        $vartwig=$this->menuNav->admin(
            $this->board,
            'functions',
            Links::ADMIN,
            1);


        return $this->render('aff_master/home.html.twig', [
            'directory'=>'customer',
            'website'=>true,
            'customer'=>$this->currentCustomer,
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('back-admin/maj-customer/maj-services', name:"back_admin_maj_services")]
    public function majServicesCustomer(EntityManagerInterface $em, CustomersRepository $customersRepository, ProductsRepository $productsRepository): Response
    {

        $customers=$customersRepository->findAll();
        dump($customers);
        $listcustomer=[];

        foreach ($customers as $customer){
            $tabService = [];

            $listservices=$customer->getServices();
            if($listservices) {
                foreach ($listservices as $servicelist) {
                    $tavservice[] = $servicelist->getNamemodule();
                }
            }

            foreach (DefaultModules::MODULE_LIST as $list){
                if(!in_array($list,$tabService)){
                    $service= new Services();
                    $service->setProducts($productsRepository->findOneProduct($list));
                    $service->setNamemodule($list);
                    $service->setDatestartAt(new DateTime());
                    $customer->addService($service);
                    $em->persist($customer);
                    $em->flush();
                    $listcustomer[] = $customer->getEmailcontact();
                }
            }
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'tabresults',
            Links::ADMIN,
            1);

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'customer',
            'admin'=>[true],
            'list'=>$listcustomer,
            'vartwig'=>$vartwig
        ]);
    }

    #[Route('back-admin/maj-customer/maj-lismodule', name:"back_admin_maj-lismodule")]
    public function majListModule(EntityManagerInterface $em,BoardRepository $websiteRepository, ModuleListRepository $moduleListRepository,CustomersRepository $customersRepository, ProductsRepository $productsRepository): Response
    {
        $websites=$websiteRepository->findAllAndPwAdmin('superadmin');
      $list=[];
        /** @var  Board $website */
        foreach ($websites as $website){
            $tabmodule=[];
           $listmodules = $website->getListmodules();
            /** @var  ModuleList $module */
            foreach ($listmodules as $module){
                $tabmodule[]=$module->getClassmodule();
            }

            $services=$website->getBoardslist()[0]->getDisptachwebsite()->getCustomer()->getServices();
            /** @var  Services $service */
            foreach ($services as $service){
               if(!in_array($service->getNameModule(),$tabmodule)){
                   $module= new ModuleList();
                   $module->setClassmodule($service->getNameModule());
                   $module->setKeymodule($website->getCodesite());
                   $module->setBoard($website);
                   $website->addListmodule($module);
                   $em->persist($website);
                   $em->flush();
                   $list[]=$website->getNameboard();
               }
           }
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'tabresults',
            Links::ADMIN,
            1);

        return $this->render('aff_master/home.html.twig', [
            'directory'=>'customer',
            'admin'=>[true],
            'lists'=>$list,
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('back-admin/websites/{keyboard}', name:"back_admin_websites")]
    public function adminWebsite($keyboard): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        if($keyboard!=$this->getParameter('key_admin')) $this->redirectToRoute('app_logout');
        $websites=$this->wbrepo->findAll();


        $vartwig=$this->menuNav->admin(
            $this->board,
            'websites',
            Links::ADMIN,
            1);

        return $this->render('aff_master/home.html.twig', [
            'customer'=>$this->dispatch,
            'directory'=>'admin',
            'websites'=>$websites,
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);

    }






    //todo voir tout le reste

    #[Route('back-admin/product/{keyboard}', name:"back_admin_product")]
    public function adminProduct($keyboard, SpwsiteRepository $spwsiteRepository, Initbacktor $initbacktor, WebsiteRepository $websiteRepository,ProductsRepository $productsRepository, DispatchSpaceWebRepository $dispatchSpaceWebRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        if($keyboard!=$this->getParameter('key_admin')) $this->redirectToRoute('app_logout');
        $website=$websiteRepository->find(3);
        $dispatch=$dispatchSpaceWebRepository->find(1);
        $pw=$spwsiteRepository->find(1);
        $initback['fact']=$initbacktor->init();
        $products=$productsRepository->findAll();
        $vartwig=[
            "title"=>'backoffice',
            "description"=>'backoffice',
            "keyword"=>'backoffice',
            'page'=>"products",
            "tagueries"=>[["name"=> "backinfo website"]],
        ];
        return $this->render('aff_master/home.html.twig', [
            'directory'=>'product',
            'init'=>$initback,
            'website'=>$website,
            'pw'>$pw,
            'products'=>$products,
            'dispatch'=>$dispatch,
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);
    }


    #[Route('back-admin/orders/{keyboard}', name:"back_admin_orders")]
    public function adminOrders($keyboard, SpwsiteRepository $spwsiteRepository, Initbacktor $initbacktor, WebsiteRepository $websiteRepository,WbordersRepository $ordersRepository, DispatchSpaceWebRepository $dispatchSpaceWebRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        if($keyboard!=$this->getParameter('key_admin')) $this->redirectToRoute('app_logout');
       // $website=$websiteRepository->find(3);
       // $dispatch=$dispatchSpaceWebRepository->find(1);
       // $pw=$spwsiteRepository->find(1);
        $initback['fact']=$initbacktor->init();
        $orders=$ordersRepository->byDateOrders();
        $vartwig=$this->menuNav->templateControl(
            Links::CUSTOMER_LIST,
            'order',
            "order",
            'all');

        return $this->render('aff_master/home.html.twig', [
            'init'=>$initback,
            'directory'=>'orders',
            'orders'=>$orders,
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);
    }

 //todo ??? c'est quoi ??

    #[Route('back-admin/customers/{keyboard}', name:"back_admin_customers")]
    public function adminCustomer($keyboard, SpwsiteRepository $spwsiteRepository, Initbacktor $initbacktor, WebsiteRepository $websiteRepository,CustomersRepository $customersRepository, DispatchSpaceWebRepository $dispatchSpaceWebRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        if($keyboard!=$this->getParameter('key_admin')) $this->redirectToRoute('app_logout');
        $website=$websiteRepository->find(3);
        $dispatch=$dispatchSpaceWebRepository->find(1);
        $pw=$spwsiteRepository->find(1);
        $initback['fact']=$initbacktor->init();
        $customers=$customersRepository->findAllCustoAndUserActive();

        $vartwig=[
            "title"=>'backoffice',
            "description"=>'backoffice',
            "keyword"=>'backoffice',
            "tagueries"=>[["name"=> "backinfo website"]],
        ];
        return $this->render('backoffice/customer/customers.html.twig', [
            'init'=>$initback,
            'website'=>$website,
            'pw'>$pw,
            'customers'=>$this->dispatch,
            'dispatch'=>$dispatch,
            'admin'=>[true],
            'vartwig'=>$vartwig
        ]);
    }



}
