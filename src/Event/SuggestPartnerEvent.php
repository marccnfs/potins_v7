<?php


namespace App\Event;

use App\Entity\Boards\Tbsuggest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class SuggestPartnerEvent extends Event
{
    public const DISPATCH = 'add-partner-create-by-dispatch';
    public const CONTACT = 'add-partner-create-by-contact';
    public const NEWCONTACT = 'add-partner-create-by-new-contact';


    private Response $response;
    private Tbsuggest $tabsuggest;

    public function __construct($tabsuggest)
    {
        $this->tabsuggest=$tabsuggest;

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
     * @return Tbsuggest|null
     */
    public function getTabsuggest(): ?Tbsuggest
    {
        return $this->tabsuggest;
    }

    /**
     * @param $tabsuggest
     */
    public function setTabsuggest($tabsuggest): void
    {
        $this->tabsuggest = $tabsuggest;
    }

}
