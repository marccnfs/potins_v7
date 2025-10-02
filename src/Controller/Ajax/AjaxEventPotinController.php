<?php


namespace App\Controller\Ajax;

use App\Classe\UserSessionTrait;
use App\Entity\Media\Docstore;
use App\Module\EvenatorDoc;
use App\Repository\OrderProductsRepository;
use App\Repository\PostEventRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/member/wb/event')]

class AjaxEventPotinController extends AbstractController
{
    use UserSessionTrait;


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

}
