<?php

declare(strict_types=1);

namespace Pluswerk\PlusMinify\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pluswerk\PlusMinify\Service\MinifyService;

class MinifyServiceTest extends TestCase
{
    private MinifyService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset all minify feature flags — everything disabled by default
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify'] = [];

        $this->subject = new MinifyService();
    }

    // -------------------------------------------------------------------------
    // All features off (default)
    // -------------------------------------------------------------------------

    #[Test]
    public function minifyKeepsHtmlCommentsWhenNoFeaturesAreEnabled(): void
    {
        $html = '<html><head><meta charset="utf-8"><!-- a comment --></head><body><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringContainsString('<!-- a comment -->', $result);
    }

    #[Test]
    public function minifyKeepsTypo3CommentWhenNoFeaturesAreEnabled(): void
    {
        $html = '<html><head><meta charset="utf-8"><!-- TYPO3 page info --></head><body><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringContainsString('<!-- TYPO3 page info -->', $result);
    }

    // -------------------------------------------------------------------------
    // remove_comments alone — comment removal needs the DOM parser too,
    // so the output stays unchanged when only remove_comments is set.
    // -------------------------------------------------------------------------

    #[Test]
    public function minifyDoesNotRemoveCommentsWithOnlyRemoveCommentsFlagEnabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_comments'] = '1';

        $html = '<html><head><meta charset="utf-8"></head><body><!-- just a comment --><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        // Without the DOM parser the minifier cannot parse the tree,
        // so the comment is left in the output.
        self::assertStringContainsString('<!-- just a comment -->', $result);
    }

    // -------------------------------------------------------------------------
    // remove_comments + optimize_via_html_dom_parser (the realistic combination)
    // -------------------------------------------------------------------------

    #[Test]
    public function minifyRemovesRegularCommentsWhenRemoveCommentsAndDomParserAreEnabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_comments'] = '1';
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['optimize_via_html_dom_parser'] = '1';

        $html = '<html><head><meta charset="utf-8"></head><body><!-- just a comment --><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringNotContainsString('<!-- just a comment -->', $result);
        self::assertStringContainsString('<p>Hello</p>', $result);
    }

    #[Test]
    public function minifyPreservesTypo3CommentWhenRemoveCommentsAndDomParserAreEnabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_comments'] = '1';
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['optimize_via_html_dom_parser'] = '1';

        $html = '<html><head><meta charset="utf-8"><!-- TYPO3CMS id=1 --></head><body><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringContainsString('<!-- TYPO3CMS id=1 -->', $result);
    }

    #[Test]
    public function minifyRemovesRegularCommentButKeepsTypo3CommentWhenBothFlagsAreEnabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_comments'] = '1';
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['optimize_via_html_dom_parser'] = '1';

        $html = '<html><head><meta charset="utf-8"><!-- TYPO3 page --></head><body><!-- drop me --><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringContainsString('<!-- TYPO3 page -->', $result);
        self::assertStringNotContainsString('<!-- drop me -->', $result);
    }

    // -------------------------------------------------------------------------
    // Output integrity
    // -------------------------------------------------------------------------

    #[Test]
    public function minifyReturnsFallbackHtmlWhenMinifierProducesEmptyString(): void
    {
        // An empty string causes the minifier to return '' which triggers
        // the $originalHtml fallback — the method must not crash.
        $result = $this->subject->minify('');

        self::assertSame('', $result);
    }

    #[Test]
    public function minifyAppendsClosingBodyTagWhenAbsentFromMinifiedOutput(): void
    {
        // Enable features that actually cause HtmlMin to strip closing tags.
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_comments'] = '1';
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['optimize_via_html_dom_parser'] = '1';
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['remove_omitted_html_tags'] = '1';

        $html = '<html><head><meta charset="utf-8"></head><body><p>Hello</p></body></html>';

        $result = $this->subject->minify($html);

        self::assertStringEndsWith('</body>', $result);
    }
}
