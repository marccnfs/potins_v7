<?php


namespace App\Service\Bulle;

use App\Entity\Bulles\Bullette;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class Bullatorette
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Resator constructor.
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function newBullette($param): Bullette
    {  //['form'=>$form,'spaceweb'=>$spaceweb,'module'=>$module]
        $bubblette= New Bullette();
        $bubblette->setContentHtml($param['content']);
        $bubblette->setBulle($param['bubble']);
        $bubblette->setSpacewebanswser($param['spaceWeb']);
        $bubblette->setBodyTxt("");
        $bubblette->setExpireAt((New DateTime())->modify("+ 8 days"));
        //$bubblette->setGrouped(false);
        $bubblette->setWarning(false);

        $this->entityManager->persist( $bubblette);
        $this->entityManager->flush();
        return  $bubblette;
    }



}