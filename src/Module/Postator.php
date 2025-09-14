<?php

namespace App\Module;


use App\Entity\Member\Activmember;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\Posts\Post;
use App\Lib\MsgAjax;
use App\Repository\ArticleRepository;
use App\Repository\PostRepository;
use \DateTime;
use App\Entity\Posts\Article;
use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

;

class Postator
{
    private PostRepository $postRepository;
    private EntityManagerInterface $em;
    private DateTime $now;
    private ArticleRepository $artRepositroy;

    /**
     * Postar constructor.
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param ArticleRepository $articleRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PostRepository $postRepository, ArticleRepository $articleRepository)
    {
        $this->em=$entityManager;
        $this->now= New DateTime();
        $this->postRepository = $postRepository;
        $this->artRepositroy=$articleRepository;
    }

    public function createOrUpdatePotin(Post $potin, array $articlesData): void
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
            $article->setPotin($potin);

            // Gestion des médias via VichUploaderBundle
            if (isset($articleData['media']) && $articleData['media'] instanceof UploadedFile) {
                $article->setMediaFile($articleData['media']);
            }

            $this->em->persist($article);
        }

        $this->em->persist($potin);
        $this->em->flush();
    }


    public function addArticle($result): array|int|null
    {
        if(!$affiche=$this->postRepository->find($result['post'])) return MsgAjax::MSG_ERR1;
        if($result['art']!="undefined"){
            $article=$this->artRepositroy->find($result['art']);
            $titreart=$article->getNamearticle();
        }else{
            $nbarticle=count($affiche->getHtmlcontent());
            $titreart="artcile N°".$nbarticle++;
            $affiche->setModifAt($this->now);
            $article= new Article();
            $article->setNamearticle($titreart);
            $article->setDatecreat($this->now);
            $article->setDeleted(false);
            $affiche->addHtmlcontent($article);
        }
        if($result['contenthtml']!="")
        {
            $options=[
                'filesource'=>$result['contenthtml'],
                "tag"=>$titreart,
                "name"=>$titreart];
            $article->setFile($options);
            $data=$article->initNameFile();
            if($data){
                $article->uploadContent();
            }else{
                $article->deleteFile();
            }
        }
        $this->em->persist($affiche);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function addIframe($result): array|int|null
    {
        if(!$affiche=$this->postRepository->find($result['post'])) return MsgAjax::MSG_ERR1;
        $affiche->setModifAt($this->now);
        $affiche->setIframevideo($result['iframevideo']);
        $this->em->persist($affiche);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function addLink($result): array|int|null
    {
        if(!$affiche=$this->postRepository->find($result['post'])) return MsgAjax::MSG_ERR1;
        $affiche->setModifAt($this->now);
        $affiche->setLink($result['link']);
        $this->em->persist($affiche);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    public function deleteArticle($article, Post $post): array
    {
        $post->setModifAt($this->now);
        $post->removeHtmlcontent($article);
        $this->em->persist($post);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    /**
     * @param $result
     * @param Activmember $author
     * @param $key
     * @param $locate
     * @param $website
     * @return array|int|null
     */
    public function newAffiche($result, Activmember $author, $key, $locate=null): array|int|null
    {
        if($result['post']){
            $affiche=$this->postRepository->find($result['post']);
            if (!$affiche) return MsgAjax::MSG_ERR1;
            $affiche->setModifAt($this->now);
            $affiche->setKeymodule($key);
            //$article=$affiche->getHtmlcontent();
            //$article->deleteFile();
            $media=$affiche->getMedia();
        }else{
            $affiche = new Post();
            $affiche->setDeleted(false);
            $affiche->setKeymodule($key);
            $affiche->setAuthor($author);
            //$affiche->setLocalisation($locate);
            /*$article= new Article();
            $article->setDatecreat($this->now);
            $article->setDeleted(false);*/
            //$affiche->setHtmlcontent($article);
            $media= New Media();
            $affiche->setMedia($media);
            $tabmsg=new TabpublicationMsgs();
            $affiche->setTbmessages($tabmsg);
            $tabmsg->setPost($affiche);
        }
        $affiche->setTitre($result['titre']);
        $affiche->setNumberPart($result['numberpart']);
        $affiche->setAgePotin($result['agepotin']);
        $affiche->setSubject(strip_tags($result['description'], null));
        /*if($result['contenthtml']!="")
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
        }*/

        if($result['imagesource']!="false"){
            $etapefile = $this->AddFiles($result['imagesource'], $media);
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }
        $this->em->persist($affiche);
        $this->em->flush();
     //   $event = new PostEvent($affiche);
       // $this->eventdispatcher->dispatch($event, AffiEvents::NOTIFICATION_NEW_POST);
        return MsgAjax::MSG_POSTOK;
    }

    protected function AddFiles($imagesource, $media): bool
    {
        $options=['file'=>$imagesource,'filetyp'=>'64','name'=>'filereader']; //todo recupere le nom
        $images=$media->getImagejpg();
        if(count($images)>0){
            foreach ($images as $image){
                $media->removeImagejpg($image);  //todo pour l'instant je supprime toutes les images pour eviter des erreurs !!
            }
        }
        $this->createmediasJpg($options, $media);
        return true;
    }

    protected function createmediasJpg($options, $media): bool
    {
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $media->addImagejpg($imagejpg);
        return true;
    }

    /**
     * @param $idpost
     * @param $posts
     * @return array
     */
    public function publiedPost($idpost, $posts): array
    {
        foreach ($posts as $el){
            if($el->getId() == $idpost){
                if($el->getPublied()){
                    $el->setPublied(false);
                }else{
                    $el->setPublied(true);
                }
            } else{
                $el->setPublied(false);
            }
            $this->em->persist($el);
        }
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
        
    }

    /**
     * @param Post $post
     * @return array|null
     */
    public function publiedOnePost(Post $post): ?array
    {
        $post->setPublied(!$post->isPublied());
        $this->em->persist($post);
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }
}
