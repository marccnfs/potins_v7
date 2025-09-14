<?php


namespace App\Controller\Ajax;


use App\Lib\MsgAjax;
use App\Module\Reviewscator;
use App\Repository\GpReviewRepository;
use App\Repository\OffresRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class AjaxController extends AbstractController
{
    #[Route('/ajax/addpict/gpreview/{gp}/{id}', name:"add_ajax_pict_group_review", methods:"POST")]
    public function addPictToGpReview(Request $request, PostRepository $postRepository,Reviewscator $reviewscator, $gp, $id): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            if($request->request->get('post') == $id & $request->request->get('gpreview') ==$gp){
                if ($post=$postRepository->findPostAndGpById($id))
                {
                    $gpreview=$post->getGpreview();
                    $result['gpreview']=$gpreview;
                    $result['post']= $post;
                    $result['typefile']=$request->request->get('typefile');
                    switch ($result['typefile']){
                        case "64":
                            $result['file']=$request->request->get('file64');
                            break;
                        case "gif":
                            $result['file']=$request->request->get('gif');
                            break;
                        case "file":
                            $result['file']=$request->request->get('file');
                            break;
                    }
                    $issue=$reviewscator->AddJpg($result);
                    return new JsonResponse($issue);
                }else{
                    return new JsonResponse(['success' => "no find notice"]);
                }
            }else{
                return new JsonResponse(MsgAjax::MSG_ERRORRQ);
            }
        }
        return new JsonResponse(['success' => "no find notice"]);
    }

    #[Route('/ajax/addreview/add/{gp}/{id}', name:"add_ajax_review_group_review", methods:"POST")]
    public function addReviewAtGpreview(Request $request, GpReviewRepository $gpReviewRepository,Reviewscator $reviewscator, $gp, $id=null): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {

                if ($gpreview=$gpReviewRepository->findGpreviewsAll($gp))
                {

                    $result['gpreview']=$gpreview;
                    $result['post']= $gpreview->getPotin();
                    $result['type']=$request->request->get('type');
                    $result['titre']=$request->request->get('titre');
                    $result['soustitre']=$request->request->get('soustitre');
                    $result['fiche']=$request->request->get('fiche');
                    $result['idrw']= $request->request->get('idrw');
                    $result['pict']=$request->request->get('file64');
                    $issue=$reviewscator->ManageReviewAjax($result);
                    return new JsonResponse($issue);
                }else{
                    return new JsonResponse(['success' => "no find notice"]);
                }
        }
        return new JsonResponse(['success' => "no find notice"]);
    }


    #[Route('ajx/apifile/content-post/{id}', name:"ajx_content_post", methods:"GET")]
    public function loadContentPost(Request $request, PostRepository $postRepository, SerializerInterface $serializer, $id): JsonResponse
    {

                if ($post=$postRepository->find($id)) {
                    if ($post->getHtmlcontent()->getFileblob()) {
                        if(file_exists($post->getHtmlcontent()->getphpPathblob())){
                        $content = file_get_contents($post->getHtmlcontent()->getphpPathblob());
                        $contentjson = $serializer->serialize($content, 'json');
                        $responseCode = 200;
                        http_response_code($responseCode);
                        header('Content-Type: application/json');
                        return new JsonResponse(['success' => true, "notice" => $contentjson]);
                        }
                        return new JsonResponse(['error' => "no file exits"]);
                    }
                    return new JsonResponse(['success' => "no get fileblog"]);
                }
            return new JsonResponse(['success' => "no find notice"]);


    }


    #[Route('ajx/apifile/content-offre/{id}', name:"ajx_content_offre", methods:"GET")]
    public function loadContentOffre(Request $request, OffresRepository $offresRepository, SerializerInterface $serializer, $id): JsonResponse
    {

        if ($offre = $offresRepository->find($id)) {
            if ($offre->getProduct()->getHtmlcontent()->getFileblob()) {
                if(file_exists($offre->getProduct()->getHtmlcontent()->getphpPathblob())){
                    $content = file_get_contents($offre->getProduct()->getHtmlcontent()->getphpPathblob());
                    //$contentjson = $serializer->serialize($content, 'json');
                    $responseCode = 200;
                    http_response_code($responseCode);
                    header('Content-Type: Text/Html');
                    return new JsonResponse(['success' => true, "notice" => $content]);
                }
                return new JsonResponse(['error' => "no file exits"]);
            }
            return new JsonResponse(['error' => "no get fileblog"]);
        }
        return new JsonResponse(['error' => "no find notice"]);
    }
}