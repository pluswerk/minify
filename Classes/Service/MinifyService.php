<?php

declare(strict_types=1);

namespace Pluswerk\PlusMinify\Service;

use voku\helper\HtmlMin;

class MinifyService
{
    public function minify(string $html): string
    {
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
        $htmlMin->doRemoveComments($this->isFeatureActive('remove_comments'));

        $originalHtml = $html;

        // Not nice but this is really hardcoded in the core.
        if ($this->isFeatureActive('remove_comments')) {
            $typo3Comment = $this->preserveTypo3Comment($html);
            $html = $htmlMin->minify($html);
            $output = [];
            $languageMeta = preg_match_all('#<meta charset=[a-zA-Z0-9-_"]*>#', $html, $output);
            if ($languageMeta) {
                $insertAt = strpos($html, (string)$output[0][0]) + strlen($output[0][0]);
                $html = substr($html, 0, $insertAt) . $typo3Comment . substr($html, $insertAt);
            } else {
                $html = $htmlMin->minify($html);
            }
        } else {
            $html = $htmlMin->minify($html);
        }

        if (empty($html)) {
            return $originalHtml;
        }

        return $html;
    }


    protected function isFeatureActive(string $feature): bool
    {
        return isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify'][$feature]) && $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify'][$feature] === '1';
    }

    protected function preserveTypo3Comment(string $html): string
    {
        $typo3CommentStart = strpos($html, '<!--');
        if ($typo3CommentStart !== false) {
            $typo3CommentStop = strpos($html, '-->', $typo3CommentStart);
            $typo3Comment = substr($html, $typo3CommentStart, $typo3CommentStop - $typo3CommentStart + 3);

            if (str_contains($typo3Comment, 'TYPO3')) {
                return $typo3Comment;
            }

            $html = str_replace($typo3Comment, '', $html);
            return $this->preserveTypo3Comment($html);
        }

        return '';
    }
}
