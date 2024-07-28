_This is a fork of [whiteoctober/BreadcrumbsBundle](https://github.com/whiteoctober/BreadcrumbsBundle) maintained for newer Symfony versions. See [whiteoctober/BreadcrumbsBundle#106](https://github.com/whiteoctober/BreadcrumbsBundle/issues/106)._

Installation
============

1. Install this bundle using [Composer](https://getcomposer.org/):
    
    ``` bash
    composer require mhujer/breadcrumbs-bundle
    ```

If you're using Symfony Flex, the following steps will be done automatically.
    
2. Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle::class => ['all' => true],
];
```

3. Configure the bundle in `config/packages/white_october_breadcrumbs.yaml`:
    
    ``` yaml
    # config/packages/white_october_breadcrumbs.yaml
    white_october_breadcrumbs: ~
    ```
  
That's it for basic configuration. For more options check the [Configuration](#configuration) section.

Usage
=====

In your application controller methods:

``` php
public function yourAction(User $user)
{
    $breadcrumbs = $this->get("white_october_breadcrumbs");
    
    // Simple example
    $breadcrumbs->addItem("Home", $this->get("router")->generate("index"));

    // Example without URL
    $breadcrumbs->addItem("Some text without link");

    // Example with parameter injected into translation "user.profile"
    $breadcrumbs->addItem($txt, $url, ["%user%" => $user->getName()]);
}
```
or with attributes and [PropertyAccess Component](https://symfony.com/doc/current/components/property_access.html)

```php
use WhiteOctober\BreadcrumbsBundle\Attribute\Breadcrumb;

#[Breadcrumb(text: 'textClassForAllClass', route: 'app_main')]
class Controller extends AbstractController{
    
    #[Breadcrumb(text: 'textMethod', route: 'app_main')]
    #[Breadcrumb(text: 'textMethod1', url: '/')]
    #[Breadcrumb(text: 'textMethod  {user.name} in {category.name}', route: 'app_main', parameters: ['id' => '{user.id}', 'category' => '{category.name}'])]
    public function yourAction(User $user, Category $category)
    {
    }
}
```

It is preferable, that you don't retrieve the service via `get`. Use 
[dependency injection](https://symfony.com/doc/current/service_container.html#fetching-and-using-services) instead:
                                                              
```php
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class YourController extends AbstractController
{
    public function yourAction(Breadcrumbs $breadcrumbs)
    {
      // ...
    }
}
```
 

Then, in your template:

``` jinja
{{ wo_render_breadcrumbs() }}
```

The last item in the breadcrumbs collection will automatically be rendered
as plain text rather than a `<a>...</a>` tag.

The `addItem()` method adds an item to the *end* of the breadcrumbs collection.
You can use the `prependItem()` method to add an item to the *beginning* of
the breadcrumbs collection.  This is handy when used in conjunction with
hierarchical data (e.g. Doctrine Nested-Set).  This example uses categories in
a product catalog:

``` php
public function yourAction(Category $category)
{
    $breadcrumbs = $this->get("white_october_breadcrumbs");

    $node = $category;

    while ($node) {
        $breadcrumbs->prependItem($node->getName(), "<category URL>");

        $node = $node->getParent();
    }
}
```

If you do not want to generate a URL manually, you can easily add breadcrumb items
passing only the route name with any required parameters, using the `addRouteItem()`
and `prependRouteItem()` methods:

``` php
public function yourAction()
{
    $breadcrumbs = $this->get("white_october_breadcrumbs");
    
    // Pass "_demo" route name without any parameters
    $breadcrumbs->addRouteItem("Demo", "_demo");

    // Pass "_demo_hello" route name with route parameters
    $breadcrumbs->addRouteItem("Hello Breadcrumbs", "_demo_hello", [
        'name' => 'Breadcrumbs',
    ]);

    // Add "homepage" route link at the start of the breadcrumbs
    $breadcrumbs->prependRouteItem("Home", "homepage");
}
```

Configuration
=============

The following *default* parameters can be overridden in your `config/packages/white_october_breadcrumbs.yaml`:

``` yaml
# config/packages/white_october_breadcrumbs.yaml
white_october_breadcrumbs:
    separator:          '/'
    separatorClass:     'separator'
    listId:             'wo-breadcrumbs'
    listClass:          'breadcrumb'
    itemClass:          ''
    linkRel:            ''
    locale:             ~ # defaults to null, so the default locale is used
    translation_domain: ~ # defaults to null, so the default domain is used
    viewTemplate:       '@WhiteOctoberBreadcrumbs/microdata.html.twig'
```

These can also be passed as parameters in the view when rendering the
breadcrumbs - for example:

``` jinja
{{ wo_render_breadcrumbs({separator: '>', listId: 'breadcrumbs'}) }}
```

> **NOTE:** If you need more than one set of breadcrumbs on the same page you can use namespaces.
By default, breadcrumbs use the `default` namespace, but you can add more.
To add breadcrumbs to your custom namespace use `addNamespaceItem` / `prependNamespaceItem`
or `addNamespaceRouteItem` / `prependNamespaceRouteItem` methods respectively, for example:

``` php
public function yourAction(User $user)
{
    $breadcrumbs = $this->get("white_october_breadcrumbs");

    // Simple example
    $breadcrumbs->prependNamespaceItem("subsection", "Home", $this->get("router")->generate("index"));

    // Example without URL
    $breadcrumbs->addNamespaceItem("subsection", "Some text without link");

    // Example with parameter injected into translation "user.profile"
    $breadcrumbs->addNamespaceItem("subsection", $txt, $url, ["%user%" => $user->getName()]);
    
    // Example with route name with required parameters
    $breadcrumbs->addNamespaceRouteItem("subsection", $user->getName(), "user_show", ["id" => $user->getId()]);
}
```

Then to render the `subsection` breadcrumbs in your templates, specify this namespace in the options:

``` jinja
{{ wo_render_breadcrumbs({namespace: "subsection"}) }}
```

Advanced Usage
==============

You can add a whole array of objects at once

``` php
$breadcrumbs->addObjectArray(array $objects, $text, $url, $translationParameters);
```

```
objects:            array of objects
text:               name of object property or closure
url:                name of URL property or closure
```

Example:

``` php
$that = $this;
$breadcrumbs->addObjectArray($selectedPath, "name", function($object) use ($that) {
    return $that->generateUrl('_object_index', ['slug' => $object->getSlug()]);
});
```

You can also add a tree path

``` php
$breadcrumbs->addObjectTree($object, $text, $url = "", $parent = 'parent', array $translationParameters = [], $firstPosition = -1)
```

```
object:             object to start with
text:               name of object property or closure
url:                name of URL property or closure
parent:             name of parent property or closure
firstPosition:      position to start inserting items (-1 = determine automatically)
```

> **NOTE:** You can use `addNamespaceObjectArray` and `addNamespaceObjectTree` respectively
for work with multiple breadcrumbs on the same page.

Overriding the template
=======================

There are two methods for doing this.

1. You can override the template used by copying the
    `Resources/views/microdata.html.twig` file out of the bundle and placing it
    into `<your-project>/templates/bundles/WhiteOctoberBreadcrumbsBundle`, then customising
    as you see fit. Check the [Overriding bundle templates][1] documentation section
    for more information.

2. Use the `viewTemplate` configuration parameter:
    
    ``` jinja
    {{ wo_render_breadcrumbs({ viewTemplate: "@WhiteOctoberBreadcrumbs/yourBreadcrumbs.html.twig" }) }}
    ```
> **NOTE:** If you want to use the JSON-LD format, there's already an existing template 
at `@WhiteOctoberBreadcrumbs/json-ld.html.twig`. Just set this template as the value for 
`viewTemplate` either in your Twig function call (see Step 2 above) or in your bundle [configuration](#configuration).



[1]: https://symfony.com/doc/current/bundles/override.html#templates
[2]: https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx

Contributing
============

We welcome contributions to this project, including pull requests and issues (and discussions on existing issues).

If you'd like to contribute code but aren't sure what, the [issues list](https://github.com/mhujer/breadcrumbsbundle/issues) is a good place to start.
If you're a first-time code contributor, you may find Github's guide to [forking projects](https://guides.github.com/activities/forking/) helpful.

All contributors (whether contributing code, involved in issue discussions, or involved in any other way) must abide by our [code of conduct](https://github.com/whiteoctober/open-source-code-of-conduct/blob/master/code_of_conduct.md).
