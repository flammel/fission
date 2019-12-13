<?php

namespace Flammel\Fission\Functions;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Exception\ZweigException;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class Node implements FissionFunction
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @param mixed ...$args
     * @return mixed|void
     * @throws FissionException
     */
    public function invoke(...$args)
    {
        if (!WrappedNode::isWrappable($args[0])) {
            throw new FissionException('Invalid first argument passed to node function');
        }
        $node = new WrappedNode($args[0]);
        $args[0] = $node;
        try {
            $rendered = $this->fissionContext->getComponentRenderer()->render(
                new ComponentName($node->nodeTypeName()),
                new ComponentArguments($args)
            );
        } catch (ZweigException $e) {
            throw new FissionException('An error occured while trying to render a node', 1576236642, $e);
        }
        return $rendered . PHP_EOL . $this->fissionContext->getBackendDataProvider()->nodeInformation($node->unwrap());
    }
}
