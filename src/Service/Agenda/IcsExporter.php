<?php

namespace App\Service\Agenda;

use App\Entity\Agenda\Event;

final class IcsExporter
{
    public function exportEvent(Event $e): string
    {
        $uid = $e->getSlug().'@potinsnumeriques.fr';
        $dtStart = $e->getStartsAt()->format('Ymd\THis\Z'); // UTC
        $dtEnd   = $e->getEndsAt()->format('Ymd\THis\Z');

        $summary = $this->escape($e->getTitle());
        $desc    = $this->escape((string)$e->getDescription());
        $loc     = $this->escape((string)$e->getLocationName());

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PotinsNumeriques//Agenda//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.gmdate('Ymd\THis\Z'),
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$summary,
            'DESCRIPTION:'.$desc,
            'LOCATION:'.$loc,
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    private function escape(string $s): string
    {
        // iCal escaping
        return str_replace([',',';','\\',"\n"], ['\,','\;','\\\\','\n'], $s);
    }
}
