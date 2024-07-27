<?php

namespace WhiteOctober\BreadcrumbsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use WhiteOctober\BreadcrumbsBundle\Attribute\Breadcrumb;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumbListener
{
    public function __construct(
        private readonly Breadcrumbs     $breadcrumbs,
        private readonly RouterInterface $router,
    )
    {
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        /** @var Breadcrumb $breadcrumbAttribute */
        foreach ($event->getAttributes(Breadcrumb::class) as $breadcrumbAttribute) {
            $text = $breadcrumbAttribute->getText();

            if (empty($text)) {
                $this->addBreadcrumb($breadcrumbAttribute);
                continue;
            }

            $pattern = '/\{[^}]+\}/';
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER, 0);

            if (!isset($matches[0])) {
                $this->addBreadcrumb($breadcrumbAttribute);
                continue;
            }
            foreach ($matches[0] as $match) {
                $fullPathAttribute = trim($match, '{}');
                if (empty($fullPathAttribute)) {
                    continue;
                }
                $nameObject = explode('.', $fullPathAttribute)[0];
                $mb_strlen = mb_strlen($nameObject);
                $propertyPath = mb_strcut($fullPathAttribute, ++$mb_strlen);

                $object = $event->getNamedArguments()[$nameObject];
                $data = (string)$propertyAccessor->getValue($object, $propertyPath);

                $text = str_replace($match, $data, $text);

            }

            $this->addBreadcrumb($breadcrumbAttribute->setText($text));
        }
    }


    private function addBreadcrumb(Breadcrumb $attribute): void
    {
        $url = $attribute->getUrl();
        if (empty($url) && !empty($attribute->getRoute())) {
            $url = $this->router->generate($attribute->getRoute(), $attribute->getParameters(), $attribute->getReferenceType());
        }

        $this->breadcrumbs->addNamespaceItem(
            $attribute->getNamespace(),
            $attribute->getText(),
            $url,
            $attribute->getTranslationParameters(),
            $attribute->isTranslate()
        );
    }

}
