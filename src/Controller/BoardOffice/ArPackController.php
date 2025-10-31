<?php

namespace App\Controller\BoardOffice;

use App\Classe\UserSessionTrait;
use App\Entity\Games\ArPack;
use App\Form\ArPackImportType;
use App\Service\MindArTargetBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/boardoffice/ar/arpack')]
class ArPackController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/new', name: 'admin_ar_pack_new')]
    public function new(Request $request, EntityManagerInterface $em, MindArTargetBuilder $builder): Response
    {
        $board = $this->requireBoard();
        $pack = new ArPack();
        $form = $this->createForm(ArPackImportType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fs = new Filesystem();
            $publicDir = $this->getParameter('kernel.project_dir') . '/public/mindar/packs';
            $packDir = $publicDir . '/' . $pack->getName();
            $fs->mkdir($packDir);

            // Upload du fichier .mind
            $mindFile = $form->get('mindFile')->getData();
            $mindName = 'targets.mind';
            $mindFile->move($packDir, $mindName);
            $pack->setMindPath("/mindar/packs/{$pack->getName()}/{$mindName}");

            // Upload optionnel JSON
            if ($jsonFile = $form->get('jsonFile')->getData()) {
                $jsonName = 'targets.json';
                $jsonFile->move($packDir, $jsonName);
                $pack->setPathJson("/mindar/packs/{$pack->getName()}/{$jsonName}");
            }

            // Upload miniature
            if ($thumb = $form->get('thumbnail')->getData()) {
                $thumbName = 'thumb.' . $thumb->guessExtension();
                $thumb->move($packDir, $thumbName);
                $pack->setThumbnail("/mindar/packs/{$pack->getName()}/{$thumbName}");
            }

            $em->persist($pack);
            $em->flush();

            $this->addFlash('success', 'Pack AR importé avec succès !');
            return $this->redirectToRoute('admin_ar_pack_list');
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
        $packDir = $projectDir . '/public/mindar/packs/' . $pack->getName();

        if ($fs->exists($packDir)) {
            $fs->remove($packDir);
        }

        $em->remove($pack);
        $em->flush();

        $this->addFlash('success', 'Pack supprimé avec succès.');
        return $this->redirectToRoute('admin_ar_pack_list');
    }
}
