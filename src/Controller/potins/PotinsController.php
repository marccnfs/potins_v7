<?php


namespace App\Controller\potins;

use App\Classe\UserSessionTrait;
use App\Entity\Module\TabpublicationMsgs;
use App\Entity\Posts\Post;
use App\Form\DeleteType;
use App\Lib\Links;
use App\Lib\MsgAjax;
use App\Module\Postator;
use App\Repository\ArticleRepository;
use App\Repository\BoardRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MEMBER')]
#[Route('/admin')]

class PotinsController extends AbstractController
{
    use UserSessionTrait;

    #[Route('/potins/manage/{id?}', name: 'potins_manage', methods: ['GET', 'POST'])]
    public function managePotins(
        Request $request,
        Postator $postator,
        BoardRepository $websiteRepository,
        PostRepository $postRepository,
        ?int $id = null
    ): Response {
        // Si un ID est fourni, on cherche la ressource, sinon on en crée une nouvelle
        if($id){
            $potin=$postRepository->find($id);
            if (!$potin) {
                throw $this->createNotFoundException("potin non trouvée.");
            }
            $potin->setModifAt(new \DateTime());
            $potin->setKeymodule($this->board->getCodesite());
        }else{
            $potin=new Post();
            $tabmsg=new TabpublicationMsgs();
            $potin->setTbmessages($tabmsg);
            $tabmsg->setPost($potin);
        }

        // Si le formulaire est soumis en POST
        if ($request->isMethod('POST')) {
            $potin->setTitre($request->request->get('titre'));
            $potin->setNumberPart($request->request->get('numberpart'));
            $potin->setAgePotin($request->request->get('agepotin'));
            $potin->setSubject(strip_tags($request->request->get('subject'), null));
            $potin->setKeymodule($this->board->getCodesite());
            $potin->setDeleted(false);
            $potin->setAuthor($this->member);


            // Gestion de l'upload de l'image
            $file = $request->files->get('uploadImage');
            if ($file instanceof UploadedFile) {
                $potin->setImageFile($file);
            }

            // Récupération et association des articles
            $articlesData = $request->request->all('articles');
            //dump($articlesData);
            $files = $request->files->all('articles');

            foreach ($files as $index => $file) {
                if (isset($articlesData[$index])) {
                    $articlesData[$index]['media'] = $file['media'] ?? null;
                }
            }

            // Traitement via le service
            $postator->createOrUpdatePotin($potin, $articlesData);

            return $this->redirectToRoute('office_member');
        }

        $vartwig=$this->menuNav->admin(
            $this->board,
            'manage_potin',
            links::ADMIN,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'potin'=>$potin,
            'vartwig'=>$vartwig,
            'is_edit' => $id !== null
        ]);

    }

    /**
     * Create news.
     */
    #[Route('/add-news-ajx', name:"add_news_ajx")]
    public function AddNewsAjx(Request $request, Postator $postator, BoardRepository $websiteRepository): JsonResponse
    {

        if($request->isXmlHttpRequest())
        {

    if(!$this->board= $websiteRepository->findWbQ3($request->request->get('slug'),$this->member->getId())) return new JsonResponse(MsgAjax::MSG_NOWB);
            $key=$this->board->getCodesite();
        //    dump($request->request->All());
            //$locate=$this->board->getLocality()[0];
            $result['titre']=$request->request->get('titre');
            $result['description']=$request->request->get('description');
            $result['numberpart']=$request->request->get('numberpart');
            $result['agepotin']=$request->request->get('agepotin');
            //$result['contenthtml']=$request->request->get('contenthtml');
            $result['post']= $request->request->get('post')!=="0"?$request->request->get('post'):false;
            $result['imagesource']=$request->request->get('file64');
            $issue=$postator->newAffiche($result, $this->member, $key, null);// toto j'ai retirer la locate a voir $locate
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('/add-article-potin_ajx', name:"add_article_potin_ajx")]
    public function AddArticlePotinAjx(Request $request, Postator $postator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $result['contenthtml']=$request->request->get('contenthtml');
            $result['post']= $request->request->get('post');
            $result['art']= $request->request->get('art');

        //    dump($result);
            //todo une erreur si pas de id du potin
            $issue=$postator->addArticle($result);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('/add-iframe-ajx', name:"add_iframe_potin_ajx")]
    public function AddIframePotinAjx(Request $request, Postator $postator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $result['iframevideo']=$request->request->get('iframevideo');
            $result['post']= $request->request->get('post');
            //todo une erreur si pas de id du potin
            $issue=$postator->addIframe($result);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }

    #[Route('/add-link-ajx', name:"add_link_potin_ajx")]
    public function AddLinkAjx(Request $request, Postator $postator): JsonResponse
    {
        if($request->isXmlHttpRequest())
        {
            $result['link']=$request->request->get('link');
            $result['post']= $request->request->get('post');
            //todo une erreur si pas de id du potin
            $issue=$postator->addLink($result);
            return new JsonResponse($issue);
        }else{
            return new JsonResponse(MsgAjax::MSG_ERRORRQ);
        }
    }


    #[Route('/newpost/', name:"new_generic_postation")]
    #[Route('/newpost/{board}', name:"new_postation")]
    public function newPost($board): Response
    {
        if($board!=$this->board->getId()) $this->redirectToRoute('list_board');

        $vartwig=$this->menuNav->admin(
            $board,
            'newpost',
            links::ADMIN,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>0,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }

    #[Route('/addarticle-potin/{id}', name:"add-article-potin")]
    public function addArticlePotin(PostRepository $postRepository, $id): Response
    {
        $post=$postRepository->findOnePostById($id);

        $vartwig=$this->menuNav->admin(
            $this->board,
            'addarticle',
            links::ADMIN,
            1
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>$post,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }

    #[Route('/addiframe-potin/{id}', name:"add-iframe-potin")]
    public function addIframePotin(PostRepository $postRepository, $id): Response
    {
        $post=$postRepository->find($id);
        //dump($post);
        $vartwig=$this->menuNav->templatingadmin(
            'addiframevideo',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>$post->getId(),
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }

    #[Route('/editpost/{id}', name:"edit_post")]
    public function editPost(PostRepository $postRepository, $id): RedirectResponse|Response
    {
        /** @var Post $post */
        if(!$post=$postRepository->findPstQ0($id))return $this->redirectToRoute('api-error',['err'=>2]); // sans controle de l'auteur pour acces superadmin
/*
        if($post->getHtmlcontent()->getFileblob() && file_exists($post->getHtmlcontent()->getWebPathblob())){
            $content=file_get_contents($post->getHtmlcontent()->getWebPathblob());
        }else{
            $content="";
        }
*/
        $vartwig=$this->menuNav->templatingadmin(
            'edit',
            "edition de l'affiche",
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'board'=>$this->board,
            'post'=>$post,
          //  'content'=>$content,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'admin'=>[true,$this->member->getPermission()],
            'locatecity'=>0,
       //     'back'=> $this->generateUrl('module_blog',['board' => $this->board->getSlug()]),
        ]);
    }

    #[Route('/addlink-potin/{id}', name:"add-link-potin")]
    public function addlinkPotin(PostRepository $postRepository, $id): Response
    {
        $post=$postRepository->findOnePostById($id);
        $vartwig=$this->menuNav->templatingadmin(
            'addlink',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'post'=>$post,
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }
    #[Route('/editlink/{id}', name:"edit_link_potin")]
    public function editlink(PostRepository $postRepository, $id): RedirectResponse|Response
    {
        /** @var Post $post */
        if(!$post=$postRepository->findPstQ0($id))return $this->redirectToRoute('api-error',['err'=>2]); // sans controle de l'auteur pour acces superadmin
        /*
                if($post->getHtmlcontent()->getFileblob() && file_exists($post->getHtmlcontent()->getWebPathblob())){
                    $content=file_get_contents($post->getHtmlcontent()->getWebPathblob());
                }else{
                    $content="";
                }
        */
        dump($post);
        $vartwig=$this->menuNav->templatingadmin(
            'addlink',
            "edition du lien",
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'board'=>$this->board,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'admin'=>[true,$this->member->getPermission()],
            'locatecity'=>0,
        ]);
    }

    #[Route('/editarticle/{id}', name:"edit_article_potin")]
    public function editArticlePost(ArticleRepository $articleRepository, $id): RedirectResponse|Response
    {
        $article=$articleRepository->findWithPostById($id);
        $post=$article->getPotin();

        if($article->getFileblob() && file_exists($article->getWebPathblob())){
            $content=file_get_contents($article->getWebPathblob());
        }else{
            $content="";
        }

        $vartwig=$this->menuNav->templatingadmin(
            'editarticle_potin',
            "edition de l'article",
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'board'=>$this->board,
            'post'=>$post,
            'art'=>$article,
            'content'=>$content,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
        ]);
    }

    #[Route('/deletearticle-potin/{id}', name:"delete-article-potin")]
    public function deleteArticlePotin(ArticleRepository $articleRepository,Request $request,Postator $postator, $id): Response
    {
        $article=$articleRepository->findWithPostById($id);
        $post=$article->getPotin();

        $form = $this->createForm(DeleteType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $issue=$postator->deleteArticle($article, $post);

            return $this->redirectToRoute('office_member');
        }
        $vartwig=$this->menuNav->templatingadmin(
            'deletearticle',
            $this->board->getNameboard(),
            $this->board,
            2
        );

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'board'=>$this->board,
            'article'=>$article,
            'form' => $form->createView(),
            'vartwig'=>$vartwig,
            'locatecity'=>0
        ]);
    }

    #[Route('/form-delete-article/{id}', name:"form-delete_article")]
    public function deleteArticle(Postator $postator,Request $request, ArticleRepository $articleRepository, $id): RedirectResponse|Response
    {
        $article=$articleRepository->findWithPostById($id);
        $post=$article->getPost();

        $form = $this->createForm(DeleteType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $issue=$postator->deleteArticle($article, $post);

            return $this->redirectToRoute('office_member');
        }
        $vartwig = $this->menuNav->templatingadmin(
            'deletearticle',
            'delete article',
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'form' => $form->createView(),
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig' => $vartwig,
            'author' => $post->getAuthor()->getId()==$this->member->getId(),
            'admin'=>[true,$this->member->getPermission()]
        ]);
    }

//todo refaire avec la suppression des articles, des media..etc
    #[Route('/form-delete-potin/{id}', name:"form-delete_post")]
    public function deletePost(Request $request, PostRepository $postRepository, $id): RedirectResponse|Response
    {
        /** @var Post $post */
        if(!$post=$postRepository->findPstQ0($id))return $this->redirectToRoute('api-error',['err'=>2]);
        $this->initBoardByKey($post->getKeymodule());
        $form = $this->createForm(DeleteType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setDeleted(true);
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('info', 'post supprimé.');
            return $this->redirectToRoute('module_blog', ['board'=>$this->board->getSlug()]);
        }
        $vartwig = $this->menuNav->templatingadmin(
            'deletepost',
            'delete post',
            $this->board,2);

        return $this->render($this->useragentP.'ptn_office/home.html.twig', [
            'directory'=>'potins',
            'replacejs'=>false,
            'form' => $form->createView(),
            'board' => $this->board,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'vartwig' => $vartwig,
            'author' => $post->getAuthor()->getId()==$this->member->getId(),
            'admin'=>[true,$this->member->getPermission()],
            'locatecity'=>0
        ]);
    }


    #[Route('/publied-post/{id}/{board}', name:"publied_post")]
    public function publiedPost(PostRepository $postRepository,Postator $postator, $id,$board): RedirectResponse|Response
    {
        /** @var Post $post */
        $post=$postRepository->find($id);
        $this->initBoardByKey($post->getKeymodule());
        $ret=$postator->publiedOnePost($post);// todo récuper le retour pour gerer les erreurs
        return $this->redirectToRoute('show_blog', ['id' => $this->board->getId()]);
    }

}
