<?php

namespace App\Tests\Controller\Agenda;

use App\Controller\MainPublic\AgendaController;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaControllerTest extends TestCase
{
    private function createController(): AgendaController
    {
        return new class extends \App\Controller\MainPublic\AgendaController {
            public function __construct() {}
            protected function render(string $view, array $parameters = [], Response $response = null): Response
            {
                if (isset($parameters['date']) && $parameters['date'] instanceof \DateTimeInterface) {
                    $parameters['date'] = $parameters['date']->format('Y-m-d');
                }
                return new Response(json_encode($parameters));
            }
        };
    }

    private function setPrivate(object $object, string $property, $value): void
    {
        $ref = new \ReflectionClass(\App\Controller\MainPublic\AgendaController::class);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function testIndexInvalidViewDefaultsToMonth(): void
    {
        $controller = $this->createController();
        $menuNav = new class {
            public function templatepotins(...$args)
            {
                return (object)['maintwig' => '_index'];
            }
        };
        $this->setPrivate($controller, 'menuNav', $menuNav);
        $this->setPrivate($controller, 'customer', null);

        $request = new Request(['view' => 'year']);
        $response = $controller->index($request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame('month', $data['view']);
    }

    public function testIndexInvalidDateDefaultsToToday(): void
    {
        $controller = $this->createController();
        $menuNav = new class {
            public function templatepotins(...$args)
            {
                return (object)['maintwig' => '_index'];
            }
        };
        $this->setPrivate($controller, 'menuNav', $menuNav);
        $this->setPrivate($controller, 'customer', null);

        $request = new Request(['date' => 'not-a-date']);
        $response = $controller->index($request);
        $data = json_decode($response->getContent(), true);

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $this->assertSame($today, $data['date']);
    }

    public function testFeedInvalidFromReturns400(): void
    {
        $controller = $this->createController();
        $em = $this->createMock(EntityManagerInterface::class);

        $request = new Request(['from' => 'invalid', 'to' => '2024-01-10']);
        $response = $controller->feed($request, $em);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testFeedInvalidToReturns400(): void
    {
        $controller = $this->createController();
        $em = $this->createMock(EntityManagerInterface::class);

        $request = new Request(['from' => '2024-01-10', 'to' => 'invalid']);
        $response = $controller->feed($request, $em);

        $this->assertSame(400, $response->getStatusCode());
    }
}
