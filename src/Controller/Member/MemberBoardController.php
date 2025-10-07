<?php

namespace App\Controller\Member;

use App\Classe\MemberSession;
use App\Entity\Member\Boardslist;
use App\Lib\Links;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBER')]
#[Route('/customer/account/board/')]
class MemberBoardController extends AbstractController
{
    use MemberSession;

    #[Route('preferences', name: 'choice_board', methods: ['GET'])]
    public function showPreferences(): Response
    {
        if ($redirect = $this->guardMemberContext()) {
            return $redirect;
        }

        $vartwig = $this->menuNav->newtemplateControlCustomer(
            Links::CUSTOMER_LIST,
            'preferences',
            'Mes préférences',
            4
        );

        return $this->render('aff_account/home.html.twig', [
            'directory' => 'profil',
            'replacejs' => null,
            'vartwig' => $vartwig,
            'board' => $this->board,
            'member' => $this->member,
            'city' => $this->member?->getLocality()?->getCity(),
        ]);
    }

    #[Route('list', name: 'list_board', methods: ['GET'])]
    public function listBoards(): Response
    {
        if ($redirect = $this->guardMemberContext()) {
            return $redirect;
        }

        $vartwig = $this->menuNav->newtemplateControlCustomer(
            Links::CUSTOMER_LIST,
            'choice',
            'Gérer mes panneaux',
            7
        );

        return $this->render('aff_account/home.html.twig', [
            'directory' => 'profil',
            'replacejs' => null,
            'vartwig' => $vartwig,
            'board' => $this->board,
            'member' => $this->member,
        ]);
    }

    #[Route('open/{board}', name: 'officeboard_member', methods: ['GET'])]
    public function openBoard(int $board): RedirectResponse
    {
        if ($redirect = $this->guardMemberContext()) {
            return $redirect;
        }

        $link = $this->findBoardLink($board);
        if (!$link || !$link->getBoard()) {
            $this->addFlash('warning', "Panneau introuvable.");
            return $this->redirectToRoute('list_board');
        }

        $this->setActiveBoardSession($link->getBoard());

        return $this->redirectToRoute('office_member');
    }

    #[Route('change/{id}', name: 'change_board', methods: ['GET'])]
    public function changeBoard(int $id): RedirectResponse
    {
        if ($redirect = $this->guardMemberContext()) {
            return $redirect;
        }

        $link = $this->findBoardLink($id);
        if (!$link || !$link->getBoard()) {
            $this->addFlash('warning', "Panneau introuvable.");
            return $this->redirectToRoute('list_board');
        }

        foreach ($this->member->getBoardslist() as $boardslist) {
            $isDefault = $boardslist === $link;
            $boardslist->setIsdefault($isDefault);
            $boardslist->setDatemajAt(new DateTime());
            $this->em->persist($boardslist);
        }
        $this->em->flush();

        $this->setActiveBoardSession($link->getBoard());

        return $this->redirectToRoute('officeboard_member', ['board' => $link->getBoard()->getId()]);
    }

    #[Route('add', name: 'add_board', methods: ['GET'])]
    public function addBoard(): Response
    {
        if ($redirect = $this->guardMemberContext()) {
            return $redirect;
        }

        $vartwig = $this->menuNav->newtemplateControlCustomer(
            Links::CUSTOMER_LIST,
            'addboard',
            'Créer un nouveau panneau',
            7
        );

        return $this->render('aff_account/home.html.twig', [
            'directory' => 'profil',
            'replacejs' => null,
            'vartwig' => $vartwig,
            'board' => $this->board,
            'member' => $this->member,
        ]);
    }

    private function guardMemberContext(): ?RedirectResponse
    {
        if (!$this->member) {
            return $this->redirectToRoute('cargo_public');
        }

        if (!$this->board) {
            foreach ($this->member->getBoardslist() as $boardslist) {
                if ($boardslist->isIsdefault() && $boardslist->getBoard()) {
                    $this->setActiveBoardSession($boardslist->getBoard());
                    return null;
                }

                if (!$this->board && $boardslist->getBoard()) {
                    $this->setActiveBoardSession($boardslist->getBoard());
                }
            }
        } else {
            $this->setActiveBoardSession($this->board);
        }

        return null;
    }

    private function setActiveBoardSession(?\App\Entity\Boards\Board $board): void
    {
        $this->board = $board;
        $session = $this->requestStack->getSession();
        if (!$session) {
            return;
        }

        if ($board && $board->getId()) {
            $session->set('idboard', $board->getId());
        } else {
            $session->remove('idboard');
        }
    }

    private function findBoardLink(int $boardId): ?Boardslist
    {
        foreach ($this->member->getBoardslist() as $boardslist) {
            $board = $boardslist->getBoard();
            if ($board && $board->getId() === $boardId) {
                return $boardslist;
            }
        }

        return null;
    }
}
