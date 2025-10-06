<?php
namespace App\Controller\Game;

use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\MobileLink;
use App\Entity\Media\Illustration;
use App\Form\EscapeUniverseType;
use App\Form\PuzzleCryptexType;
use App\Form\PuzzleQrGeoType;
use App\Form\PuzzleSliderType;
use App\Form\PuzzleLogicType;
use App\Form\PuzzleVideoQuizType;
use App\Form\PuzzleHtmlMinType;
use App\Lib\Links;
use App\Service\MobileLinkManager;
use App\Entity\Games\Puzzle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Attribute\RequireParticipant;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTimeInterface;


#[Route('/atelier/eg')]
class WizardController extends AbstractController
{
    use UserSessionTrait;

    private const STEP_DEFINITIONS = [
        1 => ['type' => 'cryptex',       'label' => 'Cryptex numérique'],
        2 => ['type' => 'qr_geo',        'label' => 'QR code géolocalisé / caché'],
        3 => ['type' => 'slider_puzzle', 'label' => 'Puzzle numérique'],
        4 => ['type' => 'logic_form',    'label' => 'Formulaire à piège logique'],
        5 => ['type' => 'video_quiz',    'label' => 'Vidéo interactive'],
        6 => ['type' => 'html_min',      'label' => 'Code HTML minimal'],
    ];

    #[Route('/wizard/start', name: 'wizard_start', methods: ['GET'])]
    #[RequireParticipant]
    public function start(Request $req): Response
    {
        $participant=$this->currentParticipant($req);

        // Création d’un nouveau EG minimal et redirection vers l’univers
        $eg = new EscapeGame();
        $eg->setOwner($participant);
        $eg->setTitle('Mon escape game');
        $eg->setUniverse([ 'contexte'=>'', 'objectif'=>'', 'modeEmploi'=>'', 'guide'=>null ]);
        $eg->setTitresEtapes([1=>'',2=>'',3=>'',4=>'',5=>'',6=>'']);
        $eg->setPublished(false);
        // $eg->setOwner($this->getUser()); // si tu as une sécu d’édition
        $this->em->persist($eg);
        $this->em->flush();

        return $this->redirectToRoute('wizard_universe', ['id'=>$eg->getId()]);
    }

    #[Route('/{id}/etape/{step}/qr', name:'wizard_step_qr', methods:['POST'])]
    #[RequireParticipant]
    public function stepQr(MobileLinkManager $mobile, EscapeGame $eg, int $step, Request $req): JsonResponse
    {
        $participant = $this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }

        $puzzle = $eg->getPuzzleByStep($step);
        if (!$puzzle || $puzzle->getType() !== 'qr_geo') {
            return $this->json(['error' => 'puzzle_not_ready'], Response::HTTP_BAD_REQUEST);
        }

        $cfg = $puzzle->getConfig() ?? [];
        $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
        if ($mode !== 'qr_only') {
            return $this->json(['error' => 'invalid_mode'], Response::HTTP_BAD_REQUEST);
        }

        $repo = $this->em->getRepository(MobileLink::class);
        $link = $repo->findOneBy([
            'participant' => $participant,
            'escapeGame'  => $eg,
            'step'        => $step,
            'usedAt'      => null,
        ]);

        $expired = $link && $link->getExpiresAt() && $link->getExpiresAt() < new \DateTimeImmutable();
        if (!$link || $expired) {
            $link = $mobile->create($participant, $eg, $step, ttlMinutes: 15);
        }

        $payload = $this->buildWizardQrPayload($mobile, $eg, $puzzle, $cfg, $link);
        if ($payload['updated']) {
            $this->em->persist($puzzle);
            $this->em->flush();
        }

        $expiresAt = $payload['expiresAt'];

        return $this->json([
            'qr'        => $payload['qr'],
            'token'     => $link->getToken(),
            'directUrl' => $payload['directUrl'],
            'expiresAt' => $expiresAt instanceof DateTimeInterface ? $expiresAt->format(DateTimeInterface::ATOM) : null,
            'answerUrl' => $payload['answerUrl'],
            'answerQr'  => $payload['answerQr'],
        ]);
    }

    #[Route('/{id}/etape/{step}/qr/print/{token}', name:'wizard_step_qr_print', methods:['GET'])]
    #[RequireParticipant]
    public function stepQrPrint(MobileLinkManager $mobile, EscapeGame $eg, int $step, string $token, Request $req): Response
    {
        $participant = $this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }

        $link = $this->em->getRepository(MobileLink::class)->findOneBy(['token' => $token]);
        if (!$link || $link->getEscapeGame()->getId() !== $eg->getId() || $link->getStep() !== $step) {
            throw $this->createNotFoundException();
        }

        if ($link->getParticipant()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }

        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
        if ($puzzle->getType() !== 'qr_geo') {
            throw $this->createNotFoundException();
        }

        $cfg = $puzzle->getConfig() ?? [];
        $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
        if ($mode !== 'qr_only') {
            throw $this->createNotFoundException();
        }

        $payload = $this->buildWizardQrPayload($mobile, $eg, $puzzle, $cfg, $link);
        if ($payload['updated']) {
            $this->em->persist($puzzle);
            $this->em->flush();
        }

        return $this->render('pwa/escape/wizard/qr_print.html.twig', [
            'eg'      => $eg,
            'puzzle'  => $puzzle,
            'link'    => $link,
            'payload' => $payload,
            'step'    => $step,
        ]);
    }

    #[Route('/{id}/etape/{step}/qr-answer/{code}', name:'wizard_step_qr_answer', methods:['GET'])]
    public function stepQrAnswer(EscapeGame $eg, int $step, string $code): Response
    {
        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();
        if ($puzzle->getType() !== 'qr_geo') {
            throw $this->createNotFoundException();
        }

        $cfg = $puzzle->getConfig() ?? [];
        $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
        $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];

        if ($mode !== 'qr_only' || ($qrOnly['answerSlug'] ?? null) !== $code) {
            throw $this->createNotFoundException();
        }

        return $this->render('mobile/qr_simple.html.twig', [
            'title'    => $qrOnly['answerTitle'] ?? 'Réponse de l’étape',
            'message'  => $qrOnly['answerBody'] ?? '',
            'subtitle' => $cfg['title'] ?? $puzzle->getTitle(),
            'variant'  => 'answer',
        ]);
    }


    #[Route('/wizard/{id}/universe', name: 'wizard_universe', methods: ['GET','POST'])]
    #[RequireParticipant]
    public function universe(EscapeGame $eg, Request $req, SluggerInterface $slugger): Response
    {
        // $this->denyAccessUnlessGranted('EDIT', $eg);
        $participant=$this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(EscapeUniverseType::class, null, ['eg'=>$eg]); // data null, champs unmapped

        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Titre
            $eg->setTitle((string)$form->get('title')->getData());
            $eg->ensureShareSlug(
                fn (string $seed) => strtolower($slugger->slug($seed)->toString())
            );
            $participant->addEscapeGame($eg);

            // 2) Univers (array)
            $u = $eg->getUniverse() ?? [];
            $u['contexte']   = (string)$form->get('context')->getData();
            $u['objectif']   = (string)$form->get('goal')->getData();
            $u['modeEmploi'] = (string)$form->get('howto')->getData();
            $u['guide']      = (string)$form->get('guide')->getData();
            $finalPrompt = trim((string)$form->get('finalPrompt')->getData());
            $finalReveal = trim((string)$form->get('finalReveal')->getData());
            if ($finalPrompt !== '' || $finalReveal !== '') {
                $u['finale'] = [
                    'prompt' => $finalPrompt,
                    'reveal' => $finalReveal,
                ];
            } else {
                unset($u['finale']);
            }
            $eg->setUniverse($u);

            // 3) Titres d’étapes (array 1..6)
            $titles = $eg->getTitresEtapes() ?? [1=>'',2=>'',3=>'',4=>'',5=>'',6=>''];
            $stepsData = (array) $form->get('stepTitles')->getData(); // array indexé 1..6
            foreach (range(1,6) as $i) {
                $titles[$i] = (string)($stepsData[$i] ?? $titles[$i] ?? '');
            }
            $eg->setTitresEtapes($titles);

            // 4) Ajout d’images (FileType multiple, unmapped)
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $newImages */
            $newImages = $form->get('newImages')->getData() ?? [];
            foreach ($newImages as $file) {
                if (!$file) continue;
                $illu = new Illustration();
                // Vich : propriété imageFile
                $illu->setImageFile($file);
                $illu->setEscapeGame($eg);
                $this->em->persist($illu);
            }

            // 5) Suppression images cochées
            $toDeleteIds = (array) $form->get('deleteImages')->getData();
            if ($toDeleteIds) {
                foreach ($eg->getIllustrations() as $illu) {
                    if (in_array($illu->getId(), $toDeleteIds, true)) {
                        $this->em->remove($illu); // Vich supprimera le fichier
                    }
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Univers enregistré ✔');

            // Redirige vers l’overview (ou étape 1 si tu préfères enchaîner)
            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }

        // Pour la colonne gauche (progress)
        $readyMap = [];
        foreach (range(1,6) as $i) {
            $p = $eg->getPuzzleByStep($i) ?? null; // si tu as ce helper
            $readyMap[$i] = $p ? (bool)$p->isReady() : false;
        }

        $vartwig=$this->menuNav->templatepotins('_universe',Links::GAMES);


        return $this->render('pwa/escape/home.html.twig', [
            'directory'=>'wizard',
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'eg'      => $eg,
            'form'    => $form,
            'total'   => 6,
            'readyMap'=> $readyMap,
            'participant'=>$participant,
        ]);
    }

    #[Route('/{id}/wizard', name:'wizard_overview', methods:['GET'])]
    #[RequireParticipant]
    public function overview(EscapeGame $eg, Request $req ): Response {

       // $this->denyAccessUnlessGranted('EDIT', $eg);
        $participant=$this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }

        $this->em->refresh($eg);                     // recharge la racine
        $eg->getPuzzles()->toArray();          // initialise la collection (JOIN lazy)

        $stepSummaries = [];
        $completedSteps = 0;
        foreach (self::STEP_DEFINITIONS as $index => $definition) {
            $puzzle = $eg->getPuzzleByStep($index);
            $isReady = $puzzle?->isReady() ?? false;
            if ($isReady) {
                ++$completedSteps;
            }
            $stepSummaries[$index] = [
                'label'  => $definition['label'],
                'type'   => $definition['type'],
                'ready'  => $isReady,
                'puzzle' => $puzzle,
            ];
        }

        $totalSteps = count(self::STEP_DEFINITIONS);
        $completionRate = $totalSteps > 0 ? (int) floor(($completedSteps / $totalSteps) * 100) : 0;
        $vartwig=$this->menuNav->templatepotins('_overview',Links::GAMES);

        $response = $this->render('pwa/escape/home.html.twig', [
            'directory'=>'wizard',
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'stape'=>1,
            'eg'=>$eg,
            'participant'=>$participant,
            'stepSummaries'=>$stepSummaries,
            'completedSteps'=>$completedSteps,
            'totalSteps'=>$totalSteps,
            'completionRate'=>$completionRate,
            'nextStep'=>$eg->nextIncompleteStep(),
            'canPublish'=>$completedSteps === $totalSteps,

        ]);

        // Empêcher Turbo/HTTP cache de réutiliser la page
        $response->headers->set('Turbo-Cache-Control', 'no-cache'); // Turbo
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('max-age', 0);

        return $response;

    }

    #[Route('/{id}/etape/{step}', name:'wizard_step', methods:['GET','POST'])]
    #[RequireParticipant]
    public function step(EscapeGame $eg, int $step, Request $req): Response {

        $participant=$this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }
        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

        $type = $this->getStepType($step);

        $puzzle = $eg->getOrCreatePuzzleByStep($step, $type);
        $puzzle->setTitle($eg->getTitresEtapes()[$step]??"");
        $puzzle->setType($type);
        $this->em->persist($puzzle);
        $this->em->flush();

        //dump($puzzle->getConfig());

        $form = match($type) {
            'cryptex'       => $this->createForm(PuzzleCryptexType::class, null, ['config' => $puzzle->getConfig() ?? []]),
            'qr_geo'        => $this->createForm(PuzzleQrGeoType::class, null, ['config' => $puzzle->getConfig() ?? []]),
            'slider_puzzle' => $this->createForm(PuzzleSliderType::class, null, ['config' => $puzzle->getConfig() ?? []]),
            'logic_form'    => $this->createForm(PuzzleLogicType::class, null, ['config' => $puzzle->getConfig() ?? []]),
            'video_quiz'    => $this->createForm(PuzzleVideoQuizType::class, null, ['config' => $puzzle->getConfig() ?? []]),
            'html_min'      => $this->createForm(PuzzleHtmlMinType::class, null, ['config' => $puzzle->getConfig() ?? []]),
        };

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // Champs communs
            $puzzle->setTitle((string)$form->get('title')->getData());
            $puzzle->setPrompt($form->has('prompt') ? $form->get('prompt')->getData() : null);
            $cfg = $puzzle->getConfig() ?? [];

            // Écritures config spécifiques
            switch ($type) {
                case 'cryptex': {
                    $old = $puzzle->getConfig();

                    $title   = (string)$form->get('title')->getData();
                    $prompt  = (string)$form->get('prompt')->getData();
                    $solution   = strtoupper((string)$form->get('solution')->getData() ?: '');
                    $hashMode   = (bool)$form->get('hashMode')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());

                    // 3) Indices (JSON) — normalisation & exigence >= 1
                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));

                    $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                    return $this->render('pwa/escape/home.html.twig', [
                        'directory'   => 'wizard',
                        'replacejs'   => false,
                        'vartwig'     => $vartwig,
                        'eg'          => $eg,
                        'puzzle'      => $puzzle,
                        'form'        => $form,
                        'participant' => $participant,
                        'stape'       => $step,
                    ]);
                }

                    // Fusion propre
                    $cfg = array_replace($old, [
                        'title'     => $title,
                        'prompt'    => $prompt,
                        'hashMode' => false,
                        'solution'      => $solution,
                        //'okMessage' => $okMsg,
                        'solutionHash' => '',
                        'alphabet' => strtoupper((string)$form->get('alphabet')->getData() ?: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'scramble' => (bool)$form->get('scramble')->getData(),
                        'autocheck'=> (bool)$form->get('autocheck')->getData(),
                        'successMessage' => (string)$form->get('successMessage')->getData() ?: 'Bravo !',
                        'finalClue' => $finalClue,
                        'hints'     => $hints,
                    ]);

                    if ($hashMode) {
                        $cfg['hashMode'] = true;
                        // tu peux pré-calculer le hash côté serveur si tu veux, ici on laisse vide pour calcul client
                        $cfg['solutionHash'] = $puzzle->getConfig()['solutionHash'] ?? '';
                    } else {
                        if ($solution === '') {
                            $this->addFlash('danger','La solution ne peut pas être vide (mode clair).');

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);


                            return $this->render('pwa/escape/home.html.twig', [
                                'directory'=>'wizard',
                                'replacejs'=>false,
                                'vartwig'=>$vartwig,
                                'eg'=>$eg,
                                'puzzle'=>$puzzle,
                                'form'=>$form

                            ]);

                        }
                    }

                    $puzzle->setConfig($cfg);
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);
                    $puzzle->setReady(true);
                    break;
                }
                case 'qr_geo': {
                    $old = $puzzle->getConfig();
                    $title   = (string)$form->get('title')->getData();
                    $prompt  = (string)$form->get('prompt')->getData();
                    $target=['lat' => (float)$form->get('lat')->getData(),'lng' => (float)$form->get('lng')->getData()];
                    $radiusMeters =(int)$form->get('radiusMeters')->getData();
                    $okMessage = (string)$form->get('okMessage')->getData();
                    $denyMessage = (string)$form->get('denyMessage')->getData();
                    $needHttpsMessage = (string)$form->get('needHttpsMessage')->getData();
                    $mode = (string) $form->get('mode')->getData();
                    $qrValidateMessage = trim((string)$form->get('qrValidateMessage')->getData());
                    $qrAnswerTitle = trim((string)$form->get('qrAnswerTitle')->getData());
                    $qrAnswerBody = (string)$form->get('qrAnswerBody')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());

                    // 3) Indices (JSON) — normalisation & exigence >= 1
                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);


                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'   => 'wizard',
                            'replacejs'   => false,
                            'vartwig'     => $vartwig,
                            'eg'          => $eg,
                            'puzzle'      => $puzzle,
                            'form'        => $form,
                            'participant' => $participant,
                            'stape'       => $step,
                        ]);
                    }
                    // Fusion propre
                    $cfg = $old;
                    if (!is_array($cfg)) { $cfg = []; }

                    if ($mode === 'qr_only') {
                        $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];
                        if (!isset($qrOnly['answerSlug']) || !is_string($qrOnly['answerSlug']) || $qrOnly['answerSlug'] === '') {
                            $qrOnly['answerSlug'] = bin2hex(random_bytes(5));
                        }
                        $qrOnly['validateMessage'] = $qrValidateMessage !== '' ? $qrValidateMessage : 'Bravo !';
                        $qrOnly['answerTitle'] = $qrAnswerTitle !== '' ? $qrAnswerTitle : 'Réponse de l’étape';
                        $qrOnly['answerBody'] = $qrAnswerBody;

                        $cfg = array_replace($cfg, [
                            'title'  => $title,
                            'prompt' => $prompt,
                            'mode'   => 'qr_only',
                            'qrOnly' => $qrOnly,
                            'finalClue' => $finalClue,
                            'hints'  => $hints,
                        ]);
                    } else {
                        unset($cfg['qrOnly']);
                        $cfg = array_replace($cfg, [
                            'title'     => $title,
                            'prompt'    => $prompt,
                            'mode'      => 'geo',
                            'target'    => $target,
                            'radiusMeters'    => $radiusMeters,
                            'okMessage' => $okMessage,
                            'denyMessage'      => $denyMessage,
                            'needHttpsMessage' => $needHttpsMessage,
                            'finalClue' => $finalClue,
                            'hints'     => $hints,
                        ]);
                    }

                    $puzzle->setConfig($cfg);
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);
                    $puzzle->setReady(true);
                    break;
                }
                case 'slider_puzzle': {

                    // 0) Config existante
                    $old = $puzzle->getConfig();

                    // 1) Champs "simples"
                    $title   = (string) $form->get('title')->getData();
                    $prompt  = (string) $form->get('prompt')->getData();
                    $rows    = (int) ($form->get('rows')->getData() ?? ($old['rows'] ?? 3));
                    $cols    = (int) ($form->get('cols')->getData() ?? ($old['cols'] ?? 3));
                    $okMsg   = (string) $form->get('okMessage')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());

                    // bornes de sécurité (au cas où)
                    $rows = max(2, min(10, $rows));
                    $cols = max(2, min(10, $cols));


                    // 2) Fichier image (UploadedFile|null) — OPTIONNEL en édition
                    $file = $form->get('imageFile')->getData();
                    $imagePath = $old['imagePath'] ?? null;

                    if ($file) {
                        $ext = $file->guessExtension() ?: 'jpg';
                        $new = uniqid('img_').'.'.$ext;

                        // Déplacement vers le dossier public (déclaré dans services.yaml)
                        $file->move($this->getParameter('puzzle_images_dir'), $new);
                        $newPublic = $this->getParameter('puzzle_images_public').'/'.$new;
                        // (facultatif) supprimer l’ancienne image si elle existe et que le chemin change
                        $oldPublic = $old['imagePath'] ?? null;
                        if ($oldPublic && $oldPublic !== $newPublic) {
                            $abs = $this->getParameter('kernel.project_dir').'/public'.$oldPublic;
                            if (is_file($abs)) { @unlink($abs); }
                        }
                        $imagePath = $newPublic;

                    }
                    if (!$imagePath) {
                        $this->addFlash('danger','Merci de téléverser une image.');

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'=>'wizard',
                            'replacejs'=>false,
                            'vartwig'=>$vartwig,
                            'eg'=>$eg,
                            'puzzle'=>$puzzle,
                            'form'=>$form,
                            'participant'=>$participant,
                            'stape'       => $step,

                        ]);

                    }
                    // 3) Indices (JSON) — normalisation & exigence >= 1
                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);


                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'   => 'wizard',
                            'replacejs'   => false,
                            'vartwig'     => $vartwig,
                            'eg'          => $eg,
                            'puzzle'      => $puzzle,
                            'form'        => $form,
                            'participant' => $participant,
                            'stape'       => $step,
                        ]);
                    }

                    // 4) Fusion propre de la config (on ne perd pas les autres clés)
                    $cfg = array_replace($old, [
                        'title'     => $title,
                        'prompt'    => $prompt,
                        'imagePath' => $imagePath,
                        'rows'      => $rows,
                        'cols'      => $cols,
                        'okMessage' => $okMsg,
                        'finalClue' => $finalClue,
                        'hints'     => $hints,
                    ]);

                    $puzzle->setConfig($cfg);

                    // (si ton entité Puzzle a aussi ces champs dédiés)
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);

                    // prêt seulement s’il y a une image
                    $puzzle->setReady(true);

                    break;

                }
                case 'logic_form': {
                    $old = $puzzle->getConfig();
                    $title   = (string)$form->get('title')->getData();
                    $prompt  = (string)$form->get('prompt')->getData();
                    $raw = (string)$form->get('questionsJson')->getData();
                    $okMsg   = (string)$form->get('okMessage')->getData();
                    $failMsg = (string)$form->get('failMessage')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());

                    try {
                        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) {
                        $this->addFlash('danger','JSON invalide pour les questions.');

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'=>'wizard',
                            'replacejs'=>false,
                            'vartwig'=>$vartwig,
                            'eg'=>$eg,
                            'puzzle'=>$puzzle,
                            'form'=>$form,
                            'participant'=>$participant??null,
                        ]);
                    }

                    // JSON questions
                    $questionsJson = (string) $form->get('questionsJson')->getData();
                    $questions = $this->parseLogicQuestionsJson($questionsJson);
                    if (count($questions) < 1) {
                        $form->get('questionsJson')->addError(new FormError('Ajoute au moins une question valide (voir l’exemple).'));
                        // re-render ton template d’édition (même que d’habitude)

                        $vartwig=$this->menuNav->templatepotins("step{$step}",links::GAMES);


                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'=>'wizard',
                            'replacejs'=>false,
                            'vartwig'=>$vartwig,
                            'eg'=>$eg,
                            'puzzle'=>$puzzle,
                            'form'=>$form,
                            'participant'=>$participant,
                            'stape'=>$step,
                        ]);
                    }


                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));

                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'   => 'wizard',
                            'replacejs'   => false,
                            'vartwig'     => $vartwig,
                            'eg'          => $eg,
                            'puzzle'      => $puzzle,
                            'form'        => $form,
                            'participant' => $participant,
                            'stape'       => $step,
                        ]);
                    }

                    // Fusion propre
                    $cfg = array_replace($old, [
                        'title'     => $title,
                        'prompt'    => $prompt,
                        'questions'   => $questions,
                        'okMessage' => $okMsg,
                        'failMessage' =>$failMsg,
                        'finalClue' => $finalClue,
                        'hints'     => $hints,
                    ]);

                    $puzzle->setConfig($cfg);
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);
                    $puzzle->setReady(true);
                    break;
                }

                case 'video_quiz': {
                    $old = $puzzle->getConfig();

                    $title   = (string)$form->get('title')->getData();
                    $prompt  = (string)$form->get('prompt')->getData();
                    $okMsg   = (string)$form->get('okMessage')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());


                    $cues = [];
                    $raw = (string)$form->get('cuesJson')->getData();
                    if ($raw !== '') {
                        try { $cues = json_decode($raw, true, 512, JSON_THROW_ON_ERROR) ?: []; }
                        catch (\Throwable $e) { $this->addFlash('warning','JSON des questions invalide.'); }
                    }

                    // Fichier optionnel
                    $file = $form->get('videoFile')->getData(); // UploadedFile|null
                    $videoPath = $old['videoPath'] ?? null;
                    if ($file) {
                        $new = uniqid('vid_').'.'.$file->guessExtension();
                        $file->move($this->getParameter('puzzle_videos_dir'), $new);
                        $newPublic = $this->getParameter('puzzle_videos_public').'/'.$new;

                        // supprimer l’ancien si différent (facultatif)
                        if (!empty($old['videoPath']) && $old['videoPath'] !== $newPublic) {
                            $abs = $this->getParameter('kernel.project_dir').'/public'.$old['videoPath'];
                            if (is_file($abs)) @unlink($abs);
                        }
                        $videoPath = $newPublic;
                    }

                    if (!$videoPath) {
                        $this->addFlash('danger','Merci de téléverser une vidéo .mp4.');
                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'=>'wizard',
                            'replacejs'=>false,
                            'vartwig'=>$vartwig,
                            'eg'=>$eg,
                            'puzzle'=>$puzzle,
                            'form'=>$form,
                            'participant'=>$participant,
                        ]);

                    }
                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));
                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'   => 'wizard',
                            'replacejs'   => false,
                            'vartwig'     => $vartwig,
                            'eg'          => $eg,
                            'puzzle'      => $puzzle,
                            'form'        => $form,
                            'participant' => $participant,
                            'stape'       => $step,
                        ]);
                    }
                    // Fusion propre
                    $cfg = array_replace($old, [
                        'title'     => $title,
                        'prompt'    => $prompt,
                        'videoPath' => $videoPath,
                        'cues'      => \is_array($cues) ? $cues : [],
                        'okMessage' => $okMsg,
                        'finalClue' => $finalClue,
                        'hints'     => $hints,
                    ]);

                    $puzzle->setConfig($cfg);
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);
                    $puzzle->setReady(true);
                    break;
                }

                case 'html_min': {
                    $old = $puzzle->getConfig();
                    $title   = (string)$form->get('title')->getData();
                    $prompt  = (string)$form->get('prompt')->getData();
                    $starterHtml = (string)$form->get('starterHtml')->getData();
                    $checks     = json_decode((string)$form->get('checksJson')->getData(), true) ?: [];
                    $okMessage   = (string)$form->get('okMessage')->getData();
                    $finalClue = trim((string)$form->get('finalClue')->getData());

                    // 3) Indices (JSON) — normalisation & exigence >= 1
                    $hintsJson = (string) $form->get('hintsJson')->getData();
                    $hints     = $this->parseHintsFromJson($hintsJson); // <— helper montré précédemment

                    if (count($hints) < 1) {
                        $form->get('hintsJson')->addError(new FormError('Ajoute au moins un indice.'));
                        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

                        return $this->render('pwa/escape/home.html.twig', [
                            'directory'   => 'wizard',
                            'replacejs'   => false,
                            'vartwig'     => $vartwig,
                            'eg'          => $eg,
                            'puzzle'      => $puzzle,
                            'form'        => $form,
                            'participant' => $participant,
                            'stape'       => $step,
                        ]);
                    }

                    // Fusion propre
                    $cfg = array_replace($old, [
                        'title'     => $title,
                        'prompt'    => $prompt,
                        'starterHtml' => $starterHtml,
                        'checks'      => $checks,
                        'okMessage' => $okMessage,
                        'finalClue' => $finalClue,

                        'hints'     => $hints,
                    ]);

                    $puzzle->setConfig($cfg);
                    $puzzle->setTitle($title);
                    $puzzle->setPrompt($prompt);
                    $puzzle->setReady(true);
                    break;
                }
            }

            $this->em->persist($puzzle);
            $this->em->flush();

            $this->addFlash('success', "Étape {$step} enregistrée.");
            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }

        $qrOnlyOptions = null;
        if ($step === 2) {
            $cfg = $puzzle->getConfig() ?? [];
            $mode = is_string($cfg['mode'] ?? null) ? $cfg['mode'] : 'geo';
            if ($mode === 'qr_only' && $puzzle->getId()) {
                $qrOnlyOptions = [
                    'fetchUrl' => $this->generateUrl('wizard_step_qr', ['id' => $eg->getId(), 'step' => $step]),
                    'printUrl' => $this->generateUrl('wizard_step_qr_print', ['id' => $eg->getId(), 'step' => $step, 'token' => '__TOKEN__']),
                ];
            }
        }
        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'wizard',
            'vartwig'=>$vartwig,
            'progression'=>0,
            'participant'=>$participant,
            'eg'=>$eg,
            'puzzle'=>$puzzle,
            'form'=>$form,
            'step'=>$step,
            'qrOnlyOptions' => $qrOnlyOptions,
             '_t' => time(),         // bust cache
            ]);

    }

    #[Route('/{id}/preview/{step}', name:'wizard_preview_step', methods:['GET'])]
    #[RequireParticipant]
    public function preview(MobileLinkManager $mobile,EscapeGame $eg, int $step, Request $req): Response {

        $participant=$this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }
       // $this->denyAccessUnlessGranted('EDIT', $eg);
        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();

        // --- AJOUT SPÉCIFIQUE QR GEO ---
        $extras = [];
        if ($puzzle->getType() === 'qr_geo') {

            $link = $this->em->getRepository(MobileLink::class)->findOneBy([
                'participant' => $participant,
                'escapeGame'  => $eg,
                'step'        => $step,
                'usedAt'      => null,
            ]);

            $expired = $link && $link->getExpiresAt() && $link->getExpiresAt() < new \DateTimeImmutable();
            if (!$link || $expired) {
                $link = $mobile->create($participant, $eg, $step, ttlMinutes: 15);
            }

            $extras = [
                'qr'        => $mobile->buildQrDataUri($link),                  // data:image/png;base64,...
                'token'     => $link->getToken(),
                'expiresAt' => $link->getExpiresAt(),
            ];
        }

        $vartwig=$this->menuNav->templatepotins("step",Links::GAMES);


        return $this->render('pwa/escape/home.html.twig', [
            'directory'=>'preview',
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'eg'     => $eg,
            'puzzle' => $puzzle,
            'cfg'    => $puzzle->getConfig() ?? [],
            'step'   => $step,
            'extras' => $extras,
            'participant'=>$participant,
        ]);

    }

    #[Route('/{id}/publish', name:'wizard_publish', methods:['POST'])]
    #[RequireParticipant]
    public function publish(EscapeGame $eg, Request $req): Response {
        $participant=$this->currentParticipant($req);
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException();
        }
      //  $this->denyAccessUnlessGranted('EDIT', $eg);

        if (!$eg->isComplete()) {
            $this->addFlash('warning','Toutes les étapes ne sont pas prêtes.');
            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }
        $eg->ensureShareSlug(fn($seed)=> (new AsciiSlugger())->slug($seed)->lower());
        $eg->setPublished(true);
        $this->em->flush();
        $this->addFlash('success','Escape game publié !');
        return $this->redirectToRoute('play_entry', ['slug'=>$eg->getShareSlug()]);
    }

    private function getStepType(int $step): string
    {
        if (!isset(self::STEP_DEFINITIONS[$step])) {
            throw $this->createNotFoundException();
        }

        return self::STEP_DEFINITIONS[$step]['type'];
    }

    /**
     * @return array{
     *     qr: string,
     *     directUrl: string,
     *     answerUrl: string,
     *     answerQr: string,
     *     expiresAt: ?\DateTimeInterface,
     *     updated: bool
     * }
     */
    private function buildWizardQrPayload(MobileLinkManager $mobile, EscapeGame $eg, Puzzle $puzzle, array $cfg, MobileLink $link): array
    {
        $qrOnly = is_array($cfg['qrOnly'] ?? null) ? $cfg['qrOnly'] : [];
        $updated = false;

        if (!isset($qrOnly['answerSlug']) || !is_string($qrOnly['answerSlug']) || $qrOnly['answerSlug'] === '') {
            $qrOnly['answerSlug'] = bin2hex(random_bytes(5));
            $cfg['qrOnly'] = $qrOnly;
            $puzzle->setConfig($cfg);
            $updated = true;
        }

        $answerUrl = $this->generateUrl('wizard_step_qr_answer', [
            'id'   => $eg->getId(),
            'step' => $puzzle->getStep(),
            'code' => $qrOnly['answerSlug'],
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return [
            'qr'        => $mobile->buildQrDataUri($link),
            'directUrl' => $this->generateUrl('mobile_entry', ['token' => $link->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
            'answerUrl' => $answerUrl,
            'answerQr'  => $mobile->buildQrForUrl($answerUrl),
            'expiresAt' => $link->getExpiresAt(),
            'updated'   => $updated,
        ];
    }


    public function mergeHints($form,$cfg,$puzzle){
        $hints = [];
        $hintsRaw = (string) $form->get('hintsJson')->getData();
        if ($hintsRaw !== '') {
            try {
                $hints = json_decode($hintsRaw, true, 512, JSON_THROW_ON_ERROR);
                $hints = \is_array($hints)
                    ? array_values(array_filter($hints, fn($s)=>\is_string($s) && trim($s) !== ''))
                    : [];
                $cfg['hints'] = $hints;
            } catch (\Throwable $e) {
                $this->addFlash('warning','JSON des indices invalide — indices ignorés.');
            }
        }

        $puzzle->setConfig($cfg);
    }

    private function parseJsonArray(string $raw): array {
        if (trim($raw) === '') return [];
        try {
            $d = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            return is_array($d) ? $d : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
    private function parseHints(string $raw): array {
        $arr = $this->parseJsonArray($raw);
        return array_values(array_filter($arr, fn($s)=>is_string($s) && trim($s) !== ''));
    }

    private function parseHintsFromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) return [];
        $hints = array_values(array_filter(
            array_map(static fn($s)=>trim((string)$s), $data),
            static fn($s)=>$s!==''
        ));
        return $hints;
    }

    private function parseLogicQuestionsJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) return [];

        $out = [];
        foreach ($data as $i => $q) {
            if (!is_array($q)) continue;
            $label = trim((string)($q['label'] ?? ''));
            $options = $q['options'] ?? [];
            $solution = $q['solution'] ?? [];

            if ($label === '' || !is_array($options) || count($options) < 1) continue;

            // normalise options
            $opts = [];
            foreach ($options as $o) {
                $oid = isset($o['id']) ? trim((string)$o['id']) : null;
                $olab = isset($o['label']) ? trim((string)$o['label']) : null;
                if ($oid !== null && $oid !== '' && $olab !== null && $olab !== '') {
                    $opts[] = ['id'=>$oid, 'label'=>$olab];
                }
            }
            if (!$opts) continue;

            // normalise solution
            $must = array_values(array_filter(array_map('strval', $solution['must'] ?? [])));
            $mustNot = array_values(array_filter(array_map('strval', $solution['mustNot'] ?? [])));

            // garde uniquement les ids existants
            $validIds = array_column($opts, 'id');
            $must    = array_values(array_intersect($must, $validIds));
            $mustNot = array_values(array_intersect($mustNot, $validIds));

            $out[] = [
                'label'    => $label,
                'options'  => $opts,
                'solution' => ['must'=>$must, 'mustNot'=>$mustNot],
            ];
        }
        return $out;
    }


}
