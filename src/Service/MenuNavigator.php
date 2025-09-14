<?php

namespace App\Service;


use App\Entity\Posts\Post;
use App\Entity\Boards\Board;


class MenuNavigator
{
    private array $vartwig;

    public function __construct(){
        $this->vartwig=[];
    }

    protected function initemplate($links,$twigfile,$page){
        $this->vartwig=$links;
        $this->vartwig['entity']=false;
        $this->vartwig['linkbar'][0]="";
        $this->vartwig['linkbar'][1]="";
        $this->vartwig['linkbar'][2]="";
        $this->vartwig['linkbar'][3]="";
        $this->vartwig['linkbar'][4]="";
        $this->vartwig['linkbar'][5]="";
        $this->vartwig['linkbar'][$page]="active";
        $this->vartwig['maintwig']=$twigfile;
    }

    public function templatepotins($links,$twigfile, $page, $city=null): array
    {
        $this->initemplate($links,$twigfile,$page);
        $this->vartwig['menu'][]=['route'=>'contact_module_spwb','i'=>'fa fa-comments-o','name'=>'conversations'];
        $this->vartwig['titlepage']='Les potins numeriques';
        $this->vartwig['nav']=[];
        return $this->vartwig;
    }

    public function templatepotinsBoard($links,$twigfile, $page, Board $website, $city=null): array
    {
        $this->initemplate($links,$twigfile,$page);
        $this->vartwig['menu'][]=['route'=>'contact_module_spwb','i'=>'fa fa-comments-o','name'=>'conversations'];
        $this->vartwig['titlepage']='Les potins numeriques';
        $this->vartwig['nav']=[];

        return $this->vartwig;
    }


    public function templateMember($twigfile,$page, Board $board): array
    {
        $this->initemplate([],$twigfile,$page);
        $this->vartwig['tabActivities']=[];
        foreach ($board->getListmodules() as $module){
            $this->vartwig['tabActivities'][]=$module->getClassmodule();
        }
        $this->vartwig['tagueries']=$board->getTemplate()->getTagueries(); //implode(",", $template->getKeyword());
        $this->vartwig['description']=$board->getTemplate()->getDescription();
        $this->vartwig['arround']=[];
        $this->vartwig['title']= $board->getNameboard();
        $this->vartwig['titlepage']=$board->getNameboard();
        return $this->vartwig;
    }

    public function templateCustomer($links,$twigfile, $page, $city=null): array
    {
        $this->initemplate($links,$twigfile,$page);
        $this->vartwig['menu'][]=['route'=>'contact_module_spwb','i'=>'fa fa-comments-o','name'=>'conversations'];
        $this->vartwig['titlepage']='Les potins numeriques';
        $this->vartwig['nav']=[];

        return $this->vartwig;
    }


    public function websiteinfoObj(Board $website, $twigfile, $page, $typeuser): array
    {
        $this->initemplate([],$twigfile,$page);
            $this->vartwig['arround']=[];
            $this->vartwig['template']=$website->getTemplate();
            $this->vartwig['tagueries']=$website->getTemplate()->getTagueries(); //implode(",", $template->getKeyword());
            $this->vartwig['title']=$website->getNameboard();
            $this->vartwig['description']=$website->getTemplate()->getDescription();
            $this->vartwig['author']="mdj, https://affichange.com/msg/formcontact/affichange/Dc7zkgFhLpCoH5a2vbiNGUOZ";
            return $this->vartwig;
    }


    public function postinfoObj(Post $post, Board $website, $twigfile, $page,$titre, $typeuser): array
    {
        $this->initemplate([],$twigfile,$page);
        $this->vartwig['arround']=[];
        $this->vartwig['template']=$website->getTemplate();
        $this->vartwig['tagueries']=$website->getTemplate()->getTagueries(); //implode(",", $template->getKeyword());
        $this->vartwig['title']=$titre;
        $this->vartwig['description']=$post->getSubject();
        $this->vartwig['author']=$website->getNameboard();
        $this->vartwig['page']=$page;

        return $this->vartwig;
    }

    public function templatingadmin($twigfile, $title, Board $website, $nav): array
    {
        $this->initemplate([],$twigfile,0);
        $this->vartwig['tabActivities']=[];
        foreach ($website->getListmodules() as $module){
            $this->vartwig['tabActivities'][]=$module->getClassmodule();
        }
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

        switch ($twigfile) {

            case 'openday':
                $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'navselect'];
                $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'Adresse','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'no'];
                break;

            case 'localizer':
                $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'localisation','class'=>'navselect'];
                $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'no'];
                break;

            case 'update':
                $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'localisation','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'navselect'];
                break;

            case 'stateModules':
            case 'modules/comonModule':
                $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'localisation','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'navselect'];
                $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'no'];
                break;

            default:
                $this->vartwig['menu2'][]=['route'=>'opendays_edit','i'=>'fa fa-clock-o','name'=>'horaires','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweblocalize_init','i'=>'fa fa-compass','name'=>'localisation','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'spaceweb_mod','i'=>'fa fa-suitcase','name'=>'modules','class'=>'no'];
                $this->vartwig['menu2'][]=['route'=>'website_edit','i'=>'fa fa-id-card-o','name'=>'infos','class'=>'no'];
                break;
        }

        return $this->vartwig;
    }


}
