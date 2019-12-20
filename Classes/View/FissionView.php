<?php

namespace Flammel\Fission\View;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\FissionService;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Zweig\Exception\ZweigException;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\AbstractView;
use Neos\Neos\Domain\Service\ContentContext;

class FissionView extends AbstractView
{
    /**
     * @Flow\Inject
     * @var FissionService
     */
    protected $fissionService;

    /**
     * @return string
     * @throws FissionException
     * @throws ZweigException
     */
    public function render()
    {
        $currentNode = $this->getCurrentNode();
        $componentRenderer = $this->fissionService->getComponentRenderer();
        /** @var ContentContext $contentContext */
        $contentContext = $currentNode->getContext();
        $this->fissionService->initializeContext($contentContext, $this->controllerContext);
        $wrappedNode = new WrappedNode($currentNode);
        $result = $componentRenderer->render(
            new ComponentName($wrappedNode->nodeTypeName()),
            new ComponentArguments(['node' => $wrappedNode])
        );

        return $result;
    }

    /**
     * This method must be defined because it is called by the NodeController.
     *
     * @return bool
     */
    public function canRenderWithNodeAndPath(): bool
    {
        return true;
    }

    /**
     * @return NodeInterface
     * @throws FissionException
     */
    protected function getCurrentNode(): NodeInterface
    {
        $currentNode = isset($this->variables['value']) ? $this->variables['value'] : null;
        if (!$currentNode instanceof NodeInterface) {
            throw new FissionException('FissionView needs a variable \'value\' set with a Node object.');
        }
        return $currentNode;
    }
}
