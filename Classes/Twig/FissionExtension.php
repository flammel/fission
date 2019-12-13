<?php

namespace Flammel\Fission\Twig;

use Neos\Flow\Annotations as Flow;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * @Flow\Proxy(false)
 */
class FissionExtension implements ExtensionInterface
{
    /**
     * @param TwigFunction[] $functions
     */
    private $functions = [];

    /**
     * @param array<string, string> $configuredFunctions
     */
    public function __construct(array $configuredFunctions)
    {
        foreach ($configuredFunctions as $name => $class) {
            /** @var callable $callable */
            $callable = [$class, 'invoke'];
            $this->functions[] = new TwigFunction($name, $callable);
        }
    }

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
        return $this->functions;
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
