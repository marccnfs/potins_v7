<?php


namespace App\Controller\Modules;


use App\Classe\UserSessionTraitOld;
use App\Lib\Links;
use App\Repository\ContactationRepository;
use App\Module\Modulator;
use App\Util\DefaultModules;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[IsGranted("ROLE_CUSTOMER")]
#[Route('/module/contact')]

class ContactController extends AbstractController
{
    use UserSessionTraitOld;

    #[Route('/process-module-contact/{id}', name:"process_contact")]
    public function processContact(DefaultModules $defaultModules,$id): RedirectResponse|Response
    {
        if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');

        $moduletab=$defaultModules->selectModule($this->board);

        $test1= (bool)($email = $this->board->getTemplate()->getEmailspaceweb());
        $test2= (bool)($sector = $this->board->getTemplate()->getSector());

        $vartwig=$this->menuNav->templatingadmin(
            'comonModule',
            'module de contact',
            $this->board,2);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'ospaceblog',
            links::ADMIN,
            1
        );

        return $this->render('aff_websiteadmin/home.html.twig', [
            'directory'=>'parameters',
            'vartwig' => $vartwig,
            'replacejs'=>false,
            'website'=>$this->board,
            'board'=>$this->board,
            'tabmodule'=>$moduletab,
            'test1'=>$test1,
            'test2'=>$test2,
            'email'=>$email,
            'sector'=>$sector,
            'admin'=>[$this->admin,$this->permission],
            'city'=>$this->board->getLocality()->getCity()
        ]);
    }


    #[Route('/creation-module-contact/{id}', name:"new_contact_mod")]
    public function newModContact( Request $request, Modulator $modulator, $id): RedirectResponse|Response
    {
        if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin )$this->redirectToRoute('cargo_public');

        $modulator->initContactor($this->board);

       // $this->addFlash('infoprovider', 'nouveau module de contact ok.');
        return $this->redirectToRoute('spaceweb_mod',['id'=>$this->board->getId()]);

    }


    #[Route('active-contactation-for-website/{id}', name:"init_contactation")]
    public function initContactation($id, Modulator $modulator): Response
    {
        if(!$this->getUserspwsiteOfWebsite($id) || !$this->admin ) return $this->redirectToRoute('spaceweb_mod', ['id'=>$id]);

        $modulator->initContactor($this->board);

        return $this->redirectToRoute('spaceweb_mod', ['id'=>$id]);
    }

/*--------------------------------------------- a controler ------------------------------------*/


    #[Route('/contacts', name:"contacts_customer")]
    public function contactsDispatchSpace(ContactationRepository $contactationRepository): Response //todo
    {
        if(!$this->iddispatch) return $this->redirectToRoute('cargo_public');
        $contactation=$contactationRepository->findContactationByModuleAndIdProvider($this->iddispatch);
        if(!$contactation)return $this->redirectToRoute('index_customer'); //todo pas forcement faire une info pas de message par exemple
        $module=$contactation->getModuleType();

        $dispatchspace=$module->getWebsite();
        $bullesmsg=$contactation->getMessages();
        $vartwig=$this->menuNav->templatingspaceWeb(
            'main_spaceweb/message/messages',
            'Privatemsg',
            $this->website
        );
        $tabtags=[
            ['id'=>1,'name'=>"all",'active'=>true, 'link'=>'contacts_customer'],
        ];
        return $this->render('layout/layout_mainspaceweb.html.twig', [
            'vartwig'=>$vartwig,
            'msgs'=>$bullesmsg,
            'spaceweb'=>$dispatchspace,
            'tags'=>$tabtags,
            'admin'=>$this->admin
        ]);
    }


}
