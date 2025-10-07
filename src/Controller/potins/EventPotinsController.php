<?php


namespace App\Controller\potins;

use App\Classe\UserSessionTrait;
use App\Form\DeleteType;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Module\Evenator;
use App\Module\EvenatorPotin;
use App\Repository\DocstoreRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Service\Modules\Resator;
use App\Service\Search\ListEvent;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


#[Route('/potins/event')]
#[IsGranted("ROLE_MEMBER")]

class EventPotinsController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/add-event-potin-ajx', name:"add_event_potin_ajx")]
    public function addEvenPotintAjx(Request $request, EvenatorPotin $evenator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);

            $issue=$evenator->newEventPotin($data,$this->currentMember(), $this->currentBoard);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('/new-potin-event/{id}', name:"event_potins")]
    public function newPotinEvent(PostRepository $postRepository,$id=null): RedirectResponse|Response
    {
        $potin=$postRepository->findOnePostById($id);
        if($potin->getKeymodule()!=$this->board->getCodesite())$this->redirectToRoute('list_board');

        $vartwig=$this->menuNav->admin(
            $this->board,
            'newpotinevent',
            links::ADMIN,
            3
        );


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'potin'=>$potin,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'event'=>null,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }


    #[Route('/edit-event-potin/{id}', name:"edit_event_potin")]
    public function editEvent(NormalizerInterface $normalizer,PostEventRepository $postEventRepository, $id): RedirectResponse|Response
    {
        $event = $postEventRepository->findEventById($id);
        $json = $normalizer->normalize($event,null,['groups' => 'edit_event']);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'editpotinevent',
            links::ADMIN,
            3
        );


        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'event'=>$json,
            'potin'=>$event->getPotin(),
            'vartwig'=>$vartwig,
            'admin'=>[true,[1,1,1]],
            'back'=> $this->generateUrl('module_event',['board' => $this->board->getSlug()]),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/details-event/{id}', name:"details_event")]
    public function detailsEvent(ListEvent $listEvent,Resator $resator,PostEventRepository $postEventRepository, $id): RedirectResponse|Response
    {
        $event = $postEventRepository->findEventById($id);
        $dateevent=$resator->BuildTDateOfOneEvent($event);
        $tabOrderParticpants=$listEvent->listParticipantPotin($id); // rechercher le customer et les participants

        $vartwig=$this->menuNav->admin(
            $this->board,
            'details',
            links::ADMIN,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'event'=>$event,
            'date'=>$dateevent,
            'orders'=>$tabOrderParticpants,
            'vartwig'=>$vartwig,
        ]);
    }


    #[Route('/form-delete-event-potin/{id}', name:"form-delete_event_potin")]
    public function deleteEvent(Request $request,PostEventRepository $postEventRepository, EvenatorPotin $evenator, $id): RedirectResponse|Response
    {
        if(!$event=$postEventRepository->findEventById($id)) throw new Exception('event introuvable');

        $form = $this->createForm(DeleteType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $evenator->removeEvent($event);
        return $this->redirectToRoute('module_event', ['board' => $this->board->getSlug()]);
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'delete',
            links::ADMIN,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
        'directory'=>'event',
        'replacejs'=>false,
        'form' => $form->createView(),
        'board'=>$this->board,
        'member'=>$this->currentMember,
        'customer'=>$this->currentCustomer,
        'vartwig' => $vartwig,
        'author'=>true,
         'admin'=>[true,[1,1,1]],
        'locatecity'=>0
        ]);
    }


    #[Route('/form-publied-event-potin/{id}', name:"form-publied_event_potin")]
    public function publiedEvent(PostEventRepository $postEventRepository,Evenator $evenator, $id): RedirectResponse|Response
    {
        $event = $postEventRepository->find($id);
        $this->initBoardByKey($event->getKeymodule());
        $evenator->publiedOneEvent($event);
        return $this->redirectToRoute('module_event', ['nameboard' => $this->board->getSlug()]);
    }

    #[Route('/add-doc/{orderprodid}', name:"add_doc")]
    public function addDoc(OrderProductsRepository $orderProductsRepository,$orderprodid): RedirectResponse|Response
    {
        $orderprod=$orderProductsRepository->find($orderprodid);


        $vartwig=$this->menuNav->admin(
            $this->board,
            'add_doc',
            links::ADMIN,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'orderprod'=>$orderprod,
            'vartwig'=>$vartwig,
            'anotherhead'=>"_dropzone"
        ]);
    }

    #[Route('/download-doc/{id}/{idevent}', name:"download_doc")]
    public function downLoadDoc($id, $idevent, DocstoreRepository $docstoreRepository)
    {
        $fichier=$docstoreRepository->find($id);
        if ($fichier == null){

            return $this->redirectToRoute('details_event', ['id'=>$idevent]); }
        else{
            return $this->file($this->getParameter('upload_directory').'/'.$fichier->getName(), $fichier->getNomOriginal());
        }
    }

    #[Route('/delete-doc/{id}/{idevent}', name:"delete_doc")]
    public function deleteDoc($id, $idevent, DocstoreRepository $docstoreRepository): RedirectResponse
    {
        $fichier=$docstoreRepository->find($id);
        if ($fichier == null){
            $this->addFlash('notice', 'fichier introuvable');
        }
        else{
            $orderproduct=$fichier->getProduct();
            $orderproduct->removeDoc($fichier);
            $this->em->persist($orderproduct);
            $this->em->flush();
        }
        return $this->redirectToRoute('details_event', ['id'=>$idevent]);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/add-participant', name:"add_participant")]
    public function addParticpant(PostEventRepository $eventRepository,$id): RedirectResponse|Response
    {
        $event= $eventRepository->findEventByOneId($id);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'add_part',
            links::ADMIN,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board'=>$this->board,
            'member'=>$this->currentMember,
            'customer'=>$this->currentCustomer,
            'event'=>$event,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }



}
