<?php

namespace App\Controller\Tools;

use App\Classe\potinsession;
use App\Entity\Customer\Customers;
use App\Repository\UserRepository;
use App\Repository\BoardRepository;
use App\Repository\ContactRepository;
use App\Repository\GpsRepository;
use App\Service\Registration\CreatorUser;
use App\Service\SpaceWeb\BoardlistFactor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/tools/jxrq')]


class ToolsController extends AbstractController
{
    use potinsession;

    #[Route('/test-visitor-mail', name:"test_mail_visitor")]
    public function tesEmailVisitor(Request $request, SerializerInterface $serializer, UserRepository $userRepository, ContactRepository $contactRepository): JsonResponse
    {

        $mail =  $request->request->get('email');
        $testuser=$userRepository->findOneBy(array('email'=> $mail));
        if($testuser!=null){
            $responseCode = 200;
            http_response_code($responseCode);
            header('Content-Type: application/json');
            return new JsonResponse(['ok' => true,'success' => "user",'email' => $mail]);
        }

        $contact = $contactRepository->findOneBy(array('emailCanonical' => $mail));
        if ($contact!=null) {
            $jasoncontact = $serializer->serialize($contact, 'json');
            $responseCode = 200;
            http_response_code($responseCode);
            header('Content-Type: application/json');
            return new JsonResponse(['ok' => true,'success' => "contact",'email' => $mail, 'contact' => $jasoncontact]);
        }
        return new JsonResponse(['ok' => true,'success' => "nomail", 'email' => $mail]);
    }


    #[Route('/testContactMail', name:"test_mail")]
    public function tesEmailContact(Request $request, SerializerInterface $serializer,UserRepository $userRepository, ContactRepository $contactRepository): JsonResponse
    {

        $data = json_decode((string) $request->getContent(), true);
        $mail=$data['email'];

        $testuser=$userRepository->findOneBy(array('email'=> $mail));
        if($testuser!=null){
            $responseCode = 200;
            http_response_code($responseCode);
            header('Content-Type: application/json');
            return new JsonResponse(['ok' => true,'success' => "user",'email' => $mail]);
        }

        $contact = $contactRepository->findOneBy(array('emailCanonical' => $mail));
        if ($contact!=null) {
            $jasoncontact = $serializer->serialize($contact, 'json');
            $responseCode = 200;
            http_response_code($responseCode);
            header('Content-Type: application/json');
            return new JsonResponse(['ok' => true,'success' => "contact",'email' => $mail, 'contact' => $jasoncontact]);
        }
        return new JsonResponse(['ok' => true,'success' => "nomail", 'email' => $mail]);
    }


    #[Route('/add-member-contactmail', name:"add_member_contactmail")]
    public function addMemberContact(Request         $request, BoardRepository $websiteRepository, ContactRepository $contactRepository,
                                     BoardlistFactor $factor, CreatorUser $creatorUser, SerializerInterface $serializer){
        if($request->isXmlHttpRequest())
        {
            $id=$request->request->get('id');
            if(!$id==null){
                $contact=$contactRepository->find($id);
            }
            $idwebsite=$request->request->get('idwebsite');

            if(!$idwebsite==null){
                $website=$websiteRepository->find($idwebsite);
                if($website){
                    $tabmember=[
                        "contact"=>$contact??null,
                        "type"=>true,
                        "website"=>$website,
                        "mail"=>$request->request->get('mail'),
                        "pass"=>$request->request->get('pass'),
                        "name"=>$request->request->get('name'),
                        "charte"=>$request->request->get('charte')];

                    //todo rajouter un token
                    if($typeregister=$request->request->get('typeregister')=="shop"){
                        /** @var Customers $customer */
                        $customer=$creatorUser->createUserByShop($tabmember);
                        if($customer){
                            $responseCode = 200;
                            http_response_code($responseCode);
                            header('Content-Type: application/json');
                            return new JsonResponse(['success' => true, 'id' => $customer->getId(),'name'=>$customer->getProfil()->getEmailfirst()]);
                        }else {
                            return new JsonResponse(['success' => false]);
                        }
                    }else{
                        /** @var DispatchSpaceWeb $dispatch */
                        $dispatch=$factor->addwebsiteclient($tabmember);
                        if ( $dispatch) {
                            $responseCode = 200;
                            http_response_code($responseCode);
                            header('Content-Type: application/json');
                            return new JsonResponse(['success' => true, 'id' => $dispatch->getId(),'name'=>$dispatch->getName()]);
                        }else {
                            return new JsonResponse(['success' => false]);
                        }
                    }
                }
            }
        }
        return new JsonResponse(['success'=>"erreur"]);
    }


    #[Route('/reinitslug', name:"reinitslug")]
    public function reinitSlug(GpsRepository $gpsrepo){
        if($this->isGranted("ROLE_SUPER_ADMIN")){
            $gps=$gpsrepo->findAll();
            foreach ($gps as $gp){
                $gp->setCity(strtolower($gp->getCity()));
                $this->em->persist($gp);
                $this->em->flush();
            }
        }
        return $this->redirectToRoute('cargo_public');
    }

    #[Route('/testAccess', name:"test_acces")]
    public function tesAccess(Request $request, SerializerInterface $serializer,UserRepository $userRepository, ContactRepository $contactRepository): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        $code=$data['code'];
        if($code=="0000"){
            $responseCode = 200;
            http_response_code($responseCode);
            header('Content-Type: application/json');
            return new JsonResponse(['ok' => true,'success' => "access"]);
        }
        return new JsonResponse(['ok' => true,'success' => "noaccess"]);
    }
}