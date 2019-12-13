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
It will look for this file in all paths that are configured in the `Flammel.Fission.templatePaths` setting.
Therefore, any package that provides template files needs to add its path to this setting:

```
# file: Settings.yaml
Flammel:
  Fission:
    templatePaths:
      - Path/To/Twig/Files/Relative/To/Packages/Directory
```

This Twig file is the first _component_ that is rendered.

### Components

Fission uses the [Zweig](https://github.com/flammel/zweig) library to implement components.
Zweig basically provides an enhanced way to include templates in other templates.
It can do slightly more than Twig's `include`, but the exact differences are out of scope for this introduction.
In Zweig, the function `component("templateName")` includes a template with the name `templateName`.
Let's say we defined our navigation in a Twig file:

```
{# file: Navbar.twig #}

<nav>
    <a href="/products">Products</a>
    <a href="/about-us">About Us</a>
</nav>
```

We can include the navigation in another template using the `component` function:


```
{# file: Layout.twig #}

<!doctype html>
<html>
    <head><title>Title</title></head>
    <body>
        {{ component('Navbar') }}
    </body>
</html>
```

When Zweig renders the file `Layout.twig`, the resulting HTML will be

```
<!doctype html>
<html>
    <head><title>Title</title></head>
    <body>
        <nav>
            <a href="/products">Products</a>
            <a href="/about-us">About Us</a>
        </nav>
    </body>
</html>
```

Since we use the `component` function (and, as we will see shortly, the tag of the same name) to include
templates, we use the term _component_ to refer to any template that is included via the `component` function or tag.

#### Props

The `component` function allows us to pass arguments to the included file.
We call these arguments _props_.
Props are passed to a component by providing additional arguments to the `component` function:

```
{# file: ContactForm.twig #}

<form>
    ...
    {{ component("FancyButton", "Send", "submit" }}
</form>
```

In the included file, these values are available via the `props` variable:

```
{# file: FancyButton.twig #}

<button type="{{ props[1] }}" class="fancy">
    {{ props[0] }}
</button>
```

Since accessing props via indices is cumbersome, error prone and hard to read, I recommend defining variables at the beginning of the component:

```
{# file: FancyButton.twig #}
{% set content = props[0] %}
{% set type = props[1] %}

<button type="{{ type }}" class="fancy">
    {{ content }}
</button>
```

This also serves as documentation on what props a component expects and makes it easy to define optional props:

```
{# file: FancyButton.twig #}
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

#### Slots

The problem with props is that we cannot easily use them to pass markup to a component.
For this purpose, Zweig uses _slots_.
A component can define slots using the `slot` tag. The following component defines a slot for the page body
and uses a prop for the page title:

```
{# file: Layout.twig #}
{% set title = props[0] %}

<!doctype html>
<html>
    <head>
        <title>{{ title }}</title>
    </head>
    <body>
        {% slot "body" %}
            <p>Optional default content of the slot</p>
        {% endslot %}
    </body>
</html>
```

When we want to include a component that defines slots, we have to use the `component` tag instead of the function.
We pass props to the component using the `with` keyword and fill slots using the `fill` tag:

```
{# file: AboutUs.twig #}

{% component "Layout" with ["About Us"] %}
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

### Nodes

Now that we know how components work, remember that the initial step when rendering a Neos page is to render a component.
If the current document node has the node type `Neos.Neos:Page`, then the component `Neos.Neos:Page` will be rendered.
Node type names correspond directly to component names.
This component name translates to the template path `Neos.Neos/Page.twig`.
The Fission view will pass a single prop to this component: The document node.
The Twig template for the node `Neos.Neos:Page` might look like this:

```
{# file: Neos.Neos/Page.twig #}
{% set node = props[0] %}

{% component "Layout" with [node.prop("title")] %}
    {% fill "body" %}
        <main>
            {{ node.prop("content") }}
        </main>
    {% endfill %}
{% endcomponent %}
```

This works fine when the `content` node property contains a string.
But what if it contains a node, as is common in Neos?
Since node type names correspond directly to component names, we could render the node in the `content` property like this:

```
{{ component(node.prop("content").nodeTypeName(), node.prop("content")) }}
```

We pass the node as the first prop so that we can access its properties in the included file.
Fission provides a helper function called `node` because rendering nodes is a common task and the above syntax is rather verbose:

```
{{ node(aNode, "here", "be", "props") }}
{# is the same as #}
{{ component(aNode.nodeTypeName(), aNode, "here", "be", "props") }}
```

So if the `content` node property contains a node, we have to adjust the template like this:

```
{# file: Neos.Neos/Page.twig #}
{% set node = props[0] %}

{% component "Layout" with [node.prop("title")] %}
    {% fill "body" %}
        <main>
            {{ node(node.prop("content")) }}             {# only this line changed #}
        </main>
    {% endfill %}
{% endcomponent %}
```

### Functions

Sometimes, templates need data that is not directly available in the provided props or slots.
In Fission, we use Twig functions to get this data into our templates.

For example, let's say we want to render a `Product` node and that in order to do so we need some data that is
not stored in the node's properties.
We will define a Twig function that fetches this data for us.

First, we have to register the function in the settings:

```
# file: Settings.yaml
Flammel:
  Fission:
    functions:
      productData: Vendor\MyPackage\ProductDataFunction
```

Now we can implement our function:

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

We can use all features of Flow/Neos in this implementation, including dependency injection.

The function is used like any other Twig function:

```
{# file: Product.twig #}
{% set node = props[0] %}
{% set additionalData = productData(node) %}

<h1>{{ node.prop("title") }}</h1>
{{ additionalData.importantInformation }}
```

Note that all classes in the `Flammel.Fission.functions` setting must implement the `FissionFunction` interface.
The name of the function is determined by the key in the settings file.

The above approach to defining functions has two drawbacks:
First, we have to change the settings and implement the interface for every function we want to define.
Second, if two packages define two functions with the same name, it is not immediately clear which implementation will be used.
To address these issues, I recommend creating at most _one_ `FissionFunction` implementation per package, using a name that we expect to be unique.
We can then define methods on this class and use those as functions in our templates:

```
# file: Settings.yaml
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

```
{# file: Product.twig #}
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
