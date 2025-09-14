<?php


namespace App\Service\Search;


use App\Entity\Module\GpRessources;
use App\Entity\Ressources\Ressources;
use App\Repository\GpPresentsRepository;
use App\Repository\GpRessourcesRepository;
use App\Repository\OffresRepository;
use App\Repository\PresentsRepository;

class SearchPresent
{

    private PresentsRepository $presentRepository;
    private GpPresentsRepository $gppresentRepository;
    private  OffresRepository $offresRepository;




    public function __construct(PresentsRepository $presentRepository, GpPresentsRepository $gppresentsRepository, OffresRepository $offresRepository)
    {
        $this->presentRepository=$presentRepository;
        $this->gppresentRepository=$gppresentsRepository;
        $this->offresRepository=$offresRepository;
    }



    public function searchOneRsscWithOtherRsscCat($code): bool|array
    {
        $offre=$this->offresRepository->findOffreByCode($code)[0];

        $gpPresent=$offre->getGppresents();
        $tab=[];
            foreach ($gpPresent->getArticles() as $article) {
                if ($article != null) {
                    //  $present=$this->presentRepository->findForEdit($article->getId());

                    $content = "";
                    $cat = $article->getCategorie();
                    if ($article->getHtmlcontent()) {
                        if ($article->getHtmlcontent()->getFileblob()) {
                            $content = file_get_contents($article->getHtmlcontent()->getphpPathblob());
                        }
                    }
                    $tab[]=['art' => $article, 'content' => $content];
                }
            }

            return ['tab'=>$tab,'offre'=>$offre];

    }

    public function findGroupePresentsOfOffre($id): array //todo refaire pour gpressource
    {
        $tabpresents=[];

        $presents=$this->presentRepository>findAllByOffre($id);
        if(!empty($presents)){
            foreach ($presents as $present){
                $tabpresents[$present->getCategorie()->getName()][]=$present;
            }
            return $tabpresents??[];
        }

        return $tabpresents;
    }
    public function findGpRessource($id): GpRessources
    {
        return $this->gpRessourceRepo->find($id);
    }

    public function findAllRessources(): array
    {
        $tabressources=[];

        $ressources=$this->ressourceRepository->findAll();
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
        return $this->ressourceRepository->findAll();
    }

    public function searchOneRessource($id): ?Ressources
    {
        return $this->ressourceRepository->find($id);
    }


}
