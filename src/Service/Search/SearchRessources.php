<?php


namespace App\Service\Search;


use App\Entity\Module\GpRessources;
use App\Entity\Ressources\Ressources;
use App\Repository\GpRessourcesRepository;
use App\Repository\RessourcesRepository;

class SearchRessources
{

    private RessourcesRepository $ressourceRepository;
    private GpRessourcesRepository $gpRessourceRepo;



    public function __construct(RessourcesRepository $ressourceRepository, GpRessourcesRepository $gpRessourcesRepository)
    {
        $this->ressourceRepository=$ressourceRepository;
        $this->gpRessourceRepo=$gpRessourcesRepository;
    }

    public function findGroupeRessourcesOfPotins($id): array //todo refaire pour gpressource
    {
        $tabressources=[];

            $ressources=$this->ressourceRepository->findAllById($id);
            if(!empty($ressources)){
                foreach ($ressources as $ressource){
                  $tabressources[$ressource->getCategorie()->getName()][]=$ressource;
                }
                return $tabressources??[];
            }

        return $tabressources;
    }
    public function findGpRessource($id): GpRessources
    {
        return $this->gpRessourceRepo->findById($id);
    }

    public function findRessourcesOfPotins($gpid){
        return$this->ressourceRepository->findRessourcesByGpId($gpid);
    }

    public function findAllRessources(): array
    {
        $tabressources=[];

        $ressources=$this->ressourceRepository->findAllRss();
        if(!empty($ressources)){
            foreach ($ressources as $ressource){
                $tabressources[$ressource->getCategorie()->getName()][]=$ressource;
            }
            return $tabressources??[];
        }

        return $tabressources;
    }

    public function findAllCartes(): array
    {
        return $this->ressourceRepository->findAllRss();
    }

    public function searchOneRessource($id): ?Ressources
    {
        return $this->ressourceRepository->find($id);
    }


    public function searchOneRsscWithOtherRsscCat($id): bool|array
    {
        $rsscs=[];
        $rssc=$this->ressourceRepository->findForEdit($id)[0];

        if($rssc){
            $content="";
            $cat=$rssc->getCategorie();
            foreach ($rssc->getHtmlcontent() as $article){
                if ($article->getFileblob()) {
                    $content = file_get_contents($article->getphpPathblob());
                }
            }
            $rsscs=$this->ressourceRepository->findAllRsscCategoryWithOutRsscId($rssc->getId(), $cat->getId());
            return ['rsscs'=>$rsscs, 'rssc'=>$rssc,'content'=>$content];
        }else{
            return false;
        }
    }

}
