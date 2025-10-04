<?php


namespace App\Controller\Mediatheques;

use App\Classe\UserSessionTrait;
use App\Lib\Links;
use App\Service\Search\ListEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MEDIA')]


class MediaOfficeController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/planning-media', name:"office_media")]
    public function officeMediaBoard(ListEvent $listEvent): Response
    {
        $tabdatesevents=$listEvent->listEventResa($this->board->getId());

        $vartwig=$this->menuNav->admin(
            $this->board,
            'media_office',
            links::ADMIN,
            1
        );

        $notices = [];

        return $this->render($this->useragentP.'ptn_media/home.html.twig', [
            'directory'=>"office",
            'replacejs'=>!empty($notices),
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'member'=>$this->member,
            'tabevents'=>$tabdatesevents,
        ]);
    }

}
