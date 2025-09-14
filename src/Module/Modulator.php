<?php

namespace App\Module;

use App\Entity\Admin\Orders;
use App\Entity\Customer\Services;
use App\Entity\Module\Contactation;
use App\Entity\Module\ModuleList;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Lib\Tools;

class Modulator
{
    private EntityManagerInterface $em;
    private Tools $tolls;
    private UrlGeneratorInterface $router;

    public function __construct( EntityManagerInterface $em,Tools $tolls, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->tolls = $tolls;
        $this->router = $router;
    }

    public function newModule($website){

    }

    public function addModule($namemodule,$website): bool
    {
        $module = new ModuleList();
        $module->setClassmodule($namemodule);
        $module->setKeymodule($website->getCodesite());
        $website->addListmodule($module);
        $this->em->persist($module);
        $this->em->persist($website);
        $this->em->flush();
        return true;
    }

    public function addService(Orders $order){
        $customer=$order->getNumclient()->getIdCustomer();
        $product=$order->getListproducts()[0]->getProduct();
        $subtract=$order->getListproducts()[0]->getSubscription();
        $service = new Services();
        $service->setNamemodule($product->getName());
        if($subtract){
            $service->setDatestartAt($subtract->getStarttime()??new DateTime());
            $service->setDateendAt($subtract->getStarttime()??null);
        }else{
            $service->setDatestartAt(new DateTime());
        }
        $service->setCustomer($customer);
        $service->setProducts($product);
        $service->setActive(true);
        $customer->addService($service);
        $this->em->persist($service);
        $this->em->persist($customer);
        $this->em->flush();
    }

    public function editModule($module){

    }

    public function initModules($services, $board){
        foreach ($services as $service){
            $module= new ModuleList();
            $module->setClassmodule($service->getNamemodule());
            $module->setKeymodule($board->getCodesite());
            if($service->getNamemodule()==='module_mail'){
                $contactation=new Contactation();
                $contactation->setKeymodule($board->getCodesite());
                $board->setContactation($contactation);
                $this->em->persist($contactation);
            }
            $board->addListmodule($module);
            $this->em->persist($service);
            $this->em->persist($board);
            $this->em->flush();
        }
    }

    public function initContactor($website): bool
    {

        $contactation=$website->getContactation();
        if(!$contactation){
            $contactation=new Contactation();
            $contactation->setBoard($website);
            $contactation->setKeymodule($website->getCodesite());
            $website->setContactation($contactation);
        }
        $key=$this->tolls::genererChaineAleatoire();
        $link=$this->router->generate('contact_module_spwb',[
            'slug'=>$website->getSlug(),
            'key'=>$key],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $contactation->setLinkone($link);
        $contactation->setKeycontactation($key);
        $contactation->setActive(true);
        $contactation->setDatemajAt(new DateTime());
        $this->em->persist($website);
        $this->em->persist($contactation);
        $this->em->flush();

        return true;
    }


}