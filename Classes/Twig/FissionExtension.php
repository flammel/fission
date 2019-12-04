<?php

namespace Flammel\Fission\Twig;

use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class FissionExtension implements ExtensionInterface
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return [];
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors()
    {
        return [];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return TwigTest[]
     */
    public function getTests()
    {
        return [];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('dump', [FissionRuntimeExtension::class, 'dumpFunction']),
            new TwigFunction('wrap', [FissionRuntimeExtension::class, 'wrapFunction']),
            new TwigFunction('node', [FissionRuntimeExtension::class, 'nodeFunction']),
            new TwigFunction('backend', [FissionRuntimeExtension::class, 'backendFunction']),
            new TwigFunction('siteNode', [FissionRuntimeExtension::class, 'siteNodeFunction']),
            new TwigFunction('documentNode', [FissionRuntimeExtension::class, 'documentNodeFunction']),
            new TwigFunction('nodeRoot', [FissionRuntimeExtension::class, 'nodeRootFunction']),
            new TwigFunction('neos', [FissionRuntimeExtension::class, 'neosFunction']),
        ];
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array<array> First array of unary operators, second array of binary operators
     */
    public function getOperators()
    {
        return [];
    }
}
