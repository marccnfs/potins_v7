<?php

namespace App\Controller\Member;

use App\Classe\potinsession;
use App\Entity\Customer\Customers;
use App\Entity\Users\User;
use App\Entity\Users\Contacts;
use App\Entity\Users\ProfilUser;
use App\Entity\Boards\Tbsuggest;
use App\Entity\Boards\Template;
use App\Entity\Boards\Board;
use App\Event\SuggestPartnerEvent;
use App\Lib\MsgAjax;
use App\Repository\ContactRepository;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use App\Service\Localisation\LocalisationServices;
use App\Service\SpaceWeb\BoardlistFactor;
use App\Util\Canonicalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;



#[IsGranted('ROLE_CUSTOMER')]
#[Route('/customer/suggest-boardpartner')]
class SuggestController extends AbstractController
{
    use potinsession;



    #[Route('/new-suggest', name:"new_suggest")]
    public function SuggestWebsSite(Request             $request, BoardlistFactor $factor, LocalisationServices $locate, EventDispatcherInterface $dispatcher,
                                    NormalizerInterface $normalizer, BoardRepository $websiteRepository, UserRepository $userRepository,
                                    ContactRepository   $contactRepository, Canonicalizer $emailCanonicalizer): JsonResponse
    {

        if($request->isXmlHttpRequest())
        {

            $data = json_decode((string) $request->getContent(), true);
            if($wb=$websiteRepository->findOneBy(['namewebsite'=>$data['name']])) return new JsonResponse(['success'=> false, 'wb'=>$normalizer->normalize($wb,null,['groups' => 'edit_event']) ]);
            if(!$this->selectedPwBoard($data['board']))return new JsonResponse(MsgAjax::MSG_ERRORRQ);

            $tabsuggets=new Tbsuggest();

            $partner=new Board();
            $template=new Template();
            $partner->setTemplate($template);
            $partner->setNameboard($data['name']);
            $partner->setAttached(false);
            if($data['ville']!=""){
                $gps = $locate->findLocate($data['ville']);
                if($gps==null) return new JsonResponse(['success'=> false ]);
                $partner->addLocality($gps);
            }
            $this->em->persist($partner);
            $this->em->flush();

            $tabsuggets->setPreboard($partner);
            $tabsuggets->setInvitor($this->board);
            $issue=$factor->addPartner($this->board, $partner);//todo

            $testuser=$userRepository->findOneBy(array('email'=> $data['email']));
            $contact = $contactRepository->findOneBy(array('emailCanonical' => $data['email']));

            if(!$testuser && !$contact){
                $to = new Contacts();
                $identity = new profilUser();
                $to->setValidatetop(true);
                $identity->setFirstname("");
                $identity->setLastname("");
                $identity->setEmailfirst($data['email']);
                $to->setEmailCanonical($emailCanonicalizer->canonicalize($data['email'])); //todo voir si necessaire de garder
                $to->setUseridentity($identity);
                $this->em->persist($to);
                $tabsuggets->setContact($to);
                $this->em->persist($tabsuggets);
                $this->em->flush();
                $event=New SuggestPartnerEvent($tabsuggets);
                $dispatcher->dispatch($event, SuggestPartnerEvent::NEWCONTACT);
            }elseif ($testuser){  // peu probable mais il faut traiter ce cas todo
                $to=$testuser->getCustomer()->getMember();
                $tabsuggets->setMember($to);
                $this->em->persist($tabsuggets);
                $this->em->flush();
                $event=New SuggestPartnerEvent($tabsuggets);
                $dispatcher->dispatch($event, SuggestPartnerEvent::DISPATCH);
            }elseif ($contact){  // iem peu probable mais il faut traiter ce cas  todo
                $to=$contact;
                $tabsuggets->setContact($to);
                $this->em->persist($tabsuggets);
                $this->em->flush();
                $event=New SuggestPartnerEvent($tabsuggets);
                $dispatcher->dispatch($event, SuggestPartnerEvent::CONTACT);
            }else{
                return new JsonResponse(['success'=> false ]);
            }
            /*  supprimer car remplacé par le Attached état
            $pw=New Spwsite();
            $pw->setDisptachwebsite($this->getDispatch());
            $pw->setRole("member");
            $pw->setIsadmin(false);
            $website->addSpwsite($pw);
            */

            return new JsonResponse(true);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('suggestBoardpartner/{id}', name:"suggest_boardpartner")]
    public function attacheSuggets(Request $request, UserRepository $userRepository, BoardlistFactor $spaceWebtor): Response //todo completement
    {
        /** @var User $user */
        $user=$userRepository->findCustomerAndProfilUser($this->security->getUser());
        /** @var Customers $customer */
        $customer=$user->getCustomer();

        $lat=$request->query->get('lat');
        $lon=$request->query->get('lon');

        if($lat && $lon) {
            $coord = ['lat' => $lat, 'lon' => $lon];
            if(!$user->getCharte()) {   //todo faire la validatioçn d'une vrai charte - actuellement mis a true directement a la creation
                $user->setCharte(true);
                $this->em->persist($user);
                $this->em->flush();
            }
            if($dispatch=$customer->getMember()){
                $spaceWebtor->confirmDispatch($dispatch,$coord);
            }else{
                $spaceWebtor->NewDispatch($customer,$coord);
            }

           if (!$this->requestStack->getSession()->has('idcustomer')) $this->sessioninit->initCustomer($user);// todo a savoir pourquoi on test cela ??
            return $this->redirectToRoute('intit_board_default');
        }else{
            return $this->redirectToRoute('confirmed');  // si pas de loc on retourne a la page de selection de ville
        }
    }

}
