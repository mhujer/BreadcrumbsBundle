<?php

/*
 * (c) Tezrik <yuzhakovgg@gmail.com>
 */

namespace WhiteOctober\BreadcrumbsBundle\Attribute;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Breadcrumb
{
    public function __construct(
        private string  $namespace = Breadcrumbs::DEFAULT_NAMESPACE,
        private string  $text = '',
        private ?string $url = null,
        private ?string $route = null,
        private array   $parameters = [],
        private int     $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        private array   $translationParameters = array(),
        private bool    $translate = true
    )
    {
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getReferenceType(): int
    {
        return $this->referenceType;
    }

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }

    public function isTranslate(): bool
    {
        return $this->translate;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
