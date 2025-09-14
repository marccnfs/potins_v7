<?php


namespace App\Controller\potins;

use App\Classe\MemberSession;
use App\Form\DeleteType;
use App\Lib\MsgAjax;
use App\Module\Evenator;
use App\Module\EvenatorPotin;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


#[Route('/member/wb/event')]
#[IsGranted("ROLE_MEMBER")]

class EventPotinsController extends AbstractController
{
    use MemberSession;

    #[Route('/add-event-potin-ajx', name:"add_event_potin_ajx")]
    public function addEvenPotintAjx(Request $request, EvenatorPotin $evenator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
            $issue=$evenator->newEventPotin($data,$this->member, $this->board);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('/new-potin-event/', name:"new_generic_potin_event")]
    #[Route('/new-potin-event/{id}', name:"event_potins")]
    public function newPotinEvent(PostRepository $postRepository,$id=null): RedirectResponse|Response
    {
        $potin=$postRepository->findOnePostById($id);
        if($potin->getKeymodule()!=$this->board->getCodesite())$this->redirectToRoute('list_board');
        $vartwig=$this->menuNav->templatingadmin(
            'newpotinevent',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'potin'=>$potin,
            'member'=>$this->member,
            'customer'=>$this->customer,
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
        $vartwig=$this->menuNav->templatingadmin(
            'editpotinevent',
            "edition event",
            $this->board,3);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'event'=>$json,
            'potin'=>$event->getPotin(),
            'vartwig'=>$vartwig,
            'admin'=>[true,[1,1,1]],
            'locatecity'=>0,
            'back'=> $this->generateUrl('module_event',['board' => $this->board->getSlug()]),
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
        $vartwig = $this->menuNav->templatingadmin(
        'delete',
        'delete event',
            $this->board,3);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
        'directory'=>'event',
        'replacejs'=>false,
        'form' => $form->createView(),
        'board' => $this->board,
        'member'=>$this->member,
        'customer'=>$this->customer,
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

}