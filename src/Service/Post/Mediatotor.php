<?php

namespace App\Service\Post;


use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;



class Mediatotor
{
	private $security;
	public $manager;
	protected $test;

	public function __construct(Security $security, EntityManagerInterface $manager)
	{
		$this->security=$security;
		$this->manager=$manager;
		$this->test='gros boulet';
	}

	public function createmedias64($imagesource, $namefile, $media=null){

		$image64 = new Image64();
        $image64->setFile($imagesource, $namefile);     
 
        //$createimagejpg=(imagecreatefrompng($imagesource));
        //$imagejpg= new Imagejpg();
        //$imagejpg->setFile($createimagejpg, $namefile); 
        //imagedestroy($createimagejpg);    

        if($media){
			$tabmedia=$this->manager->getRepository('App:Media')->find($media);
		}else{
			$tabmedia= New Media();
		}

		$imagejpg->setIdmedia($tabmedia);
		$tabmedia->addImagejpg($imagejpg);
		//$tabmedia->addImagejpg($imagejpg);

		return $tabmedia;
	}
 

	public function createmediasJpg($imagesource, $namefile, $media=null){

		$imagejpg = new Imagejpg();
		$imagejpg->setFile($imagesource, $namefile);

        //$createimagejpg=(imagecreatefrompng($imagesource));
        //$imagejpg= new Imagesjpg();
        //$imagejpg->setFile($createimagejpg, $namefile); 
        //imagedestroy($createimagejpg);    

        if($media){
			$tabmedia=$this->manager->getRepository('App:Media')->find($media);	
		}else{
			$tabmedia= New Media();
		}

		$tabmedia->addImagejpg($imagejpg);
		$this->manager->persist($tabmedia);

		$this->manager->flush();

		return $tabmedia;
	}

}