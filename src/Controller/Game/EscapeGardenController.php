<?php

namespace App\Controller\Game;

use App\Attribute\RequireParticipant;
use App\Classe\PublicSession;
use App\Entity\Games\EscapeGame;
use App\Entity\Media\Illustration;
use App\Entity\Users\Participant;
use App\Form\ParticipantProfileType;
use App\Lib\Links;
use App\Repository\EscapeGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class EscapeGardenController extends AbstractController
{
    use PublicSession;

    // src/Controller/LandingController.php
    #[Route('/escape/home', name: 'home')]
    public function index2(EscapeGameRepository $repo,Security $security): Response {

        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }
        $featuredGames = $repo->findBy(['published'=>true], ['id'=>'DESC'], 6);
        $recentGames   = $repo->findBy(['published'=>true], ['id'=>'DESC'], 6);

        $user = $security->getUser();
        $myGames = [];
        $lastPlayed = null;

        if ($participant) {
            $myGames = $repo->findBy(['owner'=>$user], ['id'=>'DESC'], 6);
            // si tu stockes le dernier slug joué en session/localStorage côté front, à adapter ici
            // $lastPlayed = ['slug' => 'mon-escape', 'title' => 'Titre'];
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_index',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig',[
            'featuredGames'=>$featuredGames,
            'recentGames'=>$recentGames,
            'myGames'=>$myGames,
            'lastPlayed'=>$lastPlayed,
            'replacejs'=>false,
            'directory'=>'landing',
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'participant'=>$participant,
            //'progression' => $progression,
            //'enigmes' => $enigmes
        ]);

    }


    #[Route('/escape/garden', name: 'garden')]
    #[RequireParticipant]
    public function index(): Response
    {

        $enigmes = [];
        $progression = [];
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'garden',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'newtemplate',
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'progression' => $progression,
            'enigmes' => $enigmes,
            'participant'=>$participant??null,
        ]);

    }

    #[Route('/escape', name: 'participant_entry')]
    public function entry(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'participant_entry',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'security',
            'customer'=>$this->customer,
            'vartwig'=>$vartwig
        ]);


    }

    #[Route('/escape/register', name: 'participant_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        $participant = new Participant();

        $participant->setPrenom($request->request->get('prenom'));
        $participant->setCodeAtelier($request->request->get('code_atelier'));
        $participant->setCodeSecret($request->request->get('code_secret'));


        $em->persist($participant);
        $em->flush();
        $this->requestStack->getSession()->set('participant_id', $participant->getId());
       // $request->getSession()->set('participant_id', $participant->getId());

        return $this->redirectToRoute('dashboard_my_escapes');
    }

    #[Route('/escape/login', name: 'participant_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em): Response
    {
        $prenom = $request->request->get('prenom');
        $codeAtelier = $request->request->get('code_atelier');
        $codeSecret = $request->request->get('code_secret');

        $participant = $em->getRepository(Participant::class)->findOneBy([
            'prenom' => $prenom,
            'codeAtelier' => $codeAtelier,
            'codeSecret' => $codeSecret,
        ]);

        if (!$participant) {
            $this->addFlash('error', 'Identifiants incorrects.');
            return $this->redirectToRoute('participant_entry');
        }

        //$request->getSession()->set('participant_id', $participant->getId());
        $this->requestStack->getSession()->set('participant_id', $participant->getId());

        return $this->redirectToRoute('dashboard_my_escapes');
    }

    #[Route('/escape/logout', name: 'participant_logout')]
    public function logout(Request $request, EntityManagerInterface $em): Response
    {
        $request->getSession()->invalidate();
        $this->requestStack->getSession()->invalidate();
        return $this->redirectToRoute('participant_entry');
    }

    #[Route('/escape/profil', name: 'participant_profile')]
    #[RequireParticipant]
    public function profil(Participant $participant,Request $request): Response
    {

        $form = $this->createForm(ParticipantProfileType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle remove avatar
            if ($form->get('removeAvatar')->getData()) {
                $participant->setAvatarName(null);
                $participant->setAvatarSize(null);
                // Pour que Vich supprime l’ancien fichier, on "touche" updatedAt
                $participant->setUpdatedAt(new \DateTimeImmutable());
            }

            // préférences (non mappées)
            $prefs = $participant->getPreferences() ?? [];
            $prefs['lang']  = $form->get('prefLang')->getData() ?: ($prefs['lang'] ?? 'fr');
            $prefs['theme'] = $form->get('prefTheme')->getData() ?: ($prefs['theme'] ?? 'light');
            $participant->setPreferences($prefs);

            $participant->setUpdatedAt(new \DateTimeImmutable());

            $this->em->flush();
            $this->addFlash('success', 'Profil mis à jour ✔');

            return $this->redirectToRoute('dashboard_my_escapes');
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_profil',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'dashboard',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/escape/mes-escapes', name: 'dashboard_my_escapes')]
    #[RequireParticipant]
    public function listEscapeGame( Participant $participant, SessionInterface $session,EntityManagerInterface $em): Response
    {

        $games=$participant->getEscapeGames();

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_liste',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'dashboard',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression'=>0,
            'games'=>$games
        ]);

    }

    #[Route('/escape/univers', name: 'escape_univers')]
    #[RequireParticipant]
    public function univers(Participant $participant,Request $request, SessionInterface $session, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {

        if ($request->isMethod('POST')) {
            $eg = new EscapeGame();
            $eg->setTitle($request->request->get('titre'));
            // avec ta modification
            $eg->ensureShareSlug(
                fn (string $seed) => strtolower($slugger->slug($seed)->toString())
            );

            $participant->addEscapeGame($eg);

            $univers = [
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
                        $em->persist($illustration);
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

            $eg->setUnivers($univers);
            $eg->setTitresEtapes($titresEtapes);
            $em->persist($eg);
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('wizard_step', ['id'=>$eg->getId(),'step' => 1]); // todo anciennement route vers 'escape_start'
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'univers',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'directory'=>'newtemplate',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression'=>0
        ]);

    }


    #[Route('/escape/start/{step}', name: 'escape_start',requirements: ['step' => '\d+'], defaults: ['step' => 1])]
    #[RequireParticipant]
    public function escapeStart(Participant $participant,int $step,SessionInterface $session, EntityManagerInterface $em): Response
    {

        $enigmes = $participant->getEnigmes();
        $progression = array_keys($enigmes);

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'garden',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [ // todo renvoyer vers nouvelle procedure
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'progression' => $progression,
            'enigmes' => $enigmes,
            'currentStep' => $step
        ]);
    }

    #[Route('/escape/{id}/delete', name: 'escape_delete', methods: ['POST'])]
    #[RequireParticipant]
    public function delete(
        Request $req,
        EscapeGame $eg,
        EM $em
    ): Response {
        $participant = $req->attributes->get('_participant');

        // Sécurité : seul le créateur peut supprimer
        if (!$eg->getOwner() || $eg->getOwner()->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException("Tu n'es pas autorisé à supprimer ce jeu.");
        }

        // Vérifie le CSRF
        if (!$this->isCsrfTokenValid('delete_escape_'.$eg->getId(), $req->request->get('_token'))) {
            $this->addFlash('danger','Jeton CSRF invalide.');
            return $this->redirectToRoute('participant_dashboard');
        }

        // (Optionnel) supprimer les fichiers images liés aux puzzles
        foreach ($eg->getPuzzles() as $puzzle) {
            $cfg = $puzzle->getConfig();
            if (isset($cfg['imagePath'])) {
                $abs = $this->getParameter('kernel.project_dir').'/public'.$cfg['imagePath'];
                if (is_file($abs)) { @unlink($abs); }
            }
        }

        $em->remove($eg);
        $em->flush();

        $this->addFlash('success','Escape supprimé avec succès.');
        return $this->redirectToRoute('participant_dashboard');
    }

    #[Route('/escape/publish', name: 'escape_publish', methods: ['POST'])]
    #[RequireParticipant]
    public function publish(Participant $participant,SessionInterface $session, EntityManagerInterface $em): Response
    {

        if (count($participant->getEnigmes()) < 6) {
            $this->addFlash('error', 'Tu dois avoir complété les 6 étapes avant de publier.');
            return $this->redirectToRoute('escape_start');
        }

        $participant->setPublished(true);
        $em->persist($participant);
        $em->flush();

        return $this->redirectToRoute('escape_public_view', ['id' => $participant->getId()]);
    }

    #[Route('/escape/show/{id}', name: 'escape_public_view')]
    #[RequireParticipant]
    public function showPublic(Participant $participant,int $id, EntityManagerInterface $em): Response
    {

        if ( !$participant->isPublished()) {
            throw $this->createNotFoundException('Jeu non publié.');
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'public_view',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'enigmes' => $participant->getEnigmes()
        ]);

    }

    #[Route('/escape/save/{step}', name: 'escape_save_step', methods: ['POST'])]
    #[RequireParticipant]
    public function saveStep(Participant $participant,int $step, Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {

        $contenu = $request->request->get('contenu');
        $enigmes = $participant->getEnigmes();
        $enigmes[$step] = $contenu;

        $participant->setEnigmes($enigmes);
        $em->persist($participant);
        $em->flush();

        return $this->redirectToRoute('escape_start', ['step' => $step + 1]);
    }


    private function getParticipant(SessionInterface $session, EntityManagerInterface $em): ?Participant
    {
        $id = $session->get('participant_id');
        return $id ? $em->getRepository(Participant::class)->find($id) : null;
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
            Links::ACCUEIL,
            'public_list',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'participants' => $participants
        ]);

    }

    #[Route('/docs/workshop', name: 'docs_workshop')]

    public function workshop(): Response
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_workshop',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }

    #[Route('/docs/legal_mentions', name: 'legal_mentions')]

    public function legalMentions(): Response
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_legal_mentions',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }

    #[Route('/docs/privacy', name: 'privacy')]

    public function privacy(): Response
    {
        $participantId = $this->requestStack->getSession()->get('participant_id');
        if ($participantId) {
            $participant = $this->em->getRepository(Participant::class)->find($participantId);
        }else{
            $participant = null;
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            '_privacy',
            0,
            "nocity");

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'docs',
            'participant' => $participant
        ]);

    }

}
