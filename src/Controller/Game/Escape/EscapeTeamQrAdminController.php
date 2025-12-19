<?php

namespace App\Controller\Game\Escape;

use App\Attribute\RequireParticipant;
use App\Classe\UserSessionTrait;
use App\Entity\Games\EscapeTeamQrGroup;
use App\Entity\Games\EscapeTeamQrPage;
use App\Entity\Users\Participant;
use App\Form\EscapeTeamQrGroupType;
use App\Form\EscapeTeamQrPageType;
use App\Lib\Links;
use App\Repository\EscapeTeamQrGroupRepository;
use App\Repository\EscapeTeamQrPageRepository;
use App\Repository\EscapeTeamRunRepository;
use App\Repository\EscapeWorkshopSessionRepository;
use App\Service\MobileLinkManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EscapeTeamQrAdminController extends AbstractController
{
    use UserSessionTrait;

    public function __construct(
        private readonly MobileLinkManager $mobileLinkManager,
    ) {
    }

    #[Route('/escape-team/admin/{slug}/qr-groups/new', name: 'escape_team_qr_group_new', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function createGroup(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EntityManagerInterface $em,
        string $slug,
    ): Response {
        if ($redirect = $this->guardMasterAccess($participant, $workshopRepository)) {
            return $redirect;
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux gérer que tes propres sessions.');
        }

        $group = (new EscapeTeamQrGroup())
            ->setRun($run);

        $form = $this->createForm(EscapeTeamQrGroupType::class, $group, [
            'submit_label' => 'Créer le groupe QR',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $group->setUpdatedAt(new DateTimeImmutable());
            $em->persist($group);
            $em->flush();

            $this->addFlash('success', 'Groupe QR code créé. Ajoute maintenant des pages QR.');

            return $this->redirectToRoute('escape_team_admin_pilot', ['slug' => $slug]);
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'run' => $run,
            'form' => $form->createView(),
            'directory' => 'team',
            'template' => 'team/qr_group_create.html.twig',
            'vartwig' => $vartwig,
            'isMasterParticipant' => $this->isMasterParticipant($participant, $workshopRepository),
            'title' => 'Nouveau groupe QR',
            'participant' => $participant,
            'active' => 'escape-team',
        ]);
    }

    #[Route('/escape-team/admin/{slug}/qr-groups/{id}/edit', name: 'escape_team_qr_group_edit', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function editGroup(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EscapeTeamQrGroupRepository $qrGroupRepository,
        EntityManagerInterface $em,
        string $slug,
        int $id,
    ): Response {
        if ($redirect = $this->guardMasterAccess($participant, $workshopRepository)) {
            return $redirect;
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $group = $qrGroupRepository->find($id) ?? throw $this->createNotFoundException();

        if ($group->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux gérer que tes propres sessions.');
        }

        $form = $this->createForm(EscapeTeamQrGroupType::class, $group, [
            'submit_label' => 'Mettre à jour le groupe QR',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $group->setUpdatedAt(new DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Groupe QR mis à jour.');


            return $this->redirectToRoute('escape_team_qr_group_show', [
                'slug' => $slug,
                'id' => $group->getId(),
            ]);
        }

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'run' => $run,
            'form' => $form->createView(),
            'directory' => 'team',
            'template' => 'team/qr_group_edit.html.twig',
            'vartwig' => $vartwig,
            'isMasterParticipant' => $this->isMasterParticipant($participant, $workshopRepository),
            'title' => sprintf('Modifier · %s', $group->getName()),
            'participant' => $participant,
            'active' => 'escape-team',
        ]);
    }

    #[Route('/escape-team/admin/{slug}/qr-groups/{id}', name: 'escape_team_qr_group_show', methods: ['GET', 'POST'])]
    #[RequireParticipant]
    public function showGroup(
        Request $request,
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EscapeTeamQrGroupRepository $qrGroupRepository,
        EscapeTeamQrPageRepository $qrPageRepository,
        EntityManagerInterface $em,
        string $slug,
        int $id,
    ): Response {
        if ($redirect = $this->guardMasterAccess($participant, $workshopRepository)) {
            return $redirect;
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $group = $qrGroupRepository->find($id) ?? throw $this->createNotFoundException();

        if ($group->getRun()?->getId() !== $run->getId()) {
            throw $this->createNotFoundException();
        }

        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux gérer que tes propres sessions.');
        }

        $page = (new EscapeTeamQrPage())
            ->setIdentificationCode($this->generateIdentificationCode());
        $pageForm = $this->createForm(EscapeTeamQrPageType::class, $page, [
            'submit_label' => 'Ajouter une page QR',
        ]);
        $pageForm->handleRequest($request);

        if ($pageForm->isSubmitted() && $pageForm->isValid()) {
            $page->setGroup($group);
            $em->persist($page);
            $group->setUpdatedAt(new DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Page QR ajoutée au groupe.');

            return $this->redirectToRoute('escape_team_qr_group_show', ['slug' => $slug, 'id' => $id]);
        }

        $pages = $qrPageRepository->findForGroup($group);

        $vartwig=$this->menuNav->templatepotins(
            '_index',
            Links::GAMES);

        return $this->render('pwa/escape/home.html.twig', [
            'run' => $run,
            'group' => $group,
            'pages' => $pages,
            'pageForm' => $pageForm->createView(),
            'directory' => 'team',
            'template' => 'team/qr_group_show.html.twig',
            'vartwig' => $vartwig,
            'title' => sprintf('QR groupe · %s', $group->getName()),
            'participant' => $participant,
            'active' => 'escape-team',
        ]);
    }

    #[Route('/escape-team/admin/{slug}/qr-groups/{groupId}/page/{pageId}/print', name: 'escape_team_qr_page_print', methods: ['GET'])]
    #[RequireParticipant]
    public function printPage(
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
        EscapeTeamRunRepository $runRepository,
        EscapeTeamQrGroupRepository $qrGroupRepository,
        EscapeTeamQrPageRepository $qrPageRepository,
        string $slug,
        int $groupId,
        int $pageId,
    ): Response {
        if ($redirect = $this->guardMasterAccess($participant, $workshopRepository)) {
            return $redirect;
        }

        $run = $runRepository->findOneByShareSlug($slug) ?? throw $this->createNotFoundException();
        $group = $qrGroupRepository->find($groupId) ?? throw $this->createNotFoundException();
        $page = $qrPageRepository->find($pageId) ?? throw $this->createNotFoundException();

        if ($group->getRun()?->getId() !== $run->getId() || $page->getGroup()?->getId() !== $group->getId()) {
            throw $this->createNotFoundException();
        }

        if ($run->getOwner()?->getId() !== $participant->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux gérer que tes propres sessions.');
        }

        $scanUrl = $this->generateUrl('escape_team_qr_page_view', ['token' => $page->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('pwa/escape/team/qr_page_print.html.twig', [
            'payload' => [
                'qr' => $this->mobileLinkManager->buildQrForUrl($scanUrl),
                'directUrl' => $scanUrl,
            ],
            'run' => $run,
            'page' => $page,
        ]);
    }

    #[Route('/escape-team/qr/page/{token}', name: 'escape_team_qr_page_view', methods: ['GET'])]
    public function viewPage(
        EscapeTeamQrPageRepository $qrPageRepository,
        string $token,
    ): Response {
        $page = $qrPageRepository->findOneByToken($token) ?? throw $this->createNotFoundException();
        $group = $page->getGroup();
        $run = $group?->getRun();

        return $this->render('pwa/escape/home_mob.html.twig', [
            'directory' => 'team',
            'template' => 'team/qr_page_view.html.twig',
            'vartwig' => $this->menuNav->templatepotins('_index_mob', Links::GAMES),
            'title' => 'Page QR équipe',
            'page' => $page,
            'run' => $run,
            'group' => $group,
            'isMasterParticipant' => false,
            'isnomenu' => false,
        ]);
    }

    private function guardMasterAccess(
        Participant $participant,
        EscapeWorkshopSessionRepository $workshopRepository,
    ): ?Response {
        if ($this->isMasterParticipant($participant, $workshopRepository)) {
            return null;
        }

        $this->addFlash('danger', 'Cette page est réservée au maître du jeu (session « master »).');

        return $this->redirectToRoute('dashboard_my_escapes');
    }


    private function generateIdentificationCode(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
