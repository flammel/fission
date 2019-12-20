<?php

namespace Flammel\Fission\Service;

use Flammel\Zweig\Renderer\ComponentRenderer;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Neos\Domain\Service\ContentContext;

/**
 * @Flow\Scope("singleton")
 */
class FissionContext
{
    /**
     * @var ContentContext
     */
    private $contentContext;

    /**
     * @var ControllerContext
     */
    private $controllerContext;

    /**
     * @var NodeInterface
     */
    private $siteNode;

    /**
     * @var ComponentRenderer
     */
    private $componentRenderer;

    /**
     * @var BackendDataProvider
     */
    private $backendDataProvider;

    /**
     * @var ActionRequest
     */
    private $actionRequest;

    /**
     * @param ContentContext $contentContext
     * @param ControllerContext $controllerContext
     * @param ComponentRenderer $componentRenderer
     */
    public function initialize(
        ContentContext $contentContext,
        ControllerContext $controllerContext,
        ComponentRenderer $componentRenderer
    ): void {
        $this->contentContext = $contentContext;
        $this->controllerContext = $controllerContext;
        $this->siteNode = $contentContext->getCurrentSiteNode();
        $this->componentRenderer = $componentRenderer;
        $request = $controllerContext->getRequest();
        if ($request instanceof ActionRequest) {
            $this->actionRequest = $request;
        }

        try {
            $inBackend = $contentContext->isInBackend();
        } catch (IllegalObjectTypeException $e) {
            $inBackend = false;
        }
        if ($inBackend) {
            $this->backendDataProvider = new ActiveBackendDataProvider();
        } else {
            $this->backendDataProvider = new EmptyBackendDataProvider();
        }
    }

    /**
     * @return ControllerContext
     */
    public function getControllerContext(): ControllerContext
    {
        return $this->controllerContext;
    }

    /**
     * @return NodeInterface
     */
    public function getSiteNode(): NodeInterface
    {
        return $this->siteNode;
    }

    /**
     * @return ComponentRenderer
     */
    public function getComponentRenderer(): ComponentRenderer
    {
        return $this->componentRenderer;
    }

    /**
     * @return BackendDataProvider
     */
    public function getBackendDataProvider()
    {
        return $this->backendDataProvider;
    }

    /**
     * @return ActionRequest
     */
    public function getActionRequest(): ActionRequest
    {
        return $this->actionRequest;
    }
}
