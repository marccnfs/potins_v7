<?php


namespace App\Controller\Ressources;

use App\Classe\MemberSession;
use App\Entity\Ressources\Ressources;
use App\Form\DeleteType;
use App\Lib\Links;
use App\Module\Ressourcecator;
use App\Repository\CategoriesRepository;
use App\Repository\RessourcesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_MEMBER')]
#[Route('/member')]

class RessourceController extends AbstractController
{
    use MemberSession;


    #[Route('/ressource/manage/{id?}', name: 'ressource_manage', methods: ['GET', 'POST'])]
    public function manageRessource(
        Request $request,
        RessourcesRepository $ressourceRepo,
        CategoriesRepository $catRepository,
        Ressourcecator $ressourcecator,
        ?int $id = null
    ): Response {
        // Si un ID est fourni, on cherche la ressource, sinon on en crée une nouvelle
        $ressource = $id ? $ressourceRepo->find($id) : new Ressources();
        $etat=!$id;

        if (!$ressource) {
            throw $this->createNotFoundException("Ressource non trouvée.");
        }

        $categories = $catRepository->findAll();

        // Si le formulaire est soumis en POST
        if ($request->isMethod('POST')) {
            $ressource->setTitre($request->request->get('titre'));
            $ressource->setCategorie($catRepository->find($request->request->get('cat')));
            $ressource->setComposition($request->request->get('composition'));
            $ressource->setDescriptif($request->request->get('descriptif'));

            // Gestion de l'upload de l'image
            $file = $request->files->get('uploadImage');
            if ($file instanceof UploadedFile) {
                $ressource->setImageFile($file);
            }

            // Récupération et association des articles
            $articlesData = $request->request->all('articles');
            $files = $request->files->all('articles');

            foreach ($files as $index => $file) {
                if (isset($articlesData[$index])) {
                    $articlesData[$index]['media'] = $file['media'] ?? null;
                }
            }

            // Traitement via le service
            $ressourcecator->createOrUpdateRessource($ressource, $articlesData);

            return $this->redirectToRoute('module_ressources');
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'manage_ressource',
            links::ADMIN,
            5
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'ressources',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'ressource' => $ressource,
            'cats' => $categories,
            'is_edit' => $id !== null,
            'etat'=>$etat
        ]);

    }

    #[Route('/ressource/delete/{id}', name: 'delete_ressource', methods: ['POST'])]
    public function AstriddeleteRessource(int $id, RessourcesRepository $ressourceRepo, EntityManagerInterface $entityManager): RedirectResponse
    {
        $ressource = $ressourceRepo->find($id);

        if (!$ressource) {
            throw $this->createNotFoundException("La ressource demandée n'existe pas.");
        }

        $entityManager->remove($ressource);
        $entityManager->flush();

        $this->addFlash('success', 'Ressource supprimée avec succès.');

        return $this->redirectToRoute('ressource_list'); // Redirection vers la liste des ressources
    }

    #[Route('/form-delete-ressource/{id}', name:"delete_carte")]
    public function deleteRessource(Request $request, RessourcesRepository $ressourcerepo, Ressourcecator $ressourcecator, $id): RedirectResponse|Response
    {
        if(!$ressource = $ressourcerepo->find($id)) throw new Exception('carte introuvable');

        $form = $this->createForm(DeleteType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $ressourcecator->removeRessource($ressource);
            return $this->redirectToRoute('module_ressources', ['board'=>$this->board->getSlug()]);        }


        $vartwig=$this->menuNav->admin(
            $this->board,
            'delete',
            links::ADMIN,
            5
        );
        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'replacejs'=>false,
            'form' => $form->createView(),
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'carte'=>$ressource,
            'vartwig'=>$vartwig,
            'directory'=>'ressources',
            'admin'=>[true,[1,1,1]]
        ]);
    }


}
