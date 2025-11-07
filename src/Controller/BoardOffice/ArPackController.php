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
        $form = $this->createForm(ArPackImportType::class, $pack, [
            'mind_file_required' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mindFile = $form->get('mindFile')->getData();

            if (!$mindFile instanceof UploadedFile) {
                $form->get('mindFile')->addError(new FormError('Sélectionnez un fichier MindAR (.mind).'));
            } else {
                $slugger = new AsciiSlugger();
                $safeDir = strtolower((string) $slugger->slug($pack->getName() ?? ''));

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

                            $modelFiles = $form->get('modelFiles')->getData();
                            if (is_array($modelFiles) && !empty($modelFiles)) {
                                $storedModels = $this->storeModelFiles($modelFiles, $packDir, $safeDir, $fs, $slugger);
                                $pack->setModels($storedModels !== [] ? $storedModels : null);
                            }

                            $targetImages = $form->get('targetImages')->getData();
                            if (is_array($targetImages) && !empty($targetImages)) {
                                $storedTargets = $this->storeTargetImages($targetImages, $packDir, $safeDir, $fs, $slugger);
                                $pack->setTargetImages($storedTargets !== [] ? $storedTargets : null);
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
            'pack' => $pack,
            'existingModels' => $pack->getModels(),
            'existingTargets' => $pack->getTargetImages(),
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

    #[Route('/edit/{id}', name: 'admin_ar_pack_edit')]
    public function edit(Request $request, ArPack $pack, EntityManagerInterface $em): Response
    {
        $board = $this->requireBoard();

        $slugger = new AsciiSlugger();
        $currentMindPath = $pack->getMindPath();
        $currentSafeDir = $this->extractSafeDir($currentMindPath, $pack->getName(), $slugger);

        $form = $this->createForm(ArPackImportType::class, $pack, [
            'mind_file_required' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fs = new Filesystem();
            $projectDir = $this->getParameter('kernel.project_dir');
            $publicDir = $projectDir . '/public/mindar/packs';
            $packDir = $publicDir . '/' . $currentSafeDir;

            if (!$fs->exists($packDir)) {
                $fs->mkdir($packDir);
            }

            $safeDir = strtolower((string) $slugger->slug($pack->getName() ?? ''));
            if ($safeDir === '') {
                $form->get('name')->addError(new FormError('Nom de pack invalide.'));
            } else {
                if ($safeDir !== $currentSafeDir) {
                    $newPackDir = $publicDir . '/' . $safeDir;
                    if ($fs->exists($newPackDir)) {
                        $form->get('name')->addError(new FormError('Un pack avec ce nom existe déjà.'));
                        return $this->renderDashboard('ar', 'edit', 7, [
                            'form' => $form->createView(),
                            'pack' => $pack,
                            'existingModels' => $pack->getModels(),
                            'existingTargets' => $pack->getTargetImages(),
                        ]);
                    }

                    $fs->rename($packDir, $newPackDir);
                    $this->updatePackPaths($pack, $currentSafeDir, $safeDir);
                    $packDir = $newPackDir;
                    $currentSafeDir = $safeDir;
                }

                $mindFile = $form->get('mindFile')->getData();
                if ($mindFile instanceof UploadedFile) {
                    $mindFile->move($packDir, 'targets.mind');
                    $pack->setMindPath(sprintf('/mindar/packs/%s/targets.mind', $currentSafeDir));
                }

                $jsonFile = $form->get('jsonFile')->getData();
                if ($jsonFile instanceof UploadedFile) {
                    $jsonFile->move($packDir, 'targets.json');
                    $pack->setPathJson(sprintf('/mindar/packs/%s/targets.json', $currentSafeDir));
                }

                $thumbnailFile = $form->get('thumbnail')->getData();
                if ($thumbnailFile instanceof UploadedFile) {
                    $extension = $thumbnailFile->guessExtension() ?: pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'jpg';
                    $thumbName = 'thumb.' . $extension;
                    $thumbnailFile->move($packDir, $thumbName);
                    $pack->setThumbnail(sprintf('/mindar/packs/%s/%s', $currentSafeDir, $thumbName));
                }

                $modelFiles = $form->get('modelFiles')->getData();
                if (is_array($modelFiles) && !empty($modelFiles)) {
                    $modelsDir = $packDir . '/models';
                    if ($fs->exists($modelsDir)) {
                        $fs->remove($modelsDir);
                    }
                    $storedModels = $this->storeModelFiles($modelFiles, $packDir, $currentSafeDir, $fs, $slugger);
                    $pack->setModels($storedModels !== [] ? $storedModels : null);
                }

                $targetImages = $form->get('targetImages')->getData();
                if (is_array($targetImages) && !empty($targetImages)) {
                    $targetsDir = $packDir . '/targets';
                    if ($fs->exists($targetsDir)) {
                        $fs->remove($targetsDir);
                    }
                    $storedTargets = $this->storeTargetImages($targetImages, $packDir, $currentSafeDir, $fs, $slugger);
                    $pack->setTargetImages($storedTargets !== [] ? $storedTargets : null);
                }

                $em->flush();

                $this->addFlash('success', 'Pack mis à jour avec succès.');

                return $this->redirectToRoute('admin_ar_pack_list');
            }
        }

        return $this->renderDashboard('ar', 'edit', 7, [
            'form' => $form->createView(),
            'pack' => $pack,
            'existingModels' => $pack->getModels(),
            'existingTargets' => $pack->getTargetImages(),
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_ar_pack_delete', methods: ['POST'])]
    public function delete(Request $request, ArPack $pack, EntityManagerInterface $em): Response
    {
        $this->validateCsrf('delete' . $pack->getId(), $request->request->get('_token'));

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

    private function extractSafeDir(?string $mindPath, ?string $name, AsciiSlugger $slugger): string
    {
        if ($mindPath) {
            $relative = trim(dirname($mindPath), '/');
            if ($relative !== '') {
                $parts = explode('/', $relative);
                return (string) end($parts);
            }
        }

        return strtolower((string) $slugger->slug($name ?? ''));
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, array<string, mixed>>
     */
    private function storeModelFiles(array $files, string $packDir, string $safeDir, Filesystem $fs, AsciiSlugger $slugger): array
    {
        $targetDir = $packDir . '/models';
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $stored = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $extension = $file->guessExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'bin';
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = strtolower((string) $slugger->slug($baseName ?: 'asset'));
            $unique = str_replace('.', '', uniqid('', true));
            $finalName = sprintf('%s-%s.%s', $safeName, $unique, $extension);

            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType() ?: '';

            $file->move($targetDir, $finalName);

            $type = $this->guessAssetType($mimeType, $extension);

            $stored[] = [
                'filename' => $originalName,
                'path' => sprintf('/mindar/packs/%s/models/%s', $safeDir, $finalName),
                'type' => $type,
                'mime' => $mimeType,
            ];
        }

        return $stored;
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, array<string, mixed>>
     */
    private function storeTargetImages(array $files, string $packDir, string $safeDir, Filesystem $fs, AsciiSlugger $slugger): array
    {
        $targetDir = $packDir . '/targets';
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $stored = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $extension = $file->guessExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'jpg';
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = strtolower((string) $slugger->slug($baseName ?: 'target'));
            $unique = str_replace('.', '', uniqid('', true));
            $finalName = sprintf('%s-%s.%s', $safeName, $unique, $extension);

            $originalName = $file->getClientOriginalName();

            $file->move($targetDir, $finalName);

            $stored[] = [
                'filename' => $originalName,
                'path' => sprintf('/mindar/packs/%s/targets/%s', $safeDir, $finalName),
                'label' => $baseName ?: $originalName,
            ];
        }

        return $stored;
    }

    private function updatePackPaths(ArPack $pack, string $oldSafeDir, string $newSafeDir): void
    {
        $oldPrefix = sprintf('/mindar/packs/%s', $oldSafeDir);
        $newPrefix = sprintf('/mindar/packs/%s', $newSafeDir);

        $mindPath = $pack->getMindPath();
        if ($mindPath) {
            $pack->setMindPath(str_replace($oldPrefix, $newPrefix, $mindPath));
        }

        $jsonPath = $pack->getPathJson();
        if ($jsonPath) {
            $pack->setPathJson(str_replace($oldPrefix, $newPrefix, $jsonPath));
        }

        $thumbnail = $pack->getThumbnail();
        if ($thumbnail) {
            $pack->setThumbnail(str_replace($oldPrefix, $newPrefix, $thumbnail));
        }

        $models = $pack->getModels();
        if (!empty($models)) {
            foreach ($models as &$model) {
                if (isset($model['path'])) {
                    $model['path'] = str_replace($oldPrefix, $newPrefix, $model['path']);
                }
            }
            unset($model);
            $pack->setModels($models);
        }

        $targets = $pack->getTargetImages();
        if (!empty($targets)) {
            foreach ($targets as &$target) {
                if (isset($target['path'])) {
                    $target['path'] = str_replace($oldPrefix, $newPrefix, $target['path']);
                }
            }
            unset($target);
            $pack->setTargetImages($targets);
        }
    }

    private function guessAssetType(?string $mimeType, string $extension): string
    {
        $extension = strtolower($extension);
        $mimeType = $mimeType ?? '';

        if (str_starts_with($mimeType, 'video/') || in_array($extension, ['mp4', 'webm', 'mov'], true)) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'image/') || in_array($extension, ['png', 'jpg', 'jpeg', 'gif'], true)) {
            return 'image';
        }

        if (in_array($extension, ['glb', 'gltf'], true) || str_starts_with($mimeType, 'model/')) {
            return 'model';
        }

        return 'file';
    }
}
