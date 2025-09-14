<?php

namespace App\Service\Post;

use DateTime;


class SmsTor{

	private $now;
	private $match;
	private $contenu;
	private $entry;
	private $comment;
	private $manager;
	private $prematch;


	public function __construct($result, $manager)
	{

		$this->now= New DateTime();
		$this->manager=$manager;
		$this->entry=$this->manager->getRepository('App:MatchEntry')->find($result['entry']);
		$this->match=$result['idmatch'];
		$this->contenu=$result['contenu'];
		
        $prematch=$this->manager->getRepository('App:PrematchComment')->find($this->match);
            if (!$prematch) {
                	$success=false;
                	$msg = 'la news est introuvable';
	                $message=array(
	                    'success' => $success,
	                    'msg' => $msg );
	                return $message;
            }else{
               $this->prematch=$prematch;
            }

        $this->comment= New PreMatchExchanges();
		$this->comment->setIdPreMatchComment($this->prematch);
		$this->comment->setDateCreate($this->now);	
	    $this->comment->setIdEntry($this->entry);
	    $this->comment->setContent($this->contenu);
	    $this->comment->setDeleted(false);
	    $this->prematch->addIdPreMatchExange($this->comment);

 		$this->manager->persist($this->prematch);
        $this->manager->persist($this->comment);
        $this->manager->flush();  

        $success=true;

        $message=array(
            'success' => $success,
            'msg' => 'news et comment enregistrés' );
        return $message;
	}


	
	
}
			