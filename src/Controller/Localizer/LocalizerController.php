<?php


namespace App\Controller\Localizer;

use App\Classe\MemberSession;
use App\Entity\Sector\Adresses;
use App\Repository\ActivMemberRepository;
use App\Repository\BoardRepository;
use App\Repository\SectorsRepository;
use App\Service\Localisation\LocalisationServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[IsGranted('ROLE_MEMBER')]
#[Route('/geolocate/op/')]

class LocalizerController extends AbstractController
{
    use MemberSession;

    #[Route('localize/{id}', name:"spaceweblocalize_init")]
    public function localizeWebsite(SectorsRepository $reposector, $id): Response
    {
        $sector=$reposector->findWithAdressByCodesite($this->board->getCodesite());
        $vartwig=$this->menuNav->templatingadmin(
            'localizer',  //main_spaceweb/website/adressWp',
            'parametres du panneau',
            $this->board,3);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
        'directory'=>'parameters',
        'replacejs'=>false,
        'vartwig' => $vartwig,
        'board'=>$this->board,
        'member'=>$this->member,
        'admin'=>[true,[1,1,1]],
        'sector'=>$sector
        ]);
    }

    #[Route('newadress', name:"newadress")]
    public function newAdressWebsite(Request $request, LocalisationServices $localisation, BoardRepository $websiteRepository): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $website=$websiteRepository->find($data['id']);
            if(!$website) return new JsonResponse(['success'=>false,'error'=>'merdum ici : id =>'.$data['id']]);
            $adress=$localisation->newAdress($data,  $website, 1);
            if($adress!=null){
                $website->setStatut(true);
                $this->em->persist($website);
                $this->em->flush();
                $responseCode = 200;
                http_response_code($responseCode);
                header('Content-Type: application/json');
                return new JsonResponse(['success'=>true, "label"=>$data['properties']['label']]);
            }
            return new JsonResponse(['success'=>false,"error"=>"adresse pas enregistrée"]);
        }
        return new JsonResponse(['success'=>false,"error"=>"requete erreur"]);
    }

    #[Route('deleteadress/{id}', name:"deleteadress", methods: 'DELETE')]
    public function deleteAdressWebsite(Request $request, BoardRepository $websiteRepository, $id): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $idwebsite=$request->request->get('website');
            $website=$websiteRepository->find($idwebsite);
            if(!$website)  return new JsonResponse(['success'=>false,"error"=>"id spaceweb non reconnu"]);
            $adresses=$website->getTemplate()->getSector()->getAdresse();
            /** @var Adresses $adress */
            foreach ($adresses as $adress) {
                if ($adress->getId() == $id) {
                    $this->em->remove($adress);
                    $this->em->flush();
                    $responseCode = 200;
                    http_response_code($responseCode);
                    header('Content-Type: application/json');
                    return new JsonResponse(['success'=>true]);
                }
            }
        }
        return new JsonResponse(['success'=>false,"error"=>"requete ajax non reconnue"]);
    }



    //appel ajax pour change locality via bullercity page cargo_public


    #[Route('newlocate/{city}/{code}', name:"new_locate")]
    public function newLocate(Request $request, LocalisationServices $localisation, SerializerInterface $serializer, $code=null, $city=null): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $space=null;
            $gps = $localisation->changeLocate($space, $code,$city);
            if($gps){
                $jasonlocate = $serializer->serialize($gps, 'json');
                $responseCode = 200;
                http_response_code($responseCode);
                header('Content-Type: application/json');
                return new JsonResponse(['success'=>true, "locate"=>$jasonlocate]);
            }
            return new JsonResponse(['success'=>false]);
        }
        return new JsonResponse(['success'=>false]);
    }

    //appel ajax pour init/change locality de dispatch


    #[Route('newlocatedispatch', name:"new_locate-dispatch")]
    public function newLocateDispatch(ActivMemberRepository $dispatchRepository,Request $request, LocalisationServices $localisation, SerializerInterface $serializer): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $city=$request->query->get("city");
            $code=$request->query->get("code");
            $id=$request->query->get("id");
            if($id!="null"){
                $dispatch=$dispatchRepository->find($id);
            }else{
                $dispatch=null;
            }
            $gps = $localisation->changeLocate($dispatch, $code,$city);
            if($gps){

                $jasonlocate = $serializer->serialize($gps, 'json');
                $responseCode = 200;
                http_response_code($responseCode);
                header('Content-Type: application/json');

                return new JsonResponse(['success'=>true, "locate"=>$jasonlocate]);
            }
            return new JsonResponse(['success'=>false]);
        }
        return new JsonResponse(['success'=>false]);
    }
}