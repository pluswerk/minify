<?php

declare(strict_types=1);

namespace Pluswerk\PlusMinify\Middleware;

use Pluswerk\PlusMinify\Service\MinifyService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MinifyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //  minimize only html
        $response = $handler->handle($request);
        foreach ($response->getHeader('Content-Type') as $contentType) {
            if (!str_starts_with($contentType, 'text/html')) {
                return $response;
            }
        }

        if ($response instanceof Response) {
            $body = $response->getBody();
            $body->rewind();
            $html = $body->getContents();

            $minifyService = GeneralUtility::makeInstance(MinifyService::class);
            assert($minifyService instanceof MinifyService);
            $html = $minifyService->minify($html);
            $body = new Stream('php://temp', 'wb+');
            $body->write($html);
            $response = $response->withBody($body);
        }

        return $response;
    }
}
