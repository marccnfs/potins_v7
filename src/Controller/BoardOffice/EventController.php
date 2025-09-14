<?php


namespace App\Controller\BoardOffice;

use App\Classe\MemberSession;
use App\Entity\Media\Docstore;
use App\Repository\DocstoreRepository;
use App\Entity\Module\PostEvent;
use App\Form\DeleteType;
use App\Lib\MsgAjax;
use App\Module\Evenator;
use App\Module\EvenatorDoc;
use App\Repository\ModuleListRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\PostEventRepository;
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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/member/wb/event')]
#[IsGranted("ROLE_MEMBER")]

class EventController extends AbstractController
{
    use MemberSession;


    #[Route('/add-event-ajx', name:"add_event_ajx")]
    public function addEventAjx(Request $request, Evenator $evenator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode((string) $request->getContent(), true);
         //   if(!$this->getUserspwsiteOfWebsite($data['board']) || !$this->admin ) return new JsonResponse(MsgAjax::MSG_ERRORRQ);
            $issue=$evenator->newEvent($data,$this->member, $this->board);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/add-doc-ajx', name:"add_doc_ajx")]
    public function addDocAjx(Request $request, EvenatorDoc $evenatordoc, PostEventRepository $eventRepository): JsonResponse
    {

            $data['fichier']=$request->request->get('titre');
            $data['type']=$request->request->get('type');
            $data['event']=$request->request->get('event');
            $data['pict']=$request->request->get('file64');
            $data['docfile']=$request->files->get('docfile');
          //  dump($data['docfile']);
            $event= $eventRepository->findEventByOneId($data['event']);
            $issue=$evenatordoc->addDoc($data,$event);
            return new JsonResponse($issue);
    }

    #[Route('/new-event', name:"new_event")]
    public function newEvent($board=null): RedirectResponse|Response
    {
        $vartwig=$this->menuNav->templatingadmin(
            'new',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'event'=>null,
            'vartwig'=>$vartwig,
        ]);
    }

    #[Route('/add-doc/{orderprodid}', name:"add_doc")]
    public function addDoc(OrderProductsRepository $orderProductsRepository,$orderprodid): RedirectResponse|Response
    {
        $orderprod=$orderProductsRepository->find($orderprodid);

        $vartwig=$this->menuNav->templatingadmin(
            'add_doc',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
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
    #[Route('/upload-doc/', name:"upload_doc")]
    public function uploaddDoc(Request $request, SluggerInterface $slugger, OrderProductsRepository $orderProductsRepository): JsonResponse|Response
    {
        $file = $request->files->get('file');
        $orderproduct=$orderProductsRepository->findEventByOrderProdid($request->request->get('orderprod'));
        $event=$orderproduct->getSubscription()->getEvent();

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
            try {
                $doc=new Docstore();
                $doc->setNomOriginal($file->getClientOriginalName());
                $doc->setName($newFilename);
                $doc->setDateEnvoi(new \DateTime());
                $doc->setTaille($file->getSize());
                $doc->setExtension($file->guessExtension());
                $orderproduct->addDoc($doc);
                $file->move(
                        $this->getParameter('upload_directory'), // Ce paramètre doit être défini dans config/services.yaml
                        $newFilename
                    );
                $this->em->persist($orderproduct);
                $this->em->flush();
            }
           catch (FileException $e) {
                return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors du téléversement'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $redirectUrl = $this->generateUrl('details_event', ['id'=>$event->getId()]); // Redirige vers une page de succès
            return new JsonResponse(['status' => 'success', 'redirect_url' => $redirectUrl], Response::HTTP_OK);

            //return $this->redirectToRoute('details_event', ['id'=>$event->getId()]);

           // return new JsonResponse(['status' => 'success', 'filename' => $newFilename], Response::HTTP_OK);
        }
        return new JsonResponse(['status' => 'error', 'message' => 'Aucun fichier trouvé'], Response::HTTP_BAD_REQUEST);

    }


/*
        $vartwig=$this->menuNav->templatingadmin(
            'add_doc',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'orderprod'=>$orderprod,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }
*/

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/add-participant', name:"add_participant")]
    public function addParticpant(PostEventRepository $eventRepository,$id): RedirectResponse|Response
    {
        $event= $eventRepository->findEventByOneId($id);
        $vartwig=$this->menuNav->templatingadmin(
            'add_part',
            $this->board->getNameboard(),
            $this->board,
            3
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'event'=>$event,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }


    #[Route('/edit-event/{id}', name:"edit_event")]
    public function editEvent(NormalizerInterface $normalizer,PostEventRepository $postEventRepository,   ModuleListRepository $moduleListRepository, $id): RedirectResponse|Response
    {
        $event = $postEventRepository->findEventById($id);
        $tab=[];
        if(!$this->getUserspwsiteOfWebsiteByKey($event->getKeymodule()) || !$this->admin )$this->redirectToRoute('cargo_public');

        $json = $normalizer->normalize($event,null,['groups' => 'edit_event']);
        $partners=$event->getPartners();

        foreach ($partners as $partner){
            $tab[]=['id'=>$partner->getId(), 'title'=>$partner->getNamewebsite(), 'pict'=>'/spaceweb/template/'.$partner->getTemplate()->getLogo()->getNamefile()];
        }

        $vartwig=$this->menuNav->templatingadmin(
            'edit',
            "edition event",
            $this->board,3);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'event'=>$json,
            'partners'=>$tab,
            'vartwig'=>$vartwig,
            'admin'=>[true,$this->member->getPermission()],
            'city'=>$this->board->getLocality()[0]->getCity(),
            'locatecity'=>0,
            'back'=> $this->generateUrl('module_event',['city'=>$this->board->getLocality()[0]->getCity(),'nameboard' => $this->board->getSlug()]),
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

        //dump($tabOrderParticpants);
       // $docs=$event->getDocs();

        $vartwig=$this->menuNav->templatingadmin(
            'details',
            "details event",
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'event',
            'replacejs'=>false,
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'event'=>$event,
            'date'=>$dateevent,
            'orders'=>$tabOrderParticpants,
            'vartwig'=>$vartwig,
        ]);
    }


    #[Route('/form-delete-event/{id}', name:"form-delete_event")]
    public function deleteEvent(Request $request,PostEventRepository $postEventRepository, Evenator $evenator, $id): RedirectResponse|Response
    {
        if(!$event=$postEventRepository->findEventById($id)) throw new Exception('event introuvable');
        if(!$this->getUserspwsiteOfWebsiteByKey($event->getKeymodule()) || !$this->admin )$this->redirectToRoute('cargo_public');

        $form = $this->createForm(DeleteType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $evenator->removeEvent($event);
        return $this->redirectToRoute('module_event', ['city'=>$this->board->getLocality()[0]->getCity(),'nameboard' => $this->board->getSlug()]);
        }
        $vartwig = $this->menuNav->templatingadmin(
        'delete',
        'delete event',
            $this->board,3);

        return $this->render($this->useragentP.'ptn_member/home.html.twig', [
        'directory'=>'event',
        'replacejs'=>false,
        'form' => $form->createView(),
        'board' => $this->board,
        'member'=>$this->member,
        'customer'=>$this->customer,
        'vartwig' => $vartwig,
        'author'=>true,
            'admin'=>[true,$this->member->getPermission()],
        'city'=>$this->board->getLocality()[0]->getCity(),
        'locatecity'=>0
        ]);
    }


    #[Route('/form-publied-event/{id}', name:"form-publied_event")]
    public function publiedEvent(PostEventRepository $postEventRepository,Evenator $evenator, $id): RedirectResponse|Response
    {
        $event = $postEventRepository->find($id);
        $this->initBoardByKey($event->getKeymodule());
        $evenator->publiedOneEvent($event);
        return $this->redirectToRoute('module_event', ['city'=>$this->board->getLocality()[0]->getCity(),'nameboard' => $this->board->getSlug()]);
    }

}