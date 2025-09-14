<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Lib\Links;
use App\Service\ClientApiIaTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class AffichangePublicController extends AbstractController
{

 use PublicSession;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/noticezer', name:"noticezer")]
    public function listNotices(ClientApiIaTools $noticezer): Response
    {

        //$notices= $noticezer->twoLastnoticeclub();
        $notices= $noticezer->lastnoticeclub();

       $vartwig=$this->menuNav->templatepotins(
           Links::PUBLIC,
           'listnews',
           5,
           "");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'news',
            'replacejs'=>false,
            'notices'=>$notices??[],
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/show-news/{id}', name:"chow_news_api")]
    public function showNotices(ClientApiIaTools $noticezer, $id): Response
    {

        //$notices= $noticezer->twoLastnoticeclub();
        $notice= $noticezer->getNotice($id);

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'shownotice',
            5,
            "");

        return $this->render($this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'news',
            'replacejs'=>false,
            'notice'=>$notice??[],
            'vartwig'=>$vartwig,
            'member'=>$this->member,
            'customer'=>$this->customer,
        ]);
    }
}
