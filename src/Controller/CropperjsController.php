<?php

namespace App\Controller;


use App\Classe\PublicSession;
use App\Lib\Links;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;

class CropperjsController extends AbstractController
{
    use PublicSession;
    #[Route('/cropperjs', name: 'app_cropperjs')]
    public function __invoke(PostRepository $postRepository,CropperInterface $cropper, Request $request): Response
    {

        $croppedImage = null;
        $croppedThumbnail = null;
        $vartwig=$this->menuNav->templatepotins(
            Links::ACCUEIL,
            'cropperjs',
            0,
            "nocity");

        $post=$postRepository->findPstQ0(2);

        $crop = $cropper->createCrop( $post->getMedia()->getImagejpg()[0]->getUploadRootDir().'/'.$post->getMedia()->getImagejpg()[0]->getNamefile());
        //dump($crop);
        $crop->setCroppedMaxSize(1000, 750);

        $form = $this->createFormBuilder(['crop' => $crop])
            ->add('crop', CropperType::class, [
                'public_url' => $post->getMedia()->getImagejpg()[0]->getWebPath(),
                'cropper_options' => [
                    'aspectRatio' => 4 / 3,
                    'preview' => '#cropper-preview',
                    'scalable' => false,
                    'zoomable' => false,
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // faking an error to let the page re-render with the cropped images
            $form->addError(new FormError('🤩'));
            $croppedImage = sprintf('data:image/jpeg;base64,%s', base64_encode($crop->getCroppedImage()));
            $croppedThumbnail = sprintf('data:image/jpeg;base64,%s', base64_encode($crop->getCroppedThumbnail(200, 150)));
        }


        return $this->render( $this->useragentP.'ptn_public/home.html.twig', [
            'directory'=>'main',
            'replacejs'=>false,
            'customer'=>$this->customer,
            'vartwig'=>$vartwig,
            'form' => $form,
            'croppedImage' => $croppedImage,
            'croppedThumbnail' => $croppedThumbnail,
        ]);
    }
}
