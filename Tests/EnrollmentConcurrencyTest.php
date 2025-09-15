<?php

namespace App\Tests;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

#[ORM\Entity]
#[ORM\Table(name: 'events')]
class TestEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $capacity = null;

    public function __construct(private string $slug, ?int $capacity = null)
    {
        $this->capacity = $capacity;
    }

    public function getCapacity(): ?int { return $this->capacity; }
}

#[ORM\Entity]
#[ORM\Table(name: 'participants')]
class TestParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;
}

#[ORM\Entity]
#[ORM\Table(name: 'enrollments')]
class TestEnrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TestEvent::class)]
    public TestEvent $event;

    #[ORM\ManyToOne(targetEntity: TestParticipant::class)]
    public TestParticipant $participant;

    #[ORM\Column(length: 12)]
    public string $status;

    public function __construct(TestEvent $event, TestParticipant $p, string $status)
    {
        $this->event = $event;
        $this->participant = $p;
        $this->status = $status;
    }
}

final class EnrollmentConcurrencyTest extends TestCase
{
    private function createEntityManager(): EntityManager
    {
        $config = Setup::createAttributeMetadataConfiguration([__DIR__], true);
        $conn = ['driver' => 'pdo_sqlite', 'memory' => true];
        $em = EntityManager::create($conn, $config);
        $tool = new SchemaTool($em);
        $classes = $em->getMetadataFactory()->getAllMetadata();
        $tool->createSchema($classes);
        return $em;
    }

    public function testConcurrentCapacity(): void
    {
        $em = $this->createEntityManager();

        $event = new TestEvent('e', 1);
        $p1 = new TestParticipant();
        $p2 = new TestParticipant();
        $em->persist($event);
        $em->persist($p1);
        $em->persist($p2);
        $em->flush();

        // First enrollment
        $em->wrapInTransaction(function (EntityManager $em) use ($event, $p1) {
            $em->lock($event, LockMode::PESSIMISTIC_WRITE);
            $repo = $em->getRepository(TestEnrollment::class);
            $status = $repo->count(['event' => $event, 'status' => 'confirmed']) >= $event->getCapacity() ? 'waitlist' : 'confirmed';
            $em->persist(new TestEnrollment($event, $p1, $status));
        });

        // Second enrollment
        $em->wrapInTransaction(function (EntityManager $em) use ($event, $p2) {
            $em->lock($event, LockMode::PESSIMISTIC_WRITE);
            $repo = $em->getRepository(TestEnrollment::class);
            $status = $repo->count(['event' => $event, 'status' => 'confirmed']) >= $event->getCapacity() ? 'waitlist' : 'confirmed';
            $em->persist(new TestEnrollment($event, $p2, $status));
        });
        $em->flush();

        $repo = $em->getRepository(TestEnrollment::class);
        self::assertSame(1, $repo->count(['event' => $event, 'status' => 'confirmed']));
        self::assertSame(1, $repo->count(['event' => $event, 'status' => 'waitlist']));
    }
}
