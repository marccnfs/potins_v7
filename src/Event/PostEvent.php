<?php


namespace App\Event;

use App\Entity\Posts\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class PostEvent extends Event
{

    protected Post $post;
    private Response $response;

    public function __construct($post)
    {
        $this->post=$post;
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
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param $post
     */
    public function setPost($post): void
    {
        $this->post = $post;
    }
}
