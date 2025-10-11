<?php

namespace App\Controller\MainPublic;

use App\Classe\UserSessionTrait;
use App\Entity\Agenda\Event;
use App\Entity\Users\Commentrdv;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Form\Agenda\AppointmentRequestType;
use App\Lib\Links;
use App\Repository\ContactRepository;
use App\Util\Canonicalizer;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class AppointmentController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/rdv/{slug}', name: 'agenda_request', methods: ['GET', 'POST'])]
    public function request(string $slug, Request $request, ContactRepository $contacts, Canonicalizer $canonicalizer): Response
    {
        /** @var Event|null $event */
        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || !$event->isPublished()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AppointmentRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (string) $form->get('email')->getData();
            $phone = (string) $form->get('phone')->getData();
            $message = (string) $form->get('message')->getData();

            $canonicalEmail = $canonicalizer->canonicalize($email);
            $contact = $contacts->findBymail($canonicalEmail);

            if (!$contact instanceof Contacts) {
                $contact = new Contacts();
                $contact->setEmailCanonical($canonicalEmail);
                $contact->setIpcontact($request->getClientIp());
                $contact->addLoginsource('AGENDA');

                $profile = new ProfilUser();
                $profile->setEmailsecours($email);
                $profile->setTelephonemobile($phone);
                $profile->setContact($contact);
                $contact->setUseridentity($profile);

                $this->em->persist($profile);
                $this->em->persist($contact);
            } else {
                $profile = $contact->getUseridentity();
                if (!$profile instanceof ProfilUser) {
                    $profile = new ProfilUser();
                    $profile->setContact($contact);
                }
                $profile->setEmailsecours($email);
                $profile->setTelephonemobile($phone);
                $contact->setUseridentity($profile);
                $contact->setDatemajAt(new \DateTime());
                $contact->addLoginsource('AGENDA');
                $this->em->persist($profile);
                $this->em->persist($contact);
            }

            $comment = new Commentrdv();
            $comment->setTitre($event->getTitle());
            $comment->setText($message !== '' ? $message : 'Demande de rendez-vous via l’agenda public.');
            $comment->setCity($event->getCommuneCode() ?: 'autre');
            $comment->setCreateAt(new DateTimeImmutable());
            $comment->setContact($contact);
            $contact->addComment($comment);

            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a bien été enregistrée. Nous vous recontactons rapidement.');

            return $this->redirectToRoute('agenda_request_confirmation', ['slug' => $event->getSlug()]);
        }

        $vartwig = $this->menuNav->templatePotins('request', Links::AGENDA);

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'replacejs' => false,
            'vartwig' => $vartwig,
            'directory' => 'agenda',
            'form' => $form->createView(),
            'event' => $event,
            'board' => $this->currentBoard(),
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
        ]);
    }

    #[Route('/rdv/{slug}/confirmation', name: 'agenda_request_confirmation', methods: ['GET'])]
    public function confirmation(string $slug): Response
    {
        /** @var Event|null $event */
        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => $slug]);
        if (!$event || !$event->isPublished()) {
            throw $this->createNotFoundException();
        }

        $vartwig = $this->menuNav->templatePotins('request_confirmation', Links::AGENDA);

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'replacejs' => false,
            'vartwig' => $vartwig,
            'directory' => 'agenda',
            'event' => $event,
            'board' => $this->currentBoard(),
            'member' => $this->currentMember,
            'customer' => $this->currentCustomer,
        ]);
    }
}

