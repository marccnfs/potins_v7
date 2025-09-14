<?php

namespace App\Notification;

use App\Repository\ActivMemberRepository;
use App\Repository\PrivateConversRepository;
use App\Repository\TbmsgsRepository;
use App\Service\Messages\Messageor;
use App\Service\Modules\Mailator;
use App\Service\SpaceWeb\BoardlistFactor;
use App\Util\Canonicalizer;
use Doctrine\ORM\EntityManagerInterface;

class Messageries
{
    private Messageor $messageor;
    private EntityManagerInterface $entityManager;
    private BoardlistFactor $factor;

    public function __construct(EntityManagerInterface $entityManager, Messageor $messageor, BoardlistFactor $factor)
    {
        $this->messageor=$messageor;
        $this->entityManager = $entityManager;
        $this->factor = $factor;
    }

    public function openMessageWebsite($form, $board){
        if($user and !$this->memberwb){
            if($idcontact=$form['follow']->getData()=="oui"){
                $this->factor->addSpwsiteClient($board, $this->dispatch);
            }
        }
        $this->messageor->newMessage([
            'form' => $form,
            'messagewb' => $messageWb,
            'website' => $website,
            'expe'=>$this->userdispatch??null
        ]);
        if($user){
            return $this->redirectToRoute('messagery_spwb', ['id' => $id]);
        }else{
            $this->addFlash('newmsg', ' votre message a bien été adressé à : '.$website->getNamewebsite().'.');
            return $this->redirectToRoute('contact_keep',['slug'=>$website->getSlug(),'type'=>$form['type']->getData(),'id'=>$form['id']->getData()]);
        }
    }

}