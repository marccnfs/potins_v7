<?php

namespace App\Controller\BoardOffice;

use App\Classe\UserSessionTrait;
use App\Entity\Agenda\Event;
use App\Entity\Boards\Board;
use App\Form\Agenda\EventType;
use App\Lib\Links;
use App\Service\MenuNavigator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_MEMBER") or is_granted("ROLE_SUPER_ADMIN")'))]
#[Route('/board-office')]


final class EventController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/agenda/events/new', name: 'event_new')]
    public function new(Request $req): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        // valeur par défaut : aujourd’hui 09:00–10:00 (Europe/Paris)
        $nowParis = new \DateTimeImmutable('today 09:00', new \DateTimeZone('Europe/Paris'));
        $form = $this->createForm(EventType::class);

        $form->get('startsAtLocal')->setData($nowParis);
        $form->get('endsAtLocal')->setData($nowParis->modify('+1 hour'));
        $form->get('timezone')->setData('Europe/Paris');

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // conversion Local→UTC
            $tz = new \DateTimeZone($form->get('timezone')->getData() ?: 'Europe/Paris');
            $startLocal = $form->get('startsAtLocal')->getData(); // \DateTimeInterface
            $endLocal   = $form->get('endsAtLocal')->getData();

            if (!$startLocal || !$endLocal) {
                $this->addFlash('danger', 'Dates invalides.');
            } else {
                $startUtc = (new \DateTimeImmutable($startLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));
                $endUtc   = (new \DateTimeImmutable($endLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));

                $e = new Event(
                    title:      (string) $form->get('title')->getData(),
                    startsAtUtc:$startUtc,
                    endsAtUtc:  $endUtc,
                    timezone:   (string) $form->get('timezone')->getData(),
                );

                $e->setDescription($form->get('description')->getData());
                $e->setAllDay((bool) $form->get('isAllDay')->getData());
                $e->setLocationName($form->get('locationName')->getData());
                $e->setLocationAddress($form->get('locationAddress')->getData());
                $e->setCapacity($form->get('capacity')->getData());
                $e->setCommuneCode($form->get('communeCode')->getData() ?: 'autre');
                $e->setCategory($form->get('category')->getData());
                $e->setVisibility($form->get('visibility')->getData());
                $e->setPublished((bool) $form->get('published')->getData());

                $this->em->persist($e);
                $this->em->flush();

                $this->addFlash('success', 'Événement créé.');
                $eventDate = $e->getStartsAt()->setTimezone(new \DateTimeZone($e->getTimezone()));

                return $this->redirectToRoute('module_agenda', [
                    'date' => $eventDate->format('Y-m-d'),
                ]);
            }
        }
        $vartwig=$this->menuNav->templatePotins(
            '_form',Links::ACCUEIL);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'form' => $form,
            'mode' => 'new',
            'replacejs'=>false,
            'board' => $this->currentBoard(),
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
        ]);
    }

    #[Route('/agenda/events/{slug}/edit', name: 'event_edit')]
    public function edit(string $slug, Request $req): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $e = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$e) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EventType::class, $e);

        // Pré-remplir les champs *locaux* à partir de l’UTC stocké
        $tz = new \DateTimeZone($e->getTimezone());
        $form->get('startsAtLocal')->setData($e->getStartsAt()->setTimezone($tz));
        $form->get('endsAtLocal')->setData($e->getEndsAt()->setTimezone($tz));

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $tz = new \DateTimeZone($form->get('timezone')->getData() ?: 'Europe/Paris');
            $startLocal = $form->get('startsAtLocal')->getData();
            $endLocal   = $form->get('endsAtLocal')->getData();

            if (!$startLocal || !$endLocal) {
                $this->addFlash('danger', 'Dates invalides.');
            } else {
                $startUtc = (new \DateTimeImmutable($startLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));
                $endUtc   = (new \DateTimeImmutable($endLocal->format('Y-m-d H:i:s'), $tz))
                    ->setTimezone(new \DateTimeZone('UTC'));

                $e->setTitle($form->get('title')->getData());
                $e->setDescription($form->get('description')->getData());
                $e->setTimezone($form->get('timezone')->getData());
                $e->setAllDay((bool) $form->get('isAllDay')->getData());
                $e->setLocationName($form->get('locationName')->getData());
                $e->setLocationAddress($form->get('locationAddress')->getData());
                $e->setCapacity($form->get('capacity')->getData());
                $e->setCategory($form->get('category')->getData());
                $e->setVisibility($form->get('visibility')->getData());
                $e->setPublished((bool) $form->get('published')->getData());
                $e->setPeriod($startUtc, $endUtc);
                $e->setCommuneCode($form->get('communeCode')->getData() ?: 'autre');

                $this->em->flush();
                $this->addFlash('success', 'Événement mis à jour.');
                $eventDate = $e->getStartsAt()->setTimezone(new \DateTimeZone($e->getTimezone()));

                return $this->redirectToRoute('module_agenda', [
                    'date' => $eventDate->format('Y-m-d'),
                ]);
            }
        }

        return $this->renderDashboard('agenda','_form',4, [
            'form' => $form,
            'mode' => 'edit',
            'e'    => $e,
        ]);

    }

    #[Route('/agenda/events/{slug}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(string $slug, Request $req): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $this->denyUnlessCsrf($req, 'delete_'.$slug);

        $e = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$e) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($e);
        $this->em->flush();
        $this->addFlash('success', 'Événement supprimé.');

        return $this->redirectToRoute('agenda_index');
    }

    #[Route('/agenda/events/{slug}/duplicate', name: 'event_duplicate', methods: ['POST'])]
    public function duplicate(string $slug, Request $req): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $this->denyUnlessCsrf($req, 'duplicate_'.$slug);

        $source = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$source) {
            throw $this->createNotFoundException();
        }

        $copy = $source->duplicate();
        $this->em->persist($copy);
        $this->em->flush();

        $this->addFlash('success', 'Événement dupliqué. Vous pouvez maintenant le personnaliser.');

        return $this->redirectToRoute('event_edit', ['slug' => $copy->getSlug()], Response::HTTP_SEE_OTHER);
    }

    private function denyUnlessCsrf(Request $req, string $id): void
    {
        $token = $req->request->get('_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('CSRF invalide.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderDashboard(string $directory, string $twig, int $nav, array $payload = []): Response
    {
        $board = $this->requireBoard();
        $menuNav = $this->requireMenuNav();

        $vartwig = $menuNav->admin($board, $twig, Links::ADMIN, $nav);

        return $this->render($this->agentPrefix . 'ptn_office/home.html.twig', array_merge([
            'directory' => $directory,
            'replacejs' => false,
            'vartwig' => $vartwig,
            'board' => $board,
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
        ], $payload));
    }

    private function validateCsrf(string $id, ?string $token): void
    {
        if (!$this->isCsrfTokenValid($id, $token ?? '')) {
            throw new BadRequestHttpException('Jeton de sécurité invalide.');
        }
    }

    private function requireBoard(): Board
    {
        if (!$this->board instanceof Board) {
            throw $this->createNotFoundException('Aucun panneau sélectionné.');
        }

        return $this->board;
    }

    private function requireMenuNav(): MenuNavigator
    {
        if (!$this->menuNav instanceof MenuNavigator) {
            throw new RuntimeException('Le service MenuNavigator est indisponible.');
        }

        return $this->menuNav;
    }

}
