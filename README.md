# Fission

Neos plugin to replace Fusion with Twig.

## Installation

The package is not on [packagist.org](https://packagist.org/). So the first step is to add the GitHub repository to your `composer.json:`

```
"repositories":[
    {
        "type": "vcs",
        "url": "git@github.com:flammel/fission.git"
    }
]
```

Then run
```
composer require flammel/fission
```

## Introduction

Please familiarize yourself with [Twig](https://twig.symfony.com/doc/3.x/templates.html) before reading this guide.

The rendering of a Neos page (document node) starts in the `FissionView`.
This view will attempt to render a Twig file that is named after the document node's type.
For example, if the document node's type is `Neos.Neos:Page`, then Fission will attempt to render the Twig file `Neos.Neos/Page.twig`.
It will look for this file in all paths that are configured in the `Flammel.Fission.templatePaths` setting:

```
# In Settings.yaml
Flammel:
  Fission:
    templatePaths:
      - Path/To/Your/Package/Relative/To/Packages/Directory
```

This Twig file is the first _component_ that is rendered.

### Components

Fission uses the [Zweig](https://github.com/flammel/zweig) library to implement components.

In Zweig, every Twig file is a component and every component is a Twig file.
There is no difference between a "normal" Twig file and a "component" Twig file.
For example, this file is a component:

```
{# file name: Navbar.twig #}

<nav>
    <a href="/products">Products</a>
    <a href="/about-us">About Us</a>
</nav>
```

Nothing marks it as a component, it is just a Twig file (albeit a rather boring one without any actual Twig code).

The Zweig extension for Twig basically provides an enhanced way to include templates in other templates.
For example, in Twig without Zweig, you can do this:

```
{# file name: Layout.twig #}

<!doctype html>
<html>
    <head><title>Title</title></head>
    <body>
        {{ include('Navbar.html') }}
    </body>
</html>
```

You could even pass variables to the included template:

```
{{ include('Navbar.twig', {'active': 'products'}) }}
```

The same is possible with Zweig:

```
{{ component('Navbar', {'active': 'products'}) }}
```

So what's the point of Zweig, then? The point is that components can define _slots_ which will be filled by other components.
For example, consider the following component (remember, a component is just a Twig template) that defines two slots,
one for the title and one for the body of the page:

```
{# file name: Layout.twig #}

<!doctype html>
<html>
    <head>
        <title>{% slot "title" %}{% endslot %}</title>
    </head>
    <body>
        {% slot "body" %}{% endslot %}
    </body>
</html>
```

Now we can use this component in a different component and fill these slots:

```
{# file name: AboutUs.twig #}

{% component "Layout" %}
    {% fill "title" %}
        About Us
    {% endfill %}
    {% fill "body" %}
        <h1>About us</h1>
        <p>Lorem ipsum ...</p>
    {% endfill %}
{% endcomponent %}
```

When Zweig renders the component `AboutUs`, the result will be the following HTML:

```
<!doctype html>
<html>
    <head>
        <title>About Us</title>
    </head>
    <body>
        <h1>About us</h1>
        <p>Lorem ipsum ...</p>
    </body>
</html>
```

Note that Zweig provides both a _tag_ and a _function_ called `component`.
If you want to fill slots, you have to use the tag. Otherwise, use the function.
In both cases, you can pass props to the component:

```
{# file name: ContactForm.twig #}

<form>
    ...
    {# The two following lines are equivalent #}
    {{ component("FancyButton", "Send", "submit" }}
    {% component "FancyButton" with ["Send", "submit"] %}{% endcomponent %}
</form>
```

Those props will be available in the `props` variable in the included component:

```
{# file name: FancyButton.twig #}

<button type="{{ {{ props[1] }}class="fancy">
    {{ props[0] }}
</button>
```

Since keeping track of props indices is cumbersome, I recomment defining variables at the beginning of the component:

```
{# file name: FancyButton.twig #}
{% set content = props[0] %}
{% set type = props[1] %}

<button type="{{ type }}" class="fancy">
    {{ content }}
</button>
```

This also serves as documentation on what props a component expects and makes it easy to define optional props:

```
{# file name: FancyButton.twig #}
{% set content = props[0] %}
{% set type = props[1]|default("button") %}

<button type="{{ type }}" class="fancy">
    {{ content }}
</button>
```

Now invoking this component with only one prop, like `{{ component("FancyButton", "Save") }}`, will render

```
<button type="button" class="fancy">Save</button>
```

### Nodes

Now that we know how components work, remember that the initial step when rendering a Neos page is to render a component.
If the current document node has the node type `Neos.Neos:Page`, then the component `Neos.Neos:Page` will be rendered.
Node type names correspond directly to component names.
This component name translates to the template path `Neos.Neos/Page.twig`.
The Fission view will pass a single prop to this component: The document node.
The content of the Twig file might look like this:

```
{# file name: Neos.Neos/Page.twig #}
{% set node = props[0] %}

{% component "Layout" %}
    {% fill "title" %}
        {{ node.prop("title") }}
    {% endfill %}
    {% fill "body" %}
        {{ node.prop("content") }}
    {% endfill %}
{% endcomponent %}
```

This works fine when the `content` prop contains HTML.
But what if it contains a node, as is common in Neos?
Since node type names correspond directly to component names, we can render the node in the `content` prop like this:

```
{{ component(node.prop("content").nodeTypeName(), node.prop("content")) }}
```

We pass the node as the first prop so that we can access its properties in the included file.
Since rendering a node is such a common task, Fission provides a helper function called `node`:

```
{{ node(aNode, "you can", "pass", "additional data") }}
{# is the same as #}
{{ component(aNode.nodeTypeName(), aNode, "you can", "pass", "additional data") }}
```

So if the `content` prop contains a node, we have to adjust the template like this:

```
{# file name: Neos.Neos/Page.twig #}
{% set node = props[0] %}

{% component "Layout" %}
    {% fill "title" %}
        {{ node.prop("title") }}
    {% endfill %}
    {% fill "body" %}
        {{ node(node.prop("content")) }}             {# only this line changed #}
    {% endfill %}
{% endcomponent %}
```

### Implementing Helper Functions

It is often useful or necessary to compute some values that should be rendered.
In Fission, we do this using Twig functions.
Fission provides a very thin wrapper around these functions to make them integrate well with Neos.

Let's say we want to render a `Product` node and that in order to do so we need some data that is
not stored in the node's properties but some database table.
To get this data, we define a Twig function which fetches this data for us.

First, we have to register the function in the settings:

```
# In Settings.yaml
Flammel:
  Fission:
    functions:
      productData: Vendor\MyPackage\ProductDataFunction
```

Now we can implement this function:

```
<?php namespace Vendor\MyPackage;

class ProductDataFunction implements FissionFunction
{
    public function invoke(...$args)
    {
        $productNode = $args[0];
        return [
            'importantInformation' => $this->getImportantInformation($productNode)
        ];
    }
}
```

You can use all features of Flow/Neos in this implementation, including dependency injection.

We can now call this function from our component:

```
{# file name: Product.twig #}
{% set node = props[0] %}
{% set additionalData = productData(node) %}

<h1>{{ node.prop("title") }}</h1>
{{ additionalData.importantInformation }}
```

The name of the function is determined by the key in the settings file.

This approach to defining functions has two drawbacks:
First, you have to change the settings and implement the interface for every function you want to define.
Additionally, if two packages define two functions with the same name, then the implementation will depend on the order in which the package settings are loaded by Neos.
To address these issues, I recommend that you create _one_ `FissionFunction` implementation for your package, naming it after your package.
You can then define methods on this class and use those as functions in your templates.

```
# In Settings.yaml
Flammel:
  Fission:
    functions:
      myPackage: Vendor\MyPackage\MyPackageFunction
```

```
<?php namespace Vendor\MyPackage;

class MyPackageFunction implements FissionFunction
{
    public function invoke(...$args)
    {
        return $this;
    }

    public function productData($productNode): array
    {
        return [
            'importantInformation' => $this->getImportantInformation($productNode)
        ];
    }
}
```

And in your template:

```
{# file name: Product.twig #}
{% set node = props[0] %}
{% set additionalData = myPackage().productData(node) %}

<h1>{{ node.prop("title") }}</h1>
{{ additionalData.importantInformation }}
```

## Development

Run static analysis ([PHPStan](https://github.com/phpstan/phpstan) and [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)):

```make check```

Run tests:

```make test```

## License

MIT
