<?php

namespace Flammel\Fission\Fusion;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\FissionService;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Exception\ZweigException;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentContext;

class ComponentImplementation extends \Neos\Fusion\FusionObjects\ComponentImplementation
{
    /**
     * @Flow\Inject
     * @var FissionService
     */
    protected $fissionService;

    /**
     * @return mixed|string
     * @throws ZweigException
     * @throws FissionException
     */
    public function evaluate()
    {
        $componentRenderer = $this->fissionService->getComponentRenderer();
        $nodeFromContext = $this->getRuntime()->getCurrentContext()['node'];
        if (!$nodeFromContext instanceof NodeInterface) {
            throw new FissionException(
                'In order to use Fission in Fusion, there must be a node in the `node` context variable.'
            );
        }
        /** @var ContentContext $contentContext */
        $contentContext = $nodeFromContext->getContext();
        $this->fissionService->initializeContext(
            $contentContext,
            $this->getRuntime()->getControllerContext()
        );
        $arguments = $this->getProps();
        foreach ($arguments as $idx => $argument) {
            if ($argument instanceof NodeInterface) {
                $arguments[$idx] = new WrappedNode($argument);
            }
        }
        $result = $componentRenderer->render(
            new ComponentName($this->fusionValue("component")),
            new ComponentArguments($arguments)
        );
        return $result;
    }
}
