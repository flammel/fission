<?php

namespace Flammel\Fission\Twig;

use Flammel\Fission\Service\BackendDataProvider;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Translator;

/**
 * @Flow\Scope("singleton")
 */
class FissionRuntimeExtension
{
    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @param string $key
     * @param string $package
     * @return string
     */
    public function translateFunction(string $key, string $package): string
    {
        return $this->translator->translateById($key, [], [], null, 'Main', $package);
    }

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
     */
    public function nodeFunction($node, ...$props): string
    {
        $node = new WrappedNode($node);
        array_unshift($props, $node);
        $rendered = $this->fissionContext->getComponentRenderer()->render(
            new ComponentName($node->nodeTypeName()),
            new ComponentArguments($props)
        );
        return $this->addBackendData($rendered, $node);
    }

    /**
     * @param string $rendered
     * @param WrappedNode $node
     * @return string
     */
    private function addBackendData(string $rendered, WrappedNode $node): string
    {
        return $rendered . PHP_EOL . $this->fissionContext->getBackendDataProvider()->nodeInformation($node->unwrap());
    }

    /**
     * @return BackendDataProvider
     */
    public function backendFunction()
    {
        return $this->fissionContext->getBackendDataProvider();
    }

    /**
     * @return WrappedNode
     */
    public function siteNodeFunction()
    {
        return new WrappedNode($this->fissionContext->getSiteNode());
    }

    /**
     * @return WrappedNode
     */
    public function documentNodeFunction()
    {
        return new WrappedNode($this->fissionContext->getDocumentNode());
    }
}
