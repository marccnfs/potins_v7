<?php

namespace App\Service;


use App\Entity\Posts\Post;
use App\Entity\Boards\Board;


class OldMenuNavigator
{
	private array $vartwig=[];

    protected function initemplate($vartwig, $page,){
        $this->vartwig=$vartwig;
        $this->vartwig['entity']=false;
        $this->vartwig['linkbar'][0]="";
        $this->vartwig['linkbar'][1]="";
        $this->vartwig['linkbar'][2]="";
        $this->vartwig['linkbar'][3]="";
        $this->vartwig['linkbar'][$page]="active";
    }

    public function templatepotins($vartwig,$twigfile, $page, $city=null): array
    {
        $this->initemplate($vartwig,$page);
        $this->vartwig['title']="Les potins numeriques";
        $this->vartwig['menu'][]=['route'=>'contact_module_spwb','i'=>'fa fa-comments-o','name'=>'conversations'];
        $this->vartwig['titlepage']='Les potins numeriques';
        $this->vartwig['nav']=[];
        $this->vartwig['maintwig']=$twigfile;
        return $this->vartwig;
    }

    public function templatepotinsBoard($vartwig,$twigfile, $page, Board $website, $city=null): array
    {
        $this->initemplate($vartwig,$page);
        $this->vartwig['title']="Les potins numeriques";
        $this->vartwig['menu'][]=['route'=>'contact_module_spwb','i'=>'fa fa-comments-o','name'=>'conversations'];
        $this->vartwig['titlepage']='Les potins numeriques';
        $this->vartwig['nav']=[];
        // $this->vartwig['arround']=[];
        $this->vartwig['maintwig']=$twigfile;
        return $this->vartwig;
    }


    public function templateMember($vartwig,$twigfile,$page, $title, Board $website): array
    {
        $this->initemplate($vartwig,$page);
        $this->vartwig['tabActivities']=[];

        foreach ($website->getListmodules() as $module){
            $this->vartwig['tabActivities'][]=$module->getClassmodule();
        }
        $this->vartwig['maintwig']=$twigfile;
        $this->vartwig['arround']=[];
        $this->vartwig['title']=$title;
        $this->vartwig['titlepage']=$title;
        $this->vartwig['description']="gestion boardsite";
        $this->vartwig['tagueries'][]=["name"=> "backinfo boardsite"];
        return $this->vartwig;
    }


    // rest a voir ......

    public function templatinusager($twigfile,$nav): array
    {
        $this->vartwig['tabActivities']=[];
        $this->vartwig['maintwig']=$twigfile;
        $this->vartwig['arround']=[];
        $this->vartwig['title']="pas de titre";
        $this->vartwig['titlepage']="pas de titre";
        $this->vartwig['description']="gestion boardsite";
        $this->vartwig['tagueries'][]=["name"=> "backinfo boardsite"];
        $this->vartwig['m1']=false;
        $this->vartwig['m2']=false;
        $this->vartwig['m3']=false;
        $this->vartwig['m4']=false;
        $this->vartwig['m5']=false;
        $this->vartwig['m6']=false;
        $this->vartwig['m7']=false;
        $this->vartwig['m'.$nav]=true;

        $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'no'];
        $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'localisation','class'=>'no'];
        $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'no'];
        $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'no'];

        return $this->vartwig;
    }



    /**
     * @param $website Board
     * @param $twigfile
     * @param $page
     * @param $typeuser
     * @return array
     */
    public function websiteinfoObj(Board $website, $twigfile, $page, $typeuser): array
    {

            $this->vartwig['arround']=[];
            $this->vartwig['template']=$website->getTemplate();
            $this->vartwig['tagueries']=$website->getTemplate()->getTagueries(); //implode(",", $template->getKeyword());
            $this->vartwig['title']=$website->getNameboard();
            $this->vartwig['description']=$website->getTemplate()->getDescription();
            $this->vartwig['author']="mdj, https://affichange.com/msg/formcontact/affichange/Dc7zkgFhLpCoH5a2vbiNGUOZ";
            $this->vartwig['page']=$page;
         /*   $this->vartwig['scope']=[
                "@context"=> "https://schema.org",
                'type'=>$website->getTemplate()->getTagueries()[0]->getName(),
                'name'=>$website->getNamewebsite(),
                'description'=>$website->getTemplate()->getDescription(),
                'adress'=>[
                        "@type"=> "PostalAddress",
                      "streetAddress"=>"",
                      "addressLocality"=> $website->getLocality()->getCity(),
                      "addressRegion"=> "",
                      "postalCode"=> $website->getLocality()->getCode(),
                      "addressCountry"=> "FR"
                ],
                "geo"=>[
                    "@type"=> "GeoCoordinates",
                    "latitude"=> $website->getLocality()->getLatloc(),
                    "longitude"=> $website->getLocality()->getLonloc()
                ],
                "url"=> $website->getUrl()??"",
            ];
         */
            $this->vartwig['arround']=[];
            $this->vartwig['maintwig']=$twigfile;
            return $this->vartwig;
    }

    /**
     * @param $website Board
     * @param $twigfile
     * @param $page
     * @param $typeuser
     * @return array
     */
    public function postinfoObj(Post $post, Board $website, $twigfile, $page,$titre, $typeuser): array
    {
        $this->vartwig['arround']=[];
        $this->vartwig['template']=$website->getTemplate();
        $this->vartwig['tagueries']=$website->getTemplate()->getTagueries(); //implode(",", $template->getKeyword());
        $this->vartwig['title']=$titre;
        $this->vartwig['description']=$post->getSubject();
        $this->vartwig['author']=$website->getNameboard();
        $this->vartwig['page']=$page;

        $this->vartwig['linkbar'][0]="";
        $this->vartwig['linkbar'][1]="";
        $this->vartwig['linkbar'][2]="";
        $this->vartwig['linkbar'][3]="";
        $this->vartwig['linkbar'][$page]="active";

        /*   $this->vartwig['scope']=[
               "@context"=> "https://schema.org",
               'type'=>$website->getTemplate()->getTagueries()[0]->getName(),
               'name'=>$website->getNamewebsite(),
               'description'=>$website->getTemplate()->getDescription(),
               'adress'=>[
                       "@type"=> "PostalAddress",
                     "streetAddress"=>"",
                     "addressLocality"=> $website->getLocality()->getCity(),
                     "addressRegion"=> "",
                     "postalCode"=> $website->getLocality()->getCode(),
                     "addressCountry"=> "FR"
               ],
               "geo"=>[
                   "@type"=> "GeoCoordinates",
                   "latitude"=> $website->getLocality()->getLatloc(),
                   "longitude"=> $website->getLocality()->getLonloc()
               ],
               "url"=> $website->getUrl()??"",
           ];
        */
        $this->vartwig['maintwig']=$twigfile;
        return $this->vartwig;
    }

    /**
     * @param $website
     * @param $twigfile
     * @param $page
     * @param $typeuser
     * @return array
     */
    public function websiteinfo($website, $twigfile, $page, $typeuser): array //array
    {

            $this->vartwig['arround']=[];
            $this->vartwig['template']=$website['template'];
            $this->vartwig['tagueries']=$website['template']['tagueries']; //implode(",", $template->getKeyword());
            $this->vartwig['title']=$website['namewebsite'];
            $this->vartwig['description']=$website['template']['description'];
            $this->vartwig['scope']=[
                'type'=>'spaceWeb',
                'name'=>$this->vartwig['title'],
                'description'=>$this->vartwig['description']];
            return  $this->vartwig;

    }




    /**
     * @param $twigfile
     * @param $title
     * @param Board $website
     * @param $nav
     * @return mixed
     */
    public function newtemplatingspaceWeb($twigfile, $title, Board $website, $nav): mixed
    {
        $this->vartwig['tabActivities']=[];
        foreach ($website->getListmodules() as $module){
            $this->vartwig['tabActivities'][]=$module->getClassmodule();
        }
        $this->vartwig['maintwig']=$twigfile;
        $this->vartwig['arround']=[];
        $this->vartwig['title']=$title;
        $this->vartwig['titlepage']=$title;
        $this->vartwig['description']="gestion boardsite";
        $this->vartwig['tagueries'][]=["name"=> "backinfo boardsite"];
        $this->vartwig['m1']=false;
        $this->vartwig['m2']=false;
        $this->vartwig['m3']=false;
        $this->vartwig['m4']=false;
        $this->vartwig['m5']=false;
        $this->vartwig['m6']=false;
        $this->vartwig['m7']=false;
        $this->vartwig['m'.$nav]=true;
        return $this->vartwig;
    }

    /**
     * @param $twigfile
     * @param $title
     * @param Board $website
     * @return mixed
     */

    /**
     * @param $website Board
     * @param $twigfile
     * @param $title
     * @param $member
     * @return mixed
     */
    public function dispatchinfo(Board $website, $twigfile, $title, $member): mixed //objet
    {
        $this->vartwig['tabActivities']=[];
        foreach ($website->getListmodules() as $module){
            $this->vartwig['tabActivities'][]=$module->getClassmodule();
        }
        $this->vartwig['arround']=[];
        $this->vartwig['template']=$website->getTemplate();
        $this->vartwig['tagueries']=$this->vartwig['template']->getTagueries(); //implode(",", $template->getKeyword());
        $this->vartwig['title']=$title;
        $this->vartwig['titlepage']=$title;
        $this->vartwig['description']=$this->vartwig['template']->getDescription();
        $this->vartwig['scope']=[
            'type'=>'spaceWeb',
            'name'=>$this->vartwig['title'],
            'description'=>$this->vartwig['description']];
        return  $this->vartwig;
    }

    /**
     * @param $vartwig
     * @param $twigfile
     * @param $page
     * @param $nav
     * @return array
     */
    public function newtemplateControlCustomer($vartwig, $twigfile, $page, $nav): array
    {
        $this->vartwig=$vartwig;
        $this->vartwig['entity']=false;
        $this->vartwig['title']=$page;
        $this->vartwig['m1']=false;
        $this->vartwig['m2']=false;
        $this->vartwig['m3']=false;
        $this->vartwig['m4']=false;
        $this->vartwig['m5']=false;
        $this->vartwig['m6']=false;
        $this->vartwig['m7']=false;
        $this->vartwig['m'.$nav]=true;
        $this->vartwig['maintwig']=$twigfile;
        return $this->vartwig;
    }
}
