<?php

namespace App\Module;

use App\Entity\Media\Pict;
use App\Entity\Posts\Article;
use App\Entity\Ressources\Ressources;
use App\Lib\MsgAjax;
use App\Repository\CategoriesRepository;
use App\Repository\RessourcesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Ressourcecator
{
    private EntityManagerInterface $em;
    private RessourcesRepository $ressourcesRepository;
    private DateTime $now;
    private CategoriesRepository $categoriesRepository;


    public function __construct(EntityManagerInterface $entityManager, RessourcesRepository $ressourcesRepository, CategoriesRepository $categoriesRepository)
    {
        $this->em = $entityManager;
        $this->now= New DateTime();
        $this->ressourcesRepository = $ressourcesRepository;
        $this->categoriesRepository=$categoriesRepository;
    }

    public function createOrUpdateRessource(Ressources $ressource, array $articlesData): void
    {
        foreach ($articlesData as $index => $articleData) {
            $article = (!empty($articleData['id']) && is_numeric($articleData['id']))
                ? $this->em->getRepository(Article::class)->find($articleData['id'])
                : new Article();

            if ($articleData['delete'] == "1") {
                // Suppression de l'article
                if ($article->getId()) {
                    $this->em->remove($article);
                }
                continue; // Passe à l'article suivant
            }

            $article->setDatemodif($this->now);
            $article->setTitre($articleData['titre']);
            $article->setContenu($articleData['contenu']);
            $article->setRessource($ressource);

            // Gestion des médias via VichUploaderBundle
            if (isset($articleData['media']) && $articleData['media'] instanceof UploadedFile) {
                $article->setMediaFile($articleData['media']);
            }

            $this->em->persist($article);
        }

        $this->em->persist($ressource);
        $this->em->flush();
    }



    public function ManageRessourceAjax($result,$key): array|int|null
    {
        if($result['edit']){
            $rss=$this->ressourcesRepository->findForEdit($result['edit'])[0];
            if (!$rss) return MsgAjax::MSG_ERR1;

            $article=$rss->getHtmlcontent();

            if($article){
                $article->deleteFile();
                $article->setDatemodif($this->now);
            }else{
                $article= new Article();
                $article->setDatecreat($this->now);
                $article->setDatemodif($this->now);
                $rss->setHtmlcontent($article);
            }

            $npict=$rss->getPict();
            if($result['pict']!="false"){
                $this->editPict($npict,$result['pict'],$rss);
            }
            if($result['cat']!=""){
                $rss->setCategorie($this->categoriesRepository->find($result['cat']));
            }
        }else{


            $rss = new Ressources();
            $rss->setDeleted(false);
            $rss->setKeymodule($key);
            $rss->setTitre($result['titre']);
            $rss->setLabel(strip_tags($result['label'], null));
            $rss->setHtmltitre(strip_tags($result['htmltitre'], null));
            $rss->setComposition(strip_tags($result['compo'], null));
            $rss->setDescriptif(strip_tags($result['descriptif'], null));


            $this->createOrUpdateRessource($rss, $result['articles']);

            $article= new Article();
            $article->setDatecreat($this->now);
            $article->setDatemodif($this->now);
            $article->setDeleted(false);
            $rss->setHtmlcontent($article);
            $npict= new Pict();

            if($result['pict']!="false"){
                $this->createPict($npict,$result['pict'],$rss);
            }else{
                $rss->setPict($npict);
            }
            $rss->setCategorie($this->categoriesRepository->find($result['cat']));
        }

        $rss->setTitre($result['titre']);
        $rss->setLabel(strip_tags($result['label'], null));
        $rss->setHtmltitre(strip_tags($result['htmltitre'], null));
        $rss->setComposition(strip_tags($result['compo'], null));
        $rss->setDescriptif(strip_tags($result['descriptif'], null));

        if($result['contenthtml']!="")
        {
            $options=[
                'filesource'=>$result['contenthtml'],
                "tag"=>$result['titre'],
                "name"=>$result['titre']];
            $article->setFile($options);
            $data=$article->initNameFile();
            if($data){
                $article->uploadContent();
            }else{
                $article->deleteFile();
            }
        }
        $this->em->persist($article);
        $this->em->persist($rss);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }


    public function createPict($npict,$pict64, Ressources $ressource): bool
    {
            //list($type, $data) = explode(';', $pict64);
            list(, $data)      = explode(',', $pict64);
            $parts = base64_decode($data);
            $img = imagecreatefromstring($parts);
            if($img) {
                $uploadName = bin2hex(random_bytes(16));
                $namefile = $uploadName . '.' . 'jpg';
                imagejpeg($img, $npict->getUploadRootDir() . '/' . $namefile);
                $npict->setNamefile($namefile);
                $ressource->setPict($npict);
            }
        return true;
    }

    public function editPict($npict,$pict64, Ressources $ressource): array
    {
            if($npict->getNamefile()!= null){
                $npict->removeUpload();
            }
            $this->createPict($npict,$pict64,$ressource);
        return MsgAjax::MSG_POSTOK;
    }


    public function removeRessource(Ressources $ressource): array
    {
        $this->em->remove($ressource);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

}
