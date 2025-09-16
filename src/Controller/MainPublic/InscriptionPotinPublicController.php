<?php

namespace App\Controller\MainPublic;

use App\Classe\PublicSession;
use App\Entity\Admin\PreOrderResa;
use App\Form\InscriptionPotinsPublicType;
use App\Lib\Links;
use App\Repository\PostEventRepository;
use App\Repository\ProductsRepository;
use App\Service\Modules\Resator;
use App\Service\Registration\CreatorUser;
use App\Service\Registration\Identificat;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionPotinPublicController extends AbstractController
{
    use PublicSession;

    #[Route('/potins/inscription/{id}/{timestamp}', name: 'resa_potins_public', defaults: ['timestamp' => 0], requirements: ['timestamp' => '\\d+'])]
    public function register(
        int $id,
        int $timestamp,
        Request $request,
        PostEventRepository $postEventRepository,
        Resator $resator,
        Identificat $identificat,
        CreatorUser $creatorUser,
        ProductsRepository $productsRepository
    ): Response {
        $event = $postEventRepository->findEventById($id);
        if (!$event) {
            return $this->redirectToRoute('board_all');
        }

        $availableDates = $resator->resapotin($event);
        if (!$availableDates) {
            $this->addFlash('warning', 'Aucune date n’est disponible pour ce potin.');

            return $this->redirectToRoute('show_event_id', ['id' => $event->getPotin()->getId()]);
        }

        $dateChoices = array_map(static fn(\DateTime $date): int => $date->getTimestamp(), array_values($availableDates));
        $selectedTimestamp = $timestamp > 0 ? $timestamp : reset($dateChoices);
        if (!in_array($selectedTimestamp, $dateChoices, true)) {
            $selectedTimestamp = reset($dateChoices);
        }

        $selectedDate = (new DateTime())->setTimestamp($selectedTimestamp);

        $preOrder = new PreOrderResa();
        $preOrder->setResaAt($selectedDate);
        $preOrder->setEvent($event);

        $form = $this->createForm(InscriptionPotinsPublicType::class, $preOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer = $identificat->creatorContactResa($form);
            $profil = $customer->getProfil();
            $profil->setFirstname($form['name']->getData());
            $profil->setTelephonemobile($form['telephone']->getData());
            $profil->setSex($form['sexe']->getData());
            $customer->setProfil($profil);
            $creatorUser->modifCustomer($customer);

            $preOrder->setCustomer($customer);
            $preOrder->setResaAt($selectedDate);

            $this->em->persist($preOrder);
            $this->em->flush();

            $product = $productsRepository->findOneProduct('resapotin');
            $redirectRoute = 'confirm_resa_public_potins';
            if ($product && $product->getPrice() > 0) {
                $redirectRoute = 'payment_resa_public_potins';
            }

            return $this->redirectToRoute($redirectRoute, ['id' => $preOrder->getId()]);
        }

        $product = $productsRepository->findOneProduct('resapotin');
        $vartwig = $this->menuNav->templatepotins(
            Links::EVENT,
            'inscription',
            1,
            'nocity'
        );

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'directory' => 'resa',
            'replacejs' => false,
            'vartwig' => $vartwig,
            'member' => $this->member,
            'customer' => $this->customer,
            'event' => $event,
            'selectedDate' => $selectedDate,
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/potins/inscription/confirmation/{id}', name: 'confirm_resa_public_potins')]
    public function confirm(PreOrderResa $preOrder, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->findOneProduct('resapotin');
        $vartwig = $this->menuNav->templatepotins(
            Links::EVENT,
            'confirmation',
            1,
            'nocity'
        );

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'directory' => 'resa',
            'replacejs' => false,
            'vartwig' => $vartwig,
            'member' => $this->member,
            'customer' => $this->customer,
            'preorder' => $preOrder,
            'event' => $preOrder->getEvent(),
            'product' => $product,
        ]);
    }

    #[Route('/potins/inscription/paiement/{id}', name: 'payment_resa_public_potins')]
    public function payment(PreOrderResa $preOrder, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->findOneProduct('resapotin');
        $price = $product ? $product->getPrice() : 0;
        $participants = max(1, (int) $preOrder->getNumberresa());
        $total = $price * $participants;

        $vartwig = $this->menuNav->templatepotins(
            Links::EVENT,
            'payment',
            1,
            'nocity'
        );

        return $this->render($this->useragentP . 'ptn_public/home.html.twig', [
            'directory' => 'resa',
            'replacejs' => false,
            'vartwig' => $vartwig,
            'member' => $this->member,
            'customer' => $this->customer,
            'preorder' => $preOrder,
            'event' => $preOrder->getEvent(),
            'product' => $product,
            'total' => $total,
            'participants' => $participants,
        ]);
    }
}
