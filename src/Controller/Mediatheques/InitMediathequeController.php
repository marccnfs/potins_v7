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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use JsonException;


#[IsGranted('ROLE_MEMBER')]
#[Route('/mediatheque/registration/')]


class InitMediathequeController extends AbstractController
{
    use initMember;


    #[Route('add-new-mediatheque-ajx', name:"add_new_mediatheque_ajx", methods: ['POST'])]
    public function addNewMediaBoardAjx(Request $request,MemberFactor $memberFactor,Modulator $modulator, BoardlistFactor $boardlistor): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            try {
                $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $this->createErrorResponse('invalid_json', 'Le contenu JSON fourni est invalide.');
            }

            if (!is_array($data)) {
                return $this->createErrorResponse('invalid_payload', 'Le corps de la requête doit être un objet JSON.');
            }

            $csrfToken = $request->headers->get('X-CSRF-TOKEN', $data['_token'] ?? null);
            if (!$csrfToken || !$this->isCsrfTokenValid('add_new_mediatheque', $csrfToken)) {
                return $this->createErrorResponse('invalid_csrf_token', 'Jeton CSRF manquant ou invalide.', Response::HTTP_FORBIDDEN);
            }

            if (!array_key_exists('customer', $data) || !is_scalar($data['customer']) || '' === (string) $data['customer']) {
                return $this->createErrorResponse('missing_customer', 'Le client spécifié est manquant ou invalide.');
            }

            $mediathequeCustomer=$this->repocustomer->find($data['customer']);
            if(!$mediathequeCustomer){
                return $this->createErrorResponse('customer_not_found', 'Le client demandé est introuvable.', Response::HTTP_NOT_FOUND);
            }

            $boardlist=new Boardslist();
            $member=$memberFactor->NewMember($mediathequeCustomer);
            $board=$boardlistor->createMediaBoard($mediathequeCustomer,$member,$boardlist,$data);
            $modulator->initModules($mediathequeCustomer->getServices(), $board);  // creation des modules de base avec le contactation
            return new JsonResponse(['success'=>true]);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ, Response::HTTP_BAD_REQUEST);
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

    private function createErrorResponse(string $code, string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
