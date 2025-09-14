<?php


namespace App\Event;

use App\Entity\Member\Activmember;
use App\Entity\LogMessages\MsgsP;
use App\Entity\Marketplace\Offres;
use App\Entity\Posts\Post;
use App\Entity\Users\Contacts;
use App\Entity\Boards\Board;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class MessagePublicationEvent extends Event
{
    protected MsgsP $message;
    private Response $response;
    private Contacts|null $contact;
    private Activmember|null $dispatch;
    private Post|Offres $publication;
    private Board $board;
    private Activmember|Contacts|null $author;

    public function __construct($publication, Activmember|Contacts $sender, $message, $board, $author=null)
    {
        if ($sender instanceof Activmember){
            $this->dispatch=$sender;
        }else{
            $this->contact=$sender;
        }
        $this->message=$message;
        $this->publication=$publication;
        $this->board=$board;
        $this->author=$author;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @return Activmember|null
     */
    public function getDispatch(): ?Activmember
    {
        return $this->dispatch;
    }

    /**
     * @param $dispatch
     */
    public function setDispatch($dispatch): void
    {
        $this->dispatch = $dispatch;
    }

    /**
     * @return Contacts|null
     */
    public function getContact(): ?Contacts
    {
        return $this->contact;
    }

    /**
     * @param $contact
     */
    public function setContact($contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return Activmember|Contacts|null
     */
    public function getAuthor(): Activmember|Contacts|null
    {
        return $this->author;
    }

    /**
     * @param $author
     */
    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    /**
     * @return MsgsP
     */
    public function getMessage(): MsgsP
    {
        return $this->message;
    }

    /**
     * @param $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return Offres|Post
     */
    public function getPublication(): Offres|Post
    {
        return $this->publication;
    }

    /**
     * @param $publication
     */
    public function setPublication($publication): void
    {
        $this->publication = $publication;
    }

    /**
     * @return Board
     */
    public function getBoard(): Board
    {
        return $this->board;
    }

    /**
     * @param $board
     */
    public function setBoard($board): void
    {
        $this->board = $board;
    }

}
