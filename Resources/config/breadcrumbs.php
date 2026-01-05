<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use WhiteOctober\BreadcrumbsBundle\Templating\Helper\BreadcrumbsHelper;
use WhiteOctober\BreadcrumbsBundle\Twig\Extension\BreadcrumbsExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->public(false);

    // Our service, for controllers
    $services->set(Breadcrumbs::class)
        ->call('setRouter', [service('router')])
        ->tag('kernel.reset', ['method' => 'clear']);

    $services->alias('white_october_breadcrumbs', Breadcrumbs::class)
        ->public();

    // Templating helper
    $services->set('white_october_breadcrumbs.helper', BreadcrumbsHelper::class)
        ->public()
        ->args([
            service('twig'),
            service('white_october_breadcrumbs'),
            param('white_october_breadcrumbs.options'),
        ]);

    // Twig extension
    $services->set('white_october_breadcrumbs.twig', BreadcrumbsExtension::class)
        ->public()
        ->args([service('service_container')])
        ->tag('twig.extension');
};