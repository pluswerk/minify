<?php

namespace Pluswerk\PlusMinify\EventListener;

use AUS\SsiInclude\Event\RenderedEvent;
use Pluswerk\PlusMinify\Service\MinifyService;

class RenderedEventListener
{
    public function __construct(protected MinifyService $minifyService)
    {
    }

    public function __invoke(RenderedEvent $event): void
    {
        $html = $event->getHtml();
        $html = $this->minifyService->minify($html);

        $event->setHtml($html);
    }
}
