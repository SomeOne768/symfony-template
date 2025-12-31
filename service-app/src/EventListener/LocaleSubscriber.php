<?php

// src/EventSubscriber/LocaleSubscriber.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // priorité : paramètre _locale > Accept-Language > default
        /* @phpstan-ignore-next-line */
        if ($locale = $request->query->get('_locale')) {
            $request->setLocale($locale);
        }
        /* @phpstan-ignore-next-line */
        elseif ($acceptLang = $request->headers->get('Accept-Language')) {
            // prend la première langue du header
            $request->setLocale(substr($acceptLang, 0, 2));
        } else {
            $request->setLocale($this->defaultLocale);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
