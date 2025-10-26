<?php

namespace DoctrineMigrations;

use App\Controller\Game\Escape\EntityManagerInterface;
use App\Controller\Game\Escape\EscapeGame;
use App\Controller\Game\Escape\Illustration;
use App\Controller\Game\Escape\Links;
use App\Controller\Game\Escape\Participant;
use App\Controller\Game\Escape\Request;
use App\Controller\Game\Escape\RequireParticipant;
use App\Controller\Game\Escape\Response;
use App\Controller\Game\Escape\Route;
use App\Controller\Game\Escape\SessionInterface;
use App\Controller\Game\Escape\SluggerInterface;

class oldfunctionController
{

    #[Route('/escape/universe', name: 'escape_universe')]
    #[RequireParticipant]
    public function universe(Participant $participant,Request $request, SessionInterface $session, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {

        if ($request->isMethod('POST')) {
            $eg = new EscapeGame();
            $eg->setTitle($request->request->get('titre'));
            // avec ta modification
            $eg->ensureShareSlug(
                fn (string $seed) => strtolower($slugger->slug($seed)->toString())
            );

            $participant->addEscapeGame($eg);

            $universe = [
                'titre' => $request->request->get('titre'),
                'contexte' => $request->request->get('contexte'),
                'objectif' => $request->request->get('objectif'),
                'mode_emploi' => $request->request->get('mode_emploi'),
                'guide' => $request->request->get('guide'),
            ];

            $files = $request->files->get('illustrations');
            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    if ($file) {
                        $illustration = new Illustration();
                        $illustration->setImageFile($file);
                        $illustration->setEscapeGame($eg);
                        $this->em->persist($illustration);
                    }
                }
            }


            $titresEtapes = [];
            for ($i = 1; $i <= 6; $i++) {
                $titreEtape = $request->request->get("etape_$i");
                if ($titreEtape) {
                    $titresEtapes[$i] = $titreEtape;
                }
            }

            $eg->setUniverse($universe);
            $eg->setTitresEtapes($titresEtapes);
            $this->em->persist($eg);
            $this->em->persist($participant);
            $this->em->flush();

            return $this->redirectToRoute('wizard_step', ['id'=>$eg->getId(),'step' => 1]); // todo anciennement route vers 'escape_start'
        }

        $vartwig=$this->menuNav->templatepotins(
            'universe',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'newtemplate',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression'=>0
        ]);

    }


    /*

        #[Route('/escape/garden', name: 'garden')]
        #[RequireParticipant]
        public function index(): Response
        {

            $enigmes = [];
            $progression = [];
            $participant = $this->getParticipantFromSession();
            $stepStates = array_fill(1, 6, 'locked');

            $vartwig=$this->menuNav->templatepotins(
                'garden',
                Links::GAMES);

            return $this->render('pwa/escape/home.html.twig', [
                'replacejs'=>false,
                'directory'=>'newtemplate',
                'vartwig'=>$vartwig,
                'progression' => $progression,
                'enigmes' => $enigmes,
                'participant'=>$participant??null,
                'stepStates' => $stepStates,
            ]);

        }
    */

    #[Route('/escape/start/{step}', name: 'escape_start',requirements: ['step' => '\d+'], defaults: ['step' => 1])]
    #[RequireParticipant]
    public function escapeStart(Participant $participant,int $step): Response
    {

        $step = max(1, min(6, $step));

        $enigmes = $this->normalizeEnigmes($participant->getEnigmes());
        $progressionData = $this->computeStepProgression($enigmes);
        $progression = $progressionData['completed'];
        $stateForStep = $progressionData['states'][$step] ?? 'locked';

        if ($stateForStep === 'locked') {
            $targetStep = $progressionData['nextStep'] ?? max($progression ?: [1]);
            $this->addFlash('warning', 'Tu dois compléter les étapes précédentes avant de passer à celle-ci.');
            return $this->redirectToRoute('escape_start', ['step' => $targetStep]);
        }

        $vartwig=$this->menuNav->templatepotins(
            'garden',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [ // todo renvoyer vers nouvelle procedure
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression' => $progression,
            'enigmes' => $enigmes,
            'currentStep' => $step,
            'stepStates' => $progressionData['states'],
            'nextStep' => $progressionData['nextStep'],
        ]);
    }


    #[Route('/escape/public', name: 'escape_public_list')]
    #[RequireParticipant]
    public function listPublic(EntityManagerInterface $em): Response
    {
        $participants = $em->getRepository(Participant::class)->findBy(
            ['published' => true],
            ['id' => 'DESC']
        );

        $vartwig=$this->menuNav->templatepotins(
            'public_list',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'participants' => $participants
        ]);

    }


    #[Route('/escape/save/{step}', name: 'escape_save_step', methods: ['POST'])]
    #[RequireParticipant]
    public function saveStep(Participant $participant,int $step, Request $request): Response
    {
        $step = max(1, min(6, $step));
        $contenu = $request->request->get('contenu');
        $contenu = \is_string($contenu) ? trim($contenu) : '';

        $enigmes = $this->normalizeEnigmes($participant->getEnigmes());
        $progressionData = $this->computeStepProgression($enigmes);
        $stateForStep = $progressionData['states'][$step] ?? 'locked';

        if ($stateForStep === 'locked') {
            $targetStep = $progressionData['nextStep'] ?? $step;
            $this->addFlash('warning', 'Tu dois compléter les étapes précédentes avant d\'accéder à cette énigme.');
            return $this->redirectToRoute('escape_start', ['step' => $targetStep]);
        }

        if ($contenu === '') {
            unset($enigmes[$step]);
        } else {
            $enigmes[$step] = $contenu;
        }

        ksort($enigmes);

        $participant->setEnigmes($enigmes);
        $this->em->persist($participant);
        $this->em->flush();

        $updatedProgression = $this->computeStepProgression($enigmes);
        if ($contenu === '') {
            $redirectStep = $step;
        } elseif ($updatedProgression['nextStep'] !== null) {
            $redirectStep = max($step, $updatedProgression['nextStep']);
        } else {
            $redirectStep = $step;
        }

        return $this->redirectToRoute('escape_start', ['step' => $redirectStep]);
    }

    #[Route('/escape/show/{id}', name: 'escape_public_view')]
    #[RequireParticipant]
    public function showPublic(Participant $participant,int $id): Response
    {

        if ( !$participant->isPublished()) {
            throw $this->createNotFoundException('Jeu non publié.');
        }

        $vartwig=$this->menuNav->templatepotins(
            'public_view',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'enigmes' => $participant->getEnigmes()
        ]);

    }


    #[Route('/escape/publish', name: 'escape_publish', methods: ['POST'])]
    #[RequireParticipant]
    public function publish(Participant $participant): Response
    {

        if (count($participant->getEnigmes()) < 6) {
            $this->addFlash('error', 'Tu dois avoir complété les 6 étapes avant de publier.');
            return $this->redirectToRoute('escape_start');
        }

        $participant->setPublished(true);
        $this->em->persist($participant);
        $this->em->flush();

        return $this->redirectToRoute('escape_public_view', ['id' => $participant->getId()]);
    }



    /**
     * @param array|null $enigmes
     * @return array<int, string>
     */
    private function normalizeEnigmes(?array $enigmes): array
    {
        if (empty($enigmes)) {
            return [];
        }

        $normalized = [];
        foreach ($enigmes as $step => $value) {
            $step = (int) $step;
            if ($step < 1 || $step > 6) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $normalized[$step] = (string) $value;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array<int, string> $enigmes
     * @return array{states: array<int, string>, completed: array<int>, nextStep: int|null}
     */
    private function computeStepProgression(array $enigmes): array
    {
        $states = [];
        $completed = [];

        for ($i = 1; $i <= 6; $i++) {
            $content = $enigmes[$i] ?? '';
            $isCompleted = trim((string) $content) !== '';

            if ($isCompleted) {
                $states[$i] = 'completed';
                $completed[] = $i;
            } else {
                $states[$i] = 'locked';
            }
        }

        $nextStep = null;
        for ($i = 1; $i <= 6; $i++) {
            if ($states[$i] !== 'completed') {
                $nextStep = $i;
                break;
            }
        }

        if ($nextStep !== null) {
            $states[$nextStep] = 'current';
        }

        return [
            'states' => $states,
            'completed' => $completed,
            'nextStep' => $nextStep,
        ];
    }

    /*
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

*/
    /*
  #[Route('/play/{slug}/step/{step}/qr-answer/{code}', name: 'play_qr_geo_answer', methods: ['GET'])]
  public function answer(EscapeGame $eg, int $step, string $code): Response
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
          'subtitle' => $cfg['title'] ?? $eg->getTitresEtapes()[$step] ?? 'Étape',
          'variant'  => 'answer',
      ]);
  }
  */

}
