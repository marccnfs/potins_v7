<?php

namespace App\Service\Repertories;



use App\Lib\MsgAjax;
use App\Lib\Tools;
use App\Repository\Entity\PostRepository;
use \DateTime;
use App\Entity\Module\Article;
use App\Entity\Media\Imagejpg;
use App\Entity\Media\Media;
use Doctrine\ORM\EntityManagerInterface;


class Bookator
{
    private $now;
    private $book;
    /** @var Media $media */
    private $media;
    private $titre;
    private $description;
    private $imagesource;
    private $em;
    private $dispatch;
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * Postar constructor.
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager, PostRepository $postRepository)
    {
        $this->em=$entityManager;
        $this->now= New DateTime();
        $this->postRepository = $postRepository;
    }

    protected function init($result){
        $this->dispatch=$result['dispatch'];
        $this->titre=Tools::cleaninput($result['titre']);
        $this->description=Tools::cleaninput($result['description']);
        $this->book=Tools::cleaninput($result['book']);
        $this->imagesource=$result['imagesource'];
        return true;
    }

    /**
     * @return array|int|null
     * @throws \Exception
     */
    public function newBook($dispatch, $book){
        if(!$this->init($result)) return MsgAjax::MSG_POST0;
        if($this->post){  // la post existe déja
            $this->post=$this->postRepository->find($this->post);
            if (!$this->post) return MsgAjax::MSG_ERR1;
            $this->post->setModifAt($this->now);
            $this->article=$this->post->getHtmlcontent();
            $this->article->removeUpload();
            $this->article->setDatemodif($this->now);
            $this->media=$this->post->getMedia();
        }else{
            $this->post = new Post();
            $this->post->setDeleted(false);
            $this->post->setAuthor($this->dispatch);
            $this->website->addPost($this->post);
            $this->article= new Article();
            $this->article->setDatecreat($this->now);
            $this->article->setDeleted(false);
            $this->media= New Media();
            $this->post->setMedia($this->media);
        }
        if(!$this->post) return MsgAjax::MSG_POST1;
        $this->post->setTitre($this->titre);
        $this->post->setSubject($this->description);

        if($this->contenthtml!="")
        {
            $options=[
                'filesource'=>$this->contenthtml,
                "tag"=>$this->titre,
                "name"=>$this->titre];
            $this->article->setFile($options);
        }
        $this->post->setHtmlcontent($this->article);

        if($this->imagesource!="false"){
            $etapefile = $this->AddFiles();
            if (!$etapefile) return MsgAjax::MSG_POST2;
        }
        $this->em->flush();
        return MsgAjax::MSG_POSTOK;
    }

    protected function AddFiles()
    {
        $options=['file'=>$this->imagesource,'filetyp'=>'64','name'=>'filereader']; //todo recupere le nom
        $images=$this->media->getImagejpg();
        if(count($images)>0){
            foreach ($images as $image){
                $this->media->removeImagejpg($image);  //todo pour l'instant je supprime toutes les images pour eviter des erreurs !!
            }
        }
        $this->createmediasJpg($options);
        return true;
    }

    protected function createmediasJpg($options){
        $imagejpg = new Imagejpg();
        $imagejpg->setFile($options);
        $this->media->addImagejpg($imagejpg);
        return true;
    }
}
