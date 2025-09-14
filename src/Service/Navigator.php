<?php


namespace App\Service;



class Navigator
{

    private array $vartwig;

    public function __construct(){
      $this->vartwig=[];
    }

    public function dispatchinfo($twigfile): array
    {
        switch ($twigfile){
            case 'home':
                $this->vartwig['title']="les potins numeriques";
                $this->vartwig['titlepage']="les potins numeriques";
                $this->vartwig['description']="";
                $this->vartwig['tagueries']=['conseiller numerique', 'potins numerique', 'ateliers numeriques','initiation informatique'];
                $this->vartwig['scope']=[
                    'type'=>'spaceWeb',
                    'name'=>"les potins numeriques",
                    'description'=>"ateliers & initiation au numérique dans les médiathèques de Saint Jean de Boiseau, le Pellerin et la Montagne avec votre conseiller numérique France Services"];
                break;

            case 'projets':
                $this->vartwig['title']="les potins numériques - Projets";
                $this->vartwig['titlepage']="les potins numériques - Projets";
                $this->vartwig['description']="les potins numériques - Projets";
                $this->vartwig['tagueries']=['les potins numériques - Projets'];
                $this->vartwig['scope']=[
                    'type'=>'spaceWeb',
                    'name'=>"les potins numériques - Projets",
                    'description'=>"ateliers & initiation au numérique dans les médiathèques de Saint Jean de Boiseau, le Pellerin et la Montagne avec votre conseiller numérique France Services"];
                break;

            default:
                $this->vartwig['title']="les potins numériques";
                $this->vartwig['titlepage']="les potins numériques";
                $this->vartwig['description']="les ptins numériques";
                $this->vartwig['tagueries']=['les ptins numériques'];
                $this->vartwig['scope']=[
                    'type'=>'spaceWeb',
                    'name'=>"les ptins numériques",
                    'description'=>"ateliers & initiation au numérique dans les médiathèques de Saint Jean de Boiseau, le Pellerin et la Montagne avec votre conseiller numérique France Services"];
                break;
        }
        return  $this->vartwig;
    }
}