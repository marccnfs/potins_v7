<?php


namespace App\Exeption;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * RedirectException is thrown when the user have to be redirect to a url outside of a controller.
 *
 * @author Grégory LEFER <contact@glefer.fr>
 */
class RedirectException extends \Exception
{
    /**
     * @var string target url where to redirect the user
     */
    private $url;
    /**
     * @var int Http code of the redirection (301, 302,..)
     */
    private $codeHttp;

    /**
     * RedirectException constructor ...
     * @param string $url
     * @param int $codeHttp
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $url, int $codeHttp = Response::HTTP_MOVED_PERMANENTLY,
        string $message = "", int $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->url = $url;
        $this->codeHttp = $codeHttp;
    }

    /**
     * Returns a RedirectResponse to the given URL ...
     */
    public function getResponse(): Response
    {
        return new RedirectResponse($this->url, $this->codeHttp);
    }

}