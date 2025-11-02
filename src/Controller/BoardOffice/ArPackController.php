<?php

namespace App\Controller\BoardOffice;

use App\Classe\UserSessionTrait;
use App\Entity\Games\ArPack;
use App\Form\ArPackImportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;


#[Route('/boardoffice/ar/arpack')]
class ArPackController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/new', name: 'admin_ar_pack_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $board = $this->requireBoard();
        $pack = new ArPack();
        $form = $this->createForm(ArPackImportType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mindFile = $form->get('mindFile')->getData();

            if (!$mindFile instanceof UploadedFile) {
                $form->get('mindFile')->addError(new FormError('Sélectionnez un fichier MindAR (.mind).'));
            } else {
                $slugger = new AsciiSlugger();
                $safeDir = strtolower($slugger->slug($pack->getName() ?? ''));

                if ($safeDir === '') {
                    $form->get('name')->addError(new FormError('Nom de pack invalide.'));
                } elseif ($em->getRepository(ArPack::class)->findOneBy(['name' => $pack->getName()])) {
                    $form->get('name')->addError(new FormError('Un pack avec ce nom existe déjà.'));
                } else {
                    $fs = new Filesystem();
                    $projectDir = $this->getParameter('kernel.project_dir');
                    $publicDir = $projectDir . '/public/mindar/packs';
                    $packDir = $publicDir . '/' . $safeDir;

                    if ($fs->exists($packDir)) {
                        $form->get('name')->addError(new FormError('Un dossier MindAR existe déjà pour ce nom. Choisissez un autre nom.'));
                    } else {
                        try {
                            $fs->mkdir($packDir);

                            $mindFile->move($packDir, 'targets.mind');
                            $pack->setMindPath(sprintf('/mindar/packs/%s/targets.mind', $safeDir));

                            $jsonFile = $form->get('jsonFile')->getData();
                            if ($jsonFile instanceof UploadedFile) {
                                $jsonFile->move($packDir, 'targets.json');
                                $pack->setPathJson(sprintf('/mindar/packs/%s/targets.json', $safeDir));
                            }

                            $thumbnailFile = $form->get('thumbnail')->getData();
                            if ($thumbnailFile instanceof UploadedFile) {
                                $extension = $thumbnailFile->guessExtension() ?: pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'jpg';
                                $thumbName = 'thumb.' . $extension;
                                $thumbnailFile->move($packDir, $thumbName);
                                $pack->setThumbnail(sprintf('/mindar/packs/%s/%s', $safeDir, $thumbName));
                            }

                            $em->persist($pack);
                            $em->flush();

                            $this->addFlash('success', 'Pack MindAR importé avec succès !');

                            return $this->redirectToRoute('admin_ar_pack_list');
                        } catch (\Throwable $exception) {
                            if ($fs->exists($packDir)) {
                                $fs->remove($packDir);
                            }

                            $form->addError(new FormError('Erreur lors de l\'import du pack : ' . $exception->getMessage()));
                        }
                    }
                }
            }
        }

        return $this->renderDashboard('ar', 'new', 7, [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'admin_ar_pack_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $packs = $em->getRepository(ArPack::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->renderDashboard('ar', 'list', 7, [
            'packs' => $packs
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_ar_pack_delete', methods: ['POST'])]
    public function delete(ArPack $pack, EntityManagerInterface $em): Response
    {
        $fs = new Filesystem();
        $projectDir = $this->getParameter('kernel.project_dir');
        $relativeDir = $pack->getMindPath() ? dirname($pack->getMindPath()) : null;

        if ($relativeDir) {
            $packDir = rtrim($projectDir . '/public' . $relativeDir, '/');

            if ($fs->exists($packDir)) {
                $fs->remove($packDir);
            }
        }

        $em->remove($pack);
        $em->flush();

        $this->addFlash('success', 'Pack supprimé avec succès.');
        return $this->redirectToRoute('admin_ar_pack_list');
    }
}
