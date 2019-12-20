<?php

namespace Flammel\Fission\Functions;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Exception\ZweigException;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class Node
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @param NodeInterface|WrappedNode $node
     * @return string
     * @throws FissionException
     */
    public function __invoke($node)
    {
        $node = new WrappedNode($node);
        $args['node'] = $node;
        try {
            $rendered = $this->fissionContext->getComponentRenderer()->render(
                new ComponentName($node->nodeTypeName()),
                new ComponentArguments($args)
            );
        } catch (ZweigException $e) {
            throw new FissionException('An error occured while trying to render a node', 1576236642, $e);
        }
        return $rendered . PHP_EOL . $this->fissionContext->getBackendDataProvider()->nodeInformation($node);
    }
}
