<?php

namespace App\EventListener;

use App\Exeption\RedirectException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Listen the kernel.exception event in order to redirect to an url
 *
 * @author Grégory LEFER <contact@glefer.fr>
 */
class RedirectExceptionListener
{

    /**
     * Return a RedirectResponse if a RedirectException is thrown
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if (($exception = $event->getThrowable()) instanceof RedirectException) {
            $event->setResponse($exception->getResponse());
        }
    }

}