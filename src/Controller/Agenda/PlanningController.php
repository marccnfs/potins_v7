<?php

namespace App\Controller\Agenda;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlanningController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/planning', name: 'agenda_board_week', methods: ['GET'])]
    public function boardWeek(Request $req): Response
    {
        $date = $req->query->get('date', (new \DateTimeImmutable('today'))->format('Y-m-d'));

        $vartwig=$this->menuNav->templatePotins('_board_week',Links::AGENDA);


        return $this->render('pwa/agenda/home.html.twig', [
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'directory'=>'agenda',
            'date' => new \DateTimeImmutable($date),
        ]);
    }
}
