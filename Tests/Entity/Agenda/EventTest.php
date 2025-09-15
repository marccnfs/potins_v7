<?php

namespace App\Tests\Entity\Agenda;

use App\Entity\Agenda\Event;
use App\Entity\Users\Participant;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEndMustBeAfterStart(): void
    {
        $organizer = new Participant();
        $start = new \DateTimeImmutable('2024-01-01 10:00:00', new \DateTimeZone('UTC'));
        $end = new \DateTimeImmutable('2024-01-01 09:00:00', new \DateTimeZone('UTC'));

        $this->expectException(\InvalidArgumentException::class);
        new Event($organizer, 'Bad event', $start, $end);
    }

    public function testSetPeriodValidatesChronology(): void
    {
        $organizer = new Participant();
        $start = new \DateTimeImmutable('2024-01-01 08:00:00', new \DateTimeZone('UTC'));
        $end = new \DateTimeImmutable('2024-01-01 09:00:00', new \DateTimeZone('UTC'));
        $event = new Event($organizer, 'Good event', $start, $end);

        $this->expectException(\InvalidArgumentException::class);
        $event->setPeriod($start, $start->modify('-1 hour'));
    }
}
