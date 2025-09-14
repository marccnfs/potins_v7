<?php


namespace App\Module;

use App\Entity\Agenda\Appointments;
use App\Entity\Module\GpRessources;
use App\Lib\MsgAjax;
use App\Repository\RessourcesRepository;
use App\Util\CalDateAppointement;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;


class GpRessourcator
{

    private EntityManagerInterface $em;
    private Evenator|CalDateAppointement $calldate;
    private RessourcesRepository $repoarticle;


    public function __construct(EntityManagerInterface $entityManager, CalDateAppointement $calldate, RessourcesRepository $articlesFormuleRepository)
    {
        $this->em = $entityManager;
        $this->calldate = $calldate;
        $this->repoarticle =$articlesFormuleRepository;
    }

    public function getlistarticles($gpressource): array
    {
        foreach ($gpressource->getArticles() as $artcat) {
            $tablistart[]=$artcat->getId();
        }
        return $tablistart??[];
    }

    public function editPotinRessources($form, GpRessources $gpRessources, $articles, $potin): array
    {
       // if(!$formule->getName()) $formule->setName('Menu du jour');
        if(!empty($articles)){
            foreach ($gpRessources->getArticles() as $oldarticle){
                $gpRessources->removeArticle($oldarticle);
            }
        }
        foreach ($articles as $artcat) {
          //  foreach ($artcat as $art){
                $adart=$this->repoarticle->find($artcat);
                if($adart){
                    $gpRessources->addArticle($adart);
                }
           // }
        }
        $this->em->persist($potin);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }


    /*   -------------------- todo reste a revoir --------------------------*/

    /**
     * @param $website
     * @param $key
     * @param $formule GpRessources
     * @return GpRessources
     */
    public function duplicateFormule($website, $key, GpRessources $formule): GpRessources
    {

        $newformule=clone $formule;
        if($newformule->getId()) {
            $newformule->setId(null);
        }
        $newformule->setPublied(false);
        if($formule->getName()) $newformule->setName($formule->getName().'- copie');
        $appointment = new Appointments();
        // on instancie la parution(appointements)
        $newformule->setParution($this->calldate->alongDaysNow($website,$appointment));
        $newformule->setCreateAt(new DateTime());

        $catformule=$formule->getCatformules();
        foreach ($catformule as $cat){
            $newcat= clone $cat;
            if( $newcat->getId()){
                $newcat->setId(null);
            }
            $newformule->addCatformule($newcat);
        }

        $articles = $formule->getArticles();
        foreach ($articles as $article){
            $newformule->addArticle($article);
        }
        $newformule->setKeymodule($key);
        $newformule->setServices($formule->getServices());

        $this->em->persist($newformule);
        $this->em->flush();
        return $newformule;
    }

    /**
     * @param $formule GpRessources
     * @return array
     */
    public function removeFormule(GpRessources $formule): array
    {
        foreach ($formule->getCatformules() as $cat) {
            $this->em->remove($cat);
        }
        $this->em->remove($formule);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    /**
     * @param $formule GpRessources
     * @return array
     */
    public function publiedFormule(GpRessources $formule): array
    {
        $formule->setPublied(!$formule->getPublied());
        $this->em->persist($formule);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }
}

// $appointment=$formule->getParution();
// $formule->setParution($this->calldate->alongDaysFormule($website,$form, $appointment));

/*

foreach ($form->get('articles')->getData() as $key => $art){
    if($basedata=$articles[$key]['base64']){
        list($type, $data) = explode(';', $basedata);
        list(, $data)      = explode(',', $basedata);
        $parts = base64_decode($data);
        $img = imagecreatefromstring($parts);
        if(!$img) die(); // todo faire mieux
        $uploadName = sha1(uniqid(mt_rand(), true));
        $namefile = $uploadName . '.' . 'jpg';

        if($pict=$art->getPict()){
            if($art->getPict()->getNamefile()!=""){
                $this->upload->removeUpload($art->getPict());
            }
        }else{
            $pict=new Pict();
            $art->setPict($pict);
        }
        imagejpeg($img,$pict->getUploadRootDir() . '/' . $namefile);
        $pict->setNamefile($namefile);
        $this->em->persist($art);
    }
}
*/