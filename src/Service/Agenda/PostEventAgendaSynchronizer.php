<?php

namespace App\Service\Agenda;

use App\Entity\Agenda\Event;
use App\Entity\Module\PostEvent;
use App\Entity\Users\Participant;
use App\Enum\EventCategory;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Synchronise l'entité historique {@see PostEvent} avec le nouvel agenda public.
 *
 * Lorsqu'un "Potin" est créé ou modifié dans le back-office, cette classe
 * s'assure qu'un évènement {@see Event} correspondant existe (ou est mis à jour)
 * pour alimenter l'agenda CNFS.
 */
final class PostEventAgendaSynchronizer
{
    private const SOURCE_TYPE = 'post_event';
    private const DEFAULT_TZ = 'Europe/Paris';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventRepository $eventRepository,
        private readonly ParticipantRepository $participantRepository,
        #[Autowire('%agenda.cnfs_participant_code%')]
        private readonly string $cnfsParticipantCode,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Crée ou met à jour l'évènement d'agenda associé au {@see PostEvent} fourni.
     */
    public function syncFromPostEvent(PostEvent $postEvent): void
    {
        if (!$postEvent->getId()) {
            return; // l'entité n'est pas encore persistée
        }

        $appointment = $postEvent->getAppointment();
        if (!$appointment) {
            $this->logger->warning('Impossible de synchroniser un PostEvent sans rendez-vous.', [
                'postEvent' => $postEvent->getId(),
            ]);
            return;
        }

        $start = $appointment->getStarttime();
        $end = $appointment->getEndtime();
        if (!$start || !$end) {
            $this->logger->warning('Les dates du rendez-vous sont incomplètes, synchronisation agenda annulée.', [
                'postEvent' => $postEvent->getId(),
            ]);
            return;
        }

        $event = $this->findAgendaEvent($postEvent);
        $startsUtc = $this->toUtc($start);
        $endsUtc = $this->toUtc($end);

        if (!$event) {
            $organizer = $this->resolveOrganizer();
            if (!$organizer) {
                $this->logger->error('Aucun organisateur CNFS disponible pour alimenter l’agenda.');
                return;
            }

            $event = new Event(
                organizer: $organizer,
                title: (string) $postEvent->getTitre(),
                startsAtUtc: $startsUtc,
                endsAtUtc: $endsUtc,
                timezone: self::DEFAULT_TZ,
            );
            $event->setSourceType(self::SOURCE_TYPE);
            $event->setSourceId((string) $postEvent->getId());
            $this->em->persist($event);
        } else {
            $event->setTitle((string) $postEvent->getTitre());
            $event->setPeriod($startsUtc, $endsUtc);
            $event->setTimezone(self::DEFAULT_TZ);
        }

        $event->setDescription($postEvent->getDescription());
        $event->setAllDay(false);
        $event->setCategory(EventCategory::ATELIER);
        $event->setPublished((bool) $postEvent->getPublied());
        $event->setCapacity($this->resolveCapacity($postEvent));
        $event->setLocationName($this->resolveLocationName($postEvent));
        $event->setLocationAddress(null);
        $event->setCommuneCode($this->resolveCommune($postEvent));

        $this->em->flush();
    }

    /**
     * Supprime l'évènement d'agenda associé au {@see PostEvent}.
     */
    public function removeForPostEvent(PostEvent $postEvent): void
    {
        if (!$postEvent->getId()) {
            return;
        }

        $event = $this->findAgendaEvent($postEvent);
        if ($event) {
            $this->em->remove($event);
            $this->em->flush();
        }
    }

    private function findAgendaEvent(PostEvent $postEvent): ?Event
    {
        return $this->eventRepository->findOneBy([
            'sourceType' => self::SOURCE_TYPE,
            'sourceId' => (string) $postEvent->getId(),
        ]);
    }

    private function toUtc(DateTimeInterface $date): DateTimeImmutable
    {
        $immutable = $date instanceof DateTimeImmutable ? $date : DateTimeImmutable::createFromInterface($date);
        $withTz = $immutable->setTimezone(new DateTimeZone(self::DEFAULT_TZ));
        return $withTz->setTimezone(new DateTimeZone('UTC'));
    }

    private function resolveOrganizer(): ?Participant
    {
        $participant = $this->participantRepository->findOneBy(['codeSecret' => $this->cnfsParticipantCode]);
        if ($participant instanceof Participant) {
            return $participant;
        }

        $fallback = $this->participantRepository->findOneBy([], ['id' => 'ASC']);
        if ($fallback instanceof Participant) {
            $this->logger->warning('Participant CNFS introuvable, utilisation du premier participant en base.', [
                'expectedCode' => $this->cnfsParticipantCode,
                'fallbackId' => $fallback->getId(),
            ]);

            return $fallback;
        }

        $this->logger->error('Aucun participant disponible pour alimenter l’agenda.');

        return null;
    }

    private function resolveLocationName(PostEvent $postEvent): ?string
    {
        if ($postEvent->getLocatemedia()) {
            return $postEvent->getLocatemedia()->getNameboard();
        }

        $gps = $postEvent->getAppointment()?->getLocalisation();
        return $gps?->getNameloc();
    }

    private function resolveCommune(PostEvent $postEvent): string
    {
        $gps = $postEvent->getAppointment()?->getLocalisation();
        $slug = strtolower($gps?->getSlugcity() ?? $gps?->getCity() ?? '');

        return match (true) {
            str_contains($slug, 'pellerin') => 'pellerin',
            str_contains($slug, 'montagne') => 'montagne',
            str_contains($slug, 'boiseau') => 'sjb',
            default => 'autre',
        };
    }

    private function resolveCapacity(PostEvent $postEvent): ?int
    {
        if ($postEvent->getNumberPart() !== null) {
            return $postEvent->getNumberPart();
        }

        return $postEvent->getPotin()?->getNumberPart();
    }
}
