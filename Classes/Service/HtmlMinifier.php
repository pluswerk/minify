<?php

declare(strict_types=1);

/***
 *
 * This file is part of an "Pluswerk AG" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 3/1/17 2:59 PM Stefan Lamm <stefan.lamm@pluswerk.ag>, Pluswerk AG
 *
 ***/


namespace Pluswerk\PlusMinify\Hook;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use voku\helper\HtmlMin;

/**
 * @author Stefan Lamm <stefan.lamm@pluswerk.ag>
 * @copyright 2019 Pluswerk AG
 * @license GPL, version 3
 * @package Pluswerk\PlusMinify\Hook
 */
class HtmlMinifier implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var \TYPO3\CMS\Core\Http\Response $response */
        //  minimize only html
        $response = $handler->handle($request);
        foreach ($response->getHeader('Content-Type') as $contentType) {
            if (strpos($contentType, 'text/html') !== 0) {
                return $response;
            }
        }

        if ($response instanceof Response
            && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController
            && $GLOBALS['TSFE']->isOutputting()) {

            $html = &$GLOBALS['TSFE']->content;
            $htmlMin = new HtmlMin();
            $htmlMin->doOptimizeViaHtmlDomParser($this->isFeatureActive('optimize_via_html_dom_parser'));
            $htmlMin->doSumUpWhitespace($this->isFeatureActive('sum_up_whitespace'));
            $htmlMin->doRemoveWhitespaceAroundTags($this->isFeatureActive('remove_whitespace_around_tags'));
            $htmlMin->doOptimizeAttributes($this->isFeatureActive('optimize_attributes'));
            $htmlMin->doRemoveHttpPrefixFromAttributes($this->isFeatureActive('remove_http_prefix_from_attributes'));
            $htmlMin->doRemoveDefaultAttributes($this->isFeatureActive('remove_default_attributes'));
            $htmlMin->doRemoveDeprecatedAnchorName($this->isFeatureActive('remove_deprecated_anchor_name'));
            $htmlMin->doRemoveDeprecatedScriptCharsetAttribute($this->isFeatureActive('remove_deprecated_script_charset_attribute'));
            $htmlMin->doRemoveDeprecatedTypeFromScriptTag($this->isFeatureActive('remove_deprecated_type_from_script_tag'));
            $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink($this->isFeatureActive('remove_deprecated_type_from_stylesheet_link'));
            $htmlMin->doRemoveEmptyAttributes($this->isFeatureActive('remove_empty_attributes'));
            $htmlMin->doRemoveValueFromEmptyInput($this->isFeatureActive('remove_value_from_empty_input'));
            $htmlMin->doSortCssClassNames($this->isFeatureActive('sort_css_class_names'));
            $htmlMin->doSortHtmlAttributes($this->isFeatureActive('sort_html_attributes'));
            $htmlMin->doRemoveSpacesBetweenTags($this->isFeatureActive('remove_spaces_between_tags'));
            $htmlMin->doRemoveOmittedQuotes($this->isFeatureActive('remove_omitted_quotes'));
            $htmlMin->doRemoveOmittedHtmlTags($this->isFeatureActive('remove_omitted_html_tags'));
            $originalHtml = $html;

            // Not nice but this is really hardcoded in the core.
            if ($this->isFeatureActive('remove_comments')) {

                $typo3Comment = $this->preserveTypo3Comment($html);

                $html = $htmlMin->minify($html);
                $output = [];
                $languageMeta = preg_match_all('/<meta charset=[a-zA-Z0-9-_"]*>/', $html, $output);
                if ($languageMeta) {
                    $insertAt = strpos($html, $output[0][0]) + strlen($output[0][0]);
                    $html = substr($html, 0, $insertAt) . $typo3Comment . substr($html, $insertAt);
                } else {
                    $htmlMin->doRemoveComments(false);
                    $html = $htmlMin->minify($html);
                }
            } else {
                $html = $htmlMin->minify($html);
            }

            if (empty($html)) {
                $html = $originalHtml;
            }

            $body = new Stream('php://temp', 'wb+');
            $body->write($html);
            $response = $response->withBody($body);
        }
        return $response;
    }

    protected function isFeatureActive(string $feature): bool
    {
        return isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify'][$feature]) && $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify'][$feature] === '1';
    }

    private function preserveTypo3Comment(string $html): string
    {
        $typo3CommentStart = strpos($html, '<!--');
        if ($typo3CommentStart !== false) {
            $typo3CommentStop = strpos($html, '-->', $typo3CommentStart);
            $typo3Comment = substr($html, $typo3CommentStart, $typo3CommentStop - $typo3CommentStart + 3);

            if (strpos($typo3Comment, 'TYPO3') !== false) {
                return $typo3Comment;
            } else {
                $html = str_replace($typo3Comment, '', $html);
                return $this->preserveTypo3Comment($html);
            }
        }

        return '';
    }
}
