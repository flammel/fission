<?php

namespace Flammel\Fission\Twig;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\BackendDataProvider;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\Service\NeosFunctions;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class FissionRuntimeExtension
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @Flow\Inject
     * @var NeosFunctions
     */
    protected $neosFunctions;

    /**
     * @param mixed $value
     */
    public function dumpFunction($value): void
    {
        \Neos\Flow\var_dump($value);
    }

    /**
     * @param NodeInterface $node
     * @return WrappedNode
     */
    public function wrapFunction(NodeInterface $node): WrappedNode
    {
        return new WrappedNode($node);
    }

    /**
     * @param NodeInterface|WrappedNode $node
     * @param mixed ...$props
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws FissionException
     */
    public function nodeFunction($node, ...$props): string
    {
        if (!WrappedNode::isWrappable($node)) {
            throw new FissionException('Invalid first argument passed to node function');
        }
        $node = new WrappedNode($node);
        array_unshift($props, $node);
        $rendered = $this->fissionContext->getComponentRenderer()->render(
            new ComponentName($node->nodeTypeName()),
            new ComponentArguments($props)
        );
        return $rendered . PHP_EOL . $this->fissionContext->getBackendDataProvider()->nodeInformation($node->unwrap());
    }

    /**
     * @return BackendDataProvider
     */
    public function backendFunction(): BackendDataProvider
    {
        return $this->fissionContext->getBackendDataProvider();
    }

    /**
     * @return WrappedNode
     */
    public function siteNodeFunction(): WrappedNode
    {
        return new WrappedNode($this->fissionContext->getSiteNode());
    }

    /**
     * @return WrappedNode
     */
    public function documentNodeFunction(): WrappedNode
    {
        return new WrappedNode($this->fissionContext->getDocumentNode());
    }

    /**
     * @return string
     */
    public function nodeRootFunction($node): string
    {
        return $this->fissionContext->getBackendDataProvider()->nodeRootElementAttributes(new WrappedNode($node));
    }

    /**
     * @return NeosFunctions
     */
    public function neosFunction(): NeosFunctions
    {
        return $this->neosFunctions;
    }
}
