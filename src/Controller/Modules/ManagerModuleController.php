<?php


namespace App\Controller\Modules;

use App\Classe\MemberSession;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Repository\BoardRepository;
use App\Repository\ServicesRepository;
use App\Module\Modulator;
use App\Util\DefaultModules;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/module/website/')]
#[IsGranted("ROLE_MEMBER")]

class ManagerModuleController extends AbstractController
{
    use MemberSession;


    #[Route('add-module-for-website/{activ}/{id}', name:"addmodule_website")]
    public function addModulestape($activ,$id, ServicesRepository $servicesRepository): Response
    {

        $service=$servicesRepository->findOneBy([
            'customer'=>$this->member->getCustomer()->getId(),
            'namemodule'=>$activ
        ]);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'initModules',
            links::ADMIN,
            2
        );
            return $this->render('ptn_parameters/home.html.twig', [
                'directory'=>'parameters',
                'replacejs'=>false,
                'vartwig' => $vartwig,
                'board'=>$this->board,
                'status'=> true,
                'module'=>DefaultModules::TAB_MODULES_INFO[$activ],
                'admin'=>[true,[1,1,1]],
                'locatecity'=>0,
                'city'=>null
            ]);
    }


    /**
     * init un module particulier //todo redirection vers la page board et test pour ne pas initialiser plisuer fois le meme module
     */
    #[Route('add-module-ajx', name:"add_module_ajx")]
    public function AddNewsModule(Request $request, BoardRepository $websiteRepository, Modulator $modulator): JsonResponse
    {

        if($request->isXmlHttpRequest())
        {
            $slug=$request->request->get('slug');
            $module=$request->request->get('module');
            if(!$website= $websiteRepository->findWbQ3($slug,$this->member->getId())) return new JsonResponse(MsgAjax::MSG_NOWB);

            $issue=$modulator->addModule($module,$website);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

}
