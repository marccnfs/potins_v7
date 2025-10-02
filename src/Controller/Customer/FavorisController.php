<?php


namespace App\Controller\Customer;

use App\Classe\UserSessionTraitOld;
use App\Repository\PostEventRepository;
use App\Repository\PostRepository;
use App\Repository\SuiviNotifRepository;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[IsGranted('ROLE_CUSTOMER')]

class FavorisController extends AbstractController
{
    use UserSessionTraitOld;


    #[Route('/favoris/all/board/{page?}', name:"favoris_board")]
    public function boardFavoris(SuiviNotifRepository $notifRepository,PostRepository $postRepository, PostEventRepository $postEventRepository, $page=null ): Response
    {
        if(!$this->dispatch) throw new Exception('dispatch inconnu');
        $this->activeBoard();
        //$msgsnoread=$messageor->messnoreadToList($this->userdispatch->getId());
        $notifs=$notifRepository->findBy([
            "member"=>$this->dispatch->getId()
        ]);
// todo faire un compteur pour faire une requete sur 10 entités
        foreach ($this->dispatch->getBulles() as $key => $catch){
            if($catch->getModulebubble()=='blog'){
                $this->catchs['blog'][]=['post'=>$postRepository->find($catch->getIdmodule()),'catch'=>$catch];
            }else{
                $this->catchs['event'][]=['event'=>$postEventRepository->find($catch->getIdmodule()),'catch'=>$catch];
            }
        }

        $vartwig=$this->menuNav->newtemplatingspaceWeb(
            'board',
            $this->board->getNamewebsite(),
            $this->board,
            1
        );

        return $this->render('aff_customer/home.html.twig', [
            'directory'=>"partners",
            'replacejs'=>true,
            'vartwig'=>$vartwig,
            'board'=>$this->board,
            'website'=>$this->board,
            'dispatch'=>$this->dispatch,
            'favoris'=>$this->catchs,
            'notifs'=>$notifs, //remplacera messnoread
           // 'partner'=>$this->board->getPartnergroup()->getWebsites(),
        ]);
    }


}
