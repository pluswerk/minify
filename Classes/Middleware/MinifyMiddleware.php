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

class MinifyMiddleware implements MiddlewareInterface
{
    protected MinifyService $minifyService;
    public function __construct(MinifyService $minifyService)
    {
        $this->minifyService = $minifyService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //  minimize only html
        $response = $handler->handle($request);
        foreach ($response->getHeader('Content-Type') as $contentType) {
            if (strpos($contentType, 'text/html') !== 0) {
                return $response;
            }
        }

        if ($response instanceof Response) {
            $body = $response->getBody();
            $body->rewind();
            $html = $body->getContents();
            $html = $this->minifyService->minify($html);
            $body = new Stream('php://temp', 'wb+');
            $body->write($html);
            $response = $response->withBody($body);
        }
        return $response;
    }

}
