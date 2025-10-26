<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeWorkshopSession;
use App\Entity\Users\Participant;
use App\Form\ParticipantProfileType;
use App\Lib\Links;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParticipantController extends AbstractController
{
    use UserSessionTrait;



    #[Route('/escape', name: 'participant_entry')]
    public function entry(): Response
    {
        $vartwig=$this->menuNav->templatepotins(
            'participant_entry',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'security',
            'vartwig'=>$vartwig
        ]);

    }

    #[Route('/escape/register', name: 'participant_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('participant', $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('participant_entry');
        }

        $prenom = trim((string) $request->request->get('prenom'));
        $codeAtelier = strtoupper(trim((string) $request->request->get('code_atelier')));
        $codeSecret = strtoupper(trim((string) $request->request->get('code_secret')));

        if ($prenom === '' || $codeAtelier === '' || $codeSecret === '') {
            $this->addFlash('error', 'Merci de renseigner ton prénom, le code atelier et ton code secret.');
            return $this->redirectToRoute('participant_entry');
        }

        $workshop = $this->em->getRepository(EscapeWorkshopSession::class)->findOneByCode($codeAtelier);
        if (!$workshop) {
            $this->addFlash('error', 'Ce code atelier n’est pas reconnu. Vérifie-le auprès de ton médiateur.');
            return $this->redirectToRoute('participant_entry');
        }

        $participant = new Participant();
        $participant->setPrenom($prenom);
        $participant->setCodeAtelier($workshop->getCode());
        $participant->setCodeSecret($codeSecret);

        $this->em->persist($participant);
        $this->em->flush();
        $this->requestStack->getSession()->set('participant_id', $participant->getId());

        return $this->redirectToRoute('dashboard_my_escapes');
    }

    #[Route('/escape/login', name: 'participant_login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('participant', $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('participant_entry');
        }

        $prenom = trim((string) $request->request->get('prenom'));
        $codeAtelier = strtoupper(trim((string) $request->request->get('code_atelier')));
        $codeSecret = strtoupper(trim((string) $request->request->get('code_secret')));

        $participant = $this->em->getRepository(Participant::class)->findOneBy([
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
            '_profil',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'replacejs'=>false,
            'directory'=>'dashboard',
            'vartwig'=>$vartwig,
            'participant' => $participant,
            'form' => $form,
        ]);
    }

}
