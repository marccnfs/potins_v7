<?php

namespace App\Controller\Mediatheques;

use App\Classe\initMember;
use App\Entity\Member\Boardslist;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Module\Modulator;
use App\Repository\UserRepository;
use App\Service\Member\MemberFactor;
use App\Service\SpaceWeb\BoardlistFactor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[IsGranted('ROLE_MEMBER')]
#[Route('/mediatheque/registration/')]


class InitMediathequeController extends AbstractController
{
    use initMember;


    #[Route('add-new-mediatheque-ajx', name:"add_new_mediatheque_ajx")]
    public function addNewMediaBoardAjx(Request $request,MemberFactor $memberFactor,Modulator $modulator, BoardlistFactor $boardlistor): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $mediathequeCustomer=$this->repocustomer->find($data['customer']);
            $boardlist=new Boardslist();
            $member=$memberFactor->NewMember($mediathequeCustomer);
            $board=$boardlistor->createMediaBoard($mediathequeCustomer,$member,$boardlist,$data);
            $modulator->initModules($mediathequeCustomer->getServices(), $board);  // creation des modules de base avec le contactation
            return new JsonResponse(true);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('initialisation-espace-media/{id}', name:"intit_espace_media")]
    public function newEspaceMedia(UserRepository $userRepository,$id): RedirectResponse|Response
    {
        $mediathequeCustomer=$userRepository->findAllCustomByUserId($id)->getCustomer();
        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'initmediaboard',
            0,
            "nocity");

        return $this->render($this->useragentP.'ptn_account/home.html.twig', [
            'directory'=>"registration",
            'replacejs'=>$replacejs??null,
            'vartwig'=>$vartwig,
            'customer'=>$this->customer,
            'mediatheque'=>$mediathequeCustomer
        ]);
    }
}
