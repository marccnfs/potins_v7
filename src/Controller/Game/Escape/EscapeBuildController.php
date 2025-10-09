<?php

namespace App\Controller\Game\Escape;

use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeGame;
use App\Entity\Games\Puzzle;
use App\Form\PuzzleCryptexType;
use App\Lib\Links;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/eg')]
class EscapeBuildController extends AbstractController
{
    use UserSessionTrait;


    #[Route('/escape/build_one', name: 'build_one')]
    public function index(): Response
    {

        $vartwig=$this->menuNav->templatepotins('build',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1

        ]);

    }

    #[Route('/{id}/wizard', name:'wizard_overview', methods:['GET'])]
    public function overview(EscapeGame $eg): Response {
        $this->denyAccessUnlessGranted('EDIT', $eg);

        $vartwig=$this->menuNav->templatepotins('overview',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1,
            'eg'=>$eg

        ]);

    }

    /** Étape générique : route sur /etape/{step} et délègue selon le type */
    #[Route('/{id}/etape/{step}', name:'wizard_step', methods:['GET','POST'])]
    public function step(EscapeGame $eg, int $step, Request $req): Response {
        $this->denyAccessUnlessGranted('EDIT', $eg);

        $typeMap = [
            1 => 'cryptex',
            2 => 'qr_geo',
            3 => 'slider_puzzle',
            4 => 'logic_form',
            5 => 'video_quiz',
            6 => 'html_min',
        ];

        $type = $typeMap[$step] ?? throw $this->createNotFoundException();
        $puzzle = $eg->getOrCreatePuzzleByStep($step, $type);

        // Form type par type de puzzle
        $form = match($type) {
            'cryptex'       => $this->createForm(\App\Form\PuzzleCryptexType::class, $puzzle),
            'qr_geo'        => $this->createForm(\App\Form\PuzzleQrGeoType::class, $puzzle),
            'slider_puzzle' => $this->createForm(\App\Form\PuzzleSliderType::class, $puzzle),
            'logic_form'    => $this->createForm(\App\Form\PuzzleLogicType::class, $puzzle),
            'video_quiz'    => $this->createForm(\App\Form\PuzzleVideoQuizType::class, $puzzle),
            'html_min'      => $this->createForm(\App\Form\PuzzleHtmlMinType::class, $puzzle),
        };

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // Chaque FormType met à jour $puzzle->config et les champs communs
            $puzzle->setReady(true);
            $this->em->persist($puzzle);
            $this->em->flush();

            $next = $eg->nextIncompleteStep() ?? null;
            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }

        $vartwig=$this->menuNav->templatepotins("step{$step}",Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1,
            'eg'=>$eg,
            'puzzle'=>$puzzle,
            'form'=>$form,

        ]);

    }


    #[Route('/atelier/eg/{id}/etape/1', name: 'etapes')]
    public function Etape1(EscapeGame $eg, Request $req, EntityManagerInterface $em): Response
    {
        $puzzle = $eg->getPuzzleByStep(1) ?? (new Puzzle())
            ->setEscapeGame($eg)->setStep(1)->setType('cryptex');

        $form = $this->createForm(PuzzleCryptexType::class, $puzzle);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $solution = strtoupper((string)$form->get('solution')->getData() ?: '');
            $hashMode = (bool)$form->get('hashMode')->getData();

            $cfg = [
                'alphabet' => strtoupper($puzzle->getConfig()['alphabet'] ?? $form->get('alphabet')->getData()),
                'scramble' => (bool)($puzzle->getConfig()['scramble'] ?? $form->get('scramble')->getData()),
                'autocheck'=> (bool)($puzzle->getConfig()['autocheck'] ?? $form->get('autocheck')->getData()),
                'successMessage' => $puzzle->getConfig()['successMessage'] ?? $form->get('successMessage')->getData(),
            ];

            if ($hashMode) {
                // On ne stocke pas la solution claire
                $cfg['hashMode'] = true;
                $cfg['solutionHash'] = null; // calculé côté client (ou calcule-le côté serveur si tu préfères)
            } else {
                $cfg['hashMode'] = false;
                $cfg['solution']  = $solution;
            }

            $puzzle->setConfig($cfg);
            $puzzle->setTitle($form->get('title')->getData());
            $puzzle->setPrompt($form->get('prompt')->getData());
            $puzzle->setReady(true);

            $em->persist($puzzle); $em->flush();

            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }

        $vartwig=$this->menuNav->templatepotins('build',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1

        ]);

    }

    #[Route('/atelier/eg/{id}/etape/{step}/save', name: 'save_etapes')]
    public function SaveEtape(): Response
    {

        $vartwig=$this->menuNav->templatepotins('build',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1

        ]);

    }

    #[Route('/{id}/preview/{step}', name:'wizard_preview_step', methods:['GET'])]
    public function preview(EscapeGame $eg, int $step): Response {
        $this->denyAccessUnlessGranted('EDIT', $eg);
        $puzzle = $eg->getPuzzleByStep($step) ?? throw $this->createNotFoundException();

        $vartwig=$this->menuNav->templatepotins("preview/step{$step}",Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1,
            'eg'=>$eg,
            'puzzle'=>$puzzle

        ]);

    }

    #[Route('/{id}/publish', name:'wizard_publish', methods:['POST'])]
    public function publish(EscapeGame $eg): Response {
        $this->denyAccessUnlessGranted('EDIT', $eg);
        if (!$eg->isComplete()) {
            $this->addFlash('warning','Toutes les étapes ne sont pas prêtes.');
            return $this->redirectToRoute('wizard_overview', ['id'=>$eg->getId()]);
        }
        $eg->ensureShareSlug(fn($seed)=> (new \Symfony\Component\String\Slugger\AsciiSlugger())->slug($seed)->lower());
        $eg->setPublished(true);
        $this->em->flush();
        $this->addFlash('success','Escape Game publié !');

        $vartwig=$this->menuNav->templatepotins('play_entry',Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1,
            'slug'=>$eg->getShareSlug()

        ]);

    }

    #[Route('/play/{shareSlug}', name: 'play_game')]
    public function PlayGame(): Response
    {
        $vartwig=$this->menuNav->templatepotins('build',Links::GAMES);
        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'stape'=>1

        ]);

    }

}
