<?php

namespace WhiteOctober\BreadcrumbsBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;
use WhiteOctober\BreadcrumbsBundle\Attribute\Breadcrumb;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumbListener
{
    private ControllerArgumentsEvent $event;
    private PropertyAccessor $propertyAccess;

    public function __construct(
        private readonly Breadcrumbs     $breadcrumbs,
        private readonly RouterInterface $router,
    )
    {
        $this->propertyAccess = PropertyAccess::createPropertyAccessor();
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $this->event = $event;

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
                $data = $this->extractData($match);
                if ($data === null) {
                    continue;
                }

                $text = str_replace($match, $data, $text);
            }

            $parameters = [];
            foreach ($breadcrumbAttribute->getParameters() as $key => $parameter) {
                $parameters[$key] = $this->extractData($parameter);
            }
            if (!empty($parameters)) {
                $breadcrumbAttribute->setParameters($parameters);
            }

            $this->addBreadcrumb($breadcrumbAttribute->setText($text));
        }
    }

    private function extractData(string $match): ?string
    {
        $fullPathAttribute = trim($match, '{}');
        if (empty($fullPathAttribute)) {
            return null;
        }
        $nameObject = explode('.', $fullPathAttribute)[0];
        $mb_strlen = mb_strlen($nameObject);
        $propertyPath = mb_strcut($fullPathAttribute, ++$mb_strlen);

        $object = $this->event->getNamedArguments()[$nameObject];

        return (string)$this->propertyAccess->getValue($object, $propertyPath);
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
