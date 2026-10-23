<?php

declare(strict_types=1);

namespace Pluswerk\PlusMinify\Tests\Functional\Middleware;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MinifyMiddlewareTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'pluswerk/minify',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'minify' => [
                'optimize_via_html_dom_parser' => '1',
                'remove_comments' => '1',
                'sum_up_whitespace' => '1',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendRootPage(1, ['EXT:minify/Tests/Functional/Fixtures/page.typoscript']);
        GeneralUtility::mkdir_deep($this->instancePath . '/typo3conf/sites/main');
        GeneralUtility::writeFile(
            $this->instancePath . '/typo3conf/sites/main/config.yaml',
            "rootPageId: 1\nbase: 'http://localhost/'\nlanguages:\n  - languageId: 0\n    title: English\n    locale: en_US.UTF-8\n    base: /\n"
        );
    }

    #[Test]
    public function minifiesTheHtmlOfAFrontendPage(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));

        $html = (string)$response->getBody();
        self::assertStringContainsString('<p>Hello world</p>', $html);
        self::assertStringNotContainsString('<!-- drop me -->', $html);
    }

    #[Test]
    public function sendsTheContentLengthOfTheMinifiedHtml(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/'));

        self::assertSame((string)strlen((string)$response->getBody()), $response->getHeaderLine('Content-Length'));
    }
}
