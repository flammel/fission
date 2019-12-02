# Fission

Neos plugin to replace Fusion with Twig.

## Installation

Modify your `composer.json:`

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

Change the `$defaultViewObjectName` property of `Neos\Neos\Controller\Frontend\NodeController` to `Flammel\Fission\View\FissionView::class`.

## Configuration

Instruct Fission to use templates from your package:

```
# In some Settings.yaml
Flammel:
  Fission:
    templatePaths:
      - Path/To/Your/Package/Relative/To/Packages/Directory
```

Instruct Fission to your own presenters for certain components:

```
# In some Settings.yaml
Flammel:
  Fission:
    presenters:
      Foo.Bar:Content.Text: \Foo\\Bar\Fission\Presenter\Content\TextPresenter
```

## Usage

## Development

Run static analysis ([PHPStan](https://github.com/phpstan/phpstan) and [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)):

```make check```

Run tests:

```make test```

## License

MIT
