<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BundleTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    public function testInitBundle()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        // Test if the service exists
        self::assertTrue($container->has('white_october_breadcrumbs.helper'));

        $service = $container->get('white_october_breadcrumbs.helper');
        self::assertInstanceOf(\WhiteOctober\BreadcrumbsBundle\Templating\Helper\BreadcrumbsHelper::class, $service);
    }

    public function testRendering()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs::class);
        $service->addItem('foo');

        /** @var \WhiteOctober\BreadcrumbsBundle\Twig\Extension\BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
<<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo</span>
                                    <meta itemprop="position" content="1" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs()
        );
    }

    public function testRenderingTranslationWithParameters()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs::class);
        $service->addItem('foo', '', ['name' => 'John']);

        /** @var \WhiteOctober\BreadcrumbsBundle\Twig\Extension\BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
<<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo__{name:John}</span>
                                    <meta itemprop="position" content="1" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs([
                'viewTemplate' => '@WhiteOctoberBreadcrumbs/microdata.html.twig'
            ])
        );
    }

    public function testRenderingTranslationWithParametersAndTranslationDomain()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs::class);
        $service->addItem('foo');
        $service->addItem('bar', '', ['name' => 'John']);

        /** @var \WhiteOctober\BreadcrumbsBundle\Twig\Extension\BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
            <<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo__domain:admin</span>
                                    <meta itemprop="position" content="1" />

                                    <span class='separator'>/</span>
                            </li>
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">bar__{name:John}__domain:admin</span>
                                    <meta itemprop="position" content="2" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs([
                'viewTemplate' => '@WhiteOctoberBreadcrumbs/microdata.html.twig',
                'translation_domain' => 'admin',
            ])
        );
    }

    private function getContainerForTests(): ContainerInterface
    {
        if (method_exists(WebTestCase::class, 'getContainer')) {
            return static::getContainer();
        }

        return static::$container;
    }

    public static function getKernelClass(): string
    {
        return \WhiteOctober\BreadcrumbsBundle\Test\AppKernel::class;
    }
}
