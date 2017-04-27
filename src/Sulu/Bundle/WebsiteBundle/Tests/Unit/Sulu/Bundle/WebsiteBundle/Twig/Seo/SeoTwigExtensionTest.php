<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsiteBundle\Twig\Seo;

use Prophecy\Argument;
use Sulu\Bundle\WebsiteBundle\Twig\Content\ContentPathInterface;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Component\Webspace\Portal;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SeoTwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SeoTwigExtension
     */
    private $seoTwigExtension;

    /**
     * @var RequestAnalyzerInterface
     */
    private $requestAnalyzer;

    /**
     * @var ContentPathInterface
     */
    private $contentPath;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        $this->requestAnalyzer = $this->prophesize(RequestAnalyzerInterface::class);
        $this->contentPath = $this->prophesize(ContentPathInterface::class);
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->request = $this->prophesize(Request::class);
        $this->request = $this->prophesize(Request::class);
        $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());
        $this->request->getScheme()->willReturn('https');

        $this->seoTwigExtension = new SeoTwigExtension(
            $this->requestAnalyzer->reveal(),
            $this->contentPath->reveal(),
            $this->requestStack->reveal()
        );

        $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());
    }

    public function testGetFunctions()
    {
        $result = $this->seoTwigExtension->getFunctions();

        $this->assertEquals(
            new \Twig_SimpleFunction('sulu_seo', [$this->seoTwigExtension, 'renderSeoTags'], ['needs_environment' => true]),
            $result[0]
        );
    }

    /**
     * @dataProvider provideSeoData
     */
    public function testRenderSeoTags(
        $seoExtension,
        $content,
        $urls,
        $defaultLocale,
        $shadowBaseLocale,
        $xDefaultLocale,
        $expectedResults,
        $unexpectedResults = [],
        $resourceLocator = '/test',
        $requestSeoData = []
    ) {
        $this->markTestSkipped(); // TODO add functional tests for template rendering

        $this->request->get('_seo', [])->willReturn($requestSeoData);

        /** @var Localization $localization */
        $localization = $this->prophesize(Localization::class);
        $localization->getLocalization()->willReturn($xDefaultLocale ?: $defaultLocale);

        /** @var Portal $portal */
        $portal = $this->prophesize(Portal::class);
        $portal->getXDefaultLocalization()->willReturn($localization->reveal());

        $this->requestAnalyzer->getPortal()->willReturn($portal->reveal());
        $this->requestAnalyzer->getResourceLocator()->willReturn($resourceLocator);

        $webspace = $this->prophesize(Webspace::class);
        $this->requestAnalyzer->getWebspace()->willReturn($webspace);

        $this->contentPath->getContentPath(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            null,
            'https'
        )->will(
            function ($arguments) {
                return '/' . str_replace('_', '-', $arguments[2]) . $arguments[0];
            }
        );

        $result = $this->seoTwigExtension->renderSeoTags(
            $seoExtension,
            $content,
            $urls,
            $shadowBaseLocale
        );

        foreach ($expectedResults as $expectedResult) {
            $this->assertContains($expectedResult, $result);
        }

        foreach ($unexpectedResults as $unexpectedResult) {
            $this->assertNotContains($unexpectedResult, $result);
        }
    }

    public function testRenderSeoTagsWithoutPortal()
    {
        $this->markTestSkipped(); // TODO add functional tests for template rendering

        $this->request->get('_seo', [])->willReturn([]);
        $this->seoTwigExtension->renderSeoTags([], [], [], null);
    }

    public function provideSeoData()
    {
        return [
            [
                [
                    'title' => 'SEO title',
                    'description' => 'SEO description',
                    'keywords' => 'SEO keywords',
                    'canonicalUrl' => '/canonical-url',
                    'noIndex' => true,
                    'noFollow' => true,
                    'hideInSitemap' => true,
                    'customBlock' => 'Custom Block',
                ],
                [
                    'title' => 'Content title',
                ],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                ],
                'en',
                'en',
                null,
                [
                    '<title>SEO title</title>',
                    '<meta name="description" content="SEO description"/>',
                    '<meta name="keywords" content="SEO keywords"/>',
                    '<meta name="robots" content="noIndex,noFollow"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="canonical" href="/canonical-url"/>',
                ],
            ],
            [
                [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'canonicalUrl' => '',
                    'noIndex' => false,
                    'noFollow' => false,
                    'hideInSitemap' => true,
                    'customBlock' => '',
                ],
                [
                    'title' => 'Content title',
                ],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                ],
                'de',
                'en',
                null,
                [
                    '<title>Content title</title>',
                    '<meta name="robots" content="index,follow"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="canonical" href="/en/url-en"/>',
                ],
                [
                    '<meta name="description" content=""/>',
                    '<meta name="keywords" content=""/>',
                ],
            ],
            [
                [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'canonicalUrl' => '',
                    'noIndex' => false,
                    'noFollow' => false,
                    'hideInSitemap' => true,
                    'customBlock' => '',
                ],
                [
                    'title' => 'Content title',
                ],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                ],
                'de',
                null,
                null,
                [
                    '<title>Content title</title>',
                    '<meta name="robots" content="index,follow"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                ],
                [
                    '<meta name="description" content=""/>',
                    '<meta name="keywords" content=""/>',
                    '<link rel="canonical" href=""/>',
                ],
            ],
            [
                [
                    'title' => '',
                    'description' => '',
                    'keywords' => '',
                    'canonicalUrl' => '/canonical-url',
                    'noIndex' => false,
                    'noFollow' => false,
                    'hideInSitemap' => true,
                    'customBlock' => '',
                ],
                [
                    'title' => 'Content title',
                ],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                    'de_at' => '/url-de-at',
                ],
                'de',
                null,
                null,
                [
                    '<title>Content title</title>',
                    '<meta name="robots" content="index,follow"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="alternate" href="/de-at/url-de-at" hreflang="de-at"/>',
                    '<link rel="canonical" href="/canonical-url"/>',
                ],
                [
                    '<meta name="description" content=""/>',
                    '<meta name="keywords" content=""/>',
                ],
            ],
            [
                [],
                [],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                ],
                'en',
                'en',
                'de',
                [
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                ],
            ],
            [
                [],
                [],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                    'de_at' => '/',
                ],
                'en',
                'en',
                null,
                [
                    '<link rel="alternate" href="/en/url-en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                ],
                [
                    '<link rel="alternate" href="/de_at" hreflang="de_at"/>',
                ],
            ],
            [
                [],
                [],
                [
                    'en' => '/',
                    'de' => '/url-de',
                ],
                'en',
                'en',
                null,
                [
                    // no alternate link if translation is the only one
                ],
                [
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="alternate" href="/en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en" hreflang="en"/>',
                ],
            ],
            [
                [],
                [],
                [
                    'en' => '/',
                    'de' => '/url-de',
                ],
                'de',
                'en',
                'en',
                [
                    // no alternate link if translation is the only one
                ],
                [
                    '<link rel="alternate" href="/de/url-de" hreflang="x-default"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="alternate" href="/en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en" hreflang="en"/>',
                ],
            ],
            [
                [],
                [],
                [
                    'en' => '/',
                    'de' => '/',
                ],
                'en',
                'en',
                null,
                [
                    '<link rel="alternate" href="/en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en" hreflang="en"/>',
                    '<link rel="alternate" href="/de" hreflang="de"/>',
                ],
                [],
                false,
            ],
            [
                [
                    'title' => 'SEO title',
                    'description' => 'SEO description',
                    'keywords' => 'SEO keywords',
                    'canonicalUrl' => '/canonical-url',
                    'noIndex' => true,
                    'noFollow' => true,
                    'hideInSitemap' => true,
                    'customBlock' => 'Custom Block',
                ],
                [
                    'title' => 'Content title',
                ],
                [
                    'en' => '/url-en',
                    'de' => '/url-de',
                ],
                'en',
                'en',
                null,
                [
                    '<title>SEO title</title>',
                    '<meta name="description" content="SEO description"/>',
                    '<meta name="keywords" content="SEO keywords"/>',
                    '<meta name="robots" content="index,follow"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="x-default"/>',
                    '<link rel="alternate" href="/en/url-en" hreflang="en"/>',
                    '<link rel="alternate" href="/de/url-de" hreflang="de"/>',
                    '<link rel="canonical" href="/test-url"/>',
                ],
                [],
                false,
                [
                    'canonicalUrl' => '/test-url',
                    'noIndex' => false,
                    'noFollow' => false,
                ],
            ],
            [
                [
                    'title' => '"SEO title"',
                    'description' => '"SEO description"',
                    'keywords' => '"SEO keywords"',
                    'customBlock' => '"Custom Block"',
                ],
                [],
                [
                    'en' => '/url-en',
                ],
                'en',
                'en',
                null,
                [
                    '<title>&quot;SEO title&quot;</title>',
                    '<meta name="description" content="&quot;SEO description&quot;"/>',
                    '<meta name="keywords" content="&quot;SEO keywords&quot;"/>',
                ],
            ],
            [
                [
                    'description' => '"SEO description"',
                    'keywords' => '"SEO keywords"',
                ],
                [
                    'title' => '"Content title"',
                ],
                [
                    'en' => '/url-en',
                ],
                'en',
                'en',
                null,
                [
                    '<title>&quot;Content title&quot;</title>',
                    '<meta name="description" content="&quot;SEO description&quot;"/>',
                    '<meta name="keywords" content="&quot;SEO keywords&quot;"/>',
                ],
            ],
        ];
    }
}
