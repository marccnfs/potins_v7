<?php

namespace App\Service\Registration;

use App\Entity\Customer\Customers;
use App\Entity\Member\Activmember;
use App\Entity\Sector\Gps;
use App\Entity\Users\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class Sessioninit
{

    private RequestStack $requestStack;
    private UserRepository $repouser;
    private UploaderHelper $helper;

    public function __construct(RequestStack $requestStack,UploaderHelper $helper, RouterInterface $router, UserRepository $userRepository){
        $this->requestStack = $requestStack;
        $this->repouser = $userRepository;
        $this->helper=$helper;
    }

    /**
     * @var Activmember $dispatch
     */
    public function InitMember(Activmember $dispatch): void
    {
        $session=$this->requestStack->getSession();
        $session->set('iddisptachweb', $dispatch->getId());
        $session->set('namespaceweb', $dispatch->getName());
        $session->set('permission',$dispatch->getPermission());
    }


    public function initCustomer(User $user): array
    {
        $session=$this->requestStack->getSession();
        $customer=$this->repouser->findAllCustomByUserId($user->getId())->getCustomer();
        $session->set('idcustomer', $customer->getId());
        $session->set('typeuser', 'customer');
        $avatar=$customer->getProfil()->getAvatar();
        if($avatar) $session->set('avatar', $this->helper->asset($avatar, 'imageFile'));

        if($member=$customer->getMember()){
            $this->initSession($member,$session);
            return ['customer'=>$customer,'member'=>$member];
        }
        return ['customer'=>$customer,'member'=>null];
    }

    public function preinitCustomer($user): void
    {
        /** @var Customers $customer */
        $customer=$this->repouser->findAllCustomByUserId($user->getId())->getCustomer();
        $this->requestStack->getSession()->set('idcustomer', $customer->getId());
        $this->requestStack->getSession()->set('typeuser', 'customer');
        if($member=$customer->getMember()){
            $this->requestStack->getSession()->set('iddisptachweb', $member->getId());
        }
    }

    public function initSession(Activmember $member, $session): void
    {
        $session->set('iddisptachweb', $member->getId());
        $session->set('namespaceweb', $member->getName());
        $session->set('permission',$member->getPermission());
        if($loc=$member->getLocality()){
            $session->set('city', $loc->getCity());
            $session->set('idcity', $loc->getId());
            $session->set('lon', $loc->getLonloc());
            $session->set('lat', $loc->getLatloc());
        }
    }


    // reste en cours ....

    /**
     * @var Activmember $dispatch
     */
    public function chenageLoc(Activmember $dispatch){
        $loc=$dispatch->getLocality();
        $this->requestStack->getSession()->set('city', $loc->getCity());
        $this->requestStack->getSession()->set('lon', $loc->getLonloc());
        $this->requestStack->getSession()->set('lat', $loc->getLatloc());
    }

    /**
     * @var Activmember $dispatch
     */
    public function preInitSpaceWeb(Activmember $dispatch){
        $this->requestStack->getSession()->set('iddisptachweb', $dispatch->getId());
        $this->requestStack->getSession()->set('namespaceweb', $dispatch->getName());
        $this->requestStack->getSession()->set('permission',$dispatch->getPermission());
    }

    /**
     * @param $loc Gps
     */
    public function ipOrGpsPublicLoc($loc){
        $this->requestStack->getSession()->set('city', $loc->getCity());
        $this->requestStack->getSession()->set('lon', $loc->getLonloc());
        $this->requestStack->getSession()->set('lat', $loc->getLatloc());
    }
}