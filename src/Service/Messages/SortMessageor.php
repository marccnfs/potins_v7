<?php


namespace App\Service\Messages;


use App\Repository\Entity\WebsiteRepository;
use Doctrine\ORM\NonUniqueResultException;

class SortMessageor
{

    private WebsiteRepository $websiterepo;

    public function __construct(WebsiteRepository $websiteRepository){
        $this->websiterepo=$websiteRepository;
    }


    /**
     * @param $msgs
     * @param $dispatch
     * @param $tabcode
     * @return mixed
     */
    public function sortmesgTabAll($msgs, $dispatch,$tabcode): mixed
    {
        $top=false;
        $msgs->setSender(false);

        foreach ($msgs->getMsgs() as $m){  //pour chaque messagges
            foreach ($m->getTabreaders() as $read) {//1 - test sur read
                if($read->getIdispatch() == $dispatch->getId()){
                    if($read->getIsRead()){
                        $msgs->setSender($read->getIsRead());
                        $top=true;
                    }
                }
                if($top) break;
            }
            if($top) break;
        }
        $key=$msgs->getWebsitedest()->getCodesite();
        return [$msgs,in_array($key,$tabcode)];
    }

    /**
     * @param $msgs
     * @param $dispatch
     * @param $tabcode
     * @return array
     * @throws NonUniqueResultException
     */
    public function sortmesgTabAllPublication($msgs, $dispatch,$tabcode): array
    {
        $top=false;
        $msgs->setSender(false);

        foreach ($msgs->getMsgs() as $m){  //pour chaque messagges
            foreach ($m->getTabreaders() as $read) {//1 - test sur read
                if($read->getIdispatch() == $dispatch->getId()){
                    if($read->getIsRead()){
                        $msgs->setSender($read->getIsRead());
                        $top=true;
                    }
                }
                if($top) break;
            }
            if($top) break;
        }
        if($msgs->getTabpublication()->getPost()){
            $key=$msgs->getTabpublication()->getPost()->getKeymodule();
        }else{
            $key=$msgs->getTabpublication()->getOffre()->getKeymodule();
        }
        $website=$this->websiterepo->findWbByKey($key);
        return [$msgs, $website,in_array($key,$tabcode)];
    }

}