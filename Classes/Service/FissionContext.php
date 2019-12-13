<?php

namespace Flammel\Fission\Service;

use Flammel\Zweig\Renderer\ComponentRenderer;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;

/**
 * @Flow\Scope("singleton")
 */
class FissionContext
{
    /**
     * @var ControllerContext
     */
    private $controllerContext;

    /**
     * @var NodeInterface
     */
    private $documentNode;

    /**
     * @var NodeInterface
     */
    private $siteNode;

    /**
     * @var bool
     */
    private $inBackend;

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
     * @return ControllerContext
     */
    public function getControllerContext(): ControllerContext
    {
        return $this->controllerContext;
    }

    /**
     * @param ControllerContext $controllerContext
     */
    public function setControllerContext(ControllerContext $controllerContext): void
    {
        $this->controllerContext = $controllerContext;
    }

    /**
     * @return NodeInterface
     */
    public function getDocumentNode(): NodeInterface
    {
        return $this->documentNode;
    }

    /**
     * @param NodeInterface $documentNode
     */
    public function setDocumentNode(NodeInterface $documentNode): void
    {
        $this->documentNode = $documentNode;
    }

    /**
     * @return NodeInterface
     */
    public function getSiteNode(): NodeInterface
    {
        return $this->siteNode;
    }

    /**
     * @param NodeInterface $siteNode
     */
    public function setSiteNode(NodeInterface $siteNode): void
    {
        $this->siteNode = $siteNode;
    }

    /**
     * @return bool
     */
    public function isInBackend(): bool
    {
        return $this->inBackend;
    }

    /**
     * @param bool $inBackend
     */
    public function setInBackend(bool $inBackend): void
    {
        $this->inBackend = $inBackend;
        if ($inBackend) {
            $this->backendDataProvider = new ActiveBackendDataProvider();
        } else {
            $this->backendDataProvider = new EmptyBackendDataProvider();
        }
    }

    /**
     * @return ComponentRenderer
     */
    public function getComponentRenderer(): ComponentRenderer
    {
        return $this->componentRenderer;
    }

    /**
     * @param ComponentRenderer $componentRenderer
     */
    public function setComponentRenderer(ComponentRenderer $componentRenderer): void
    {
        $this->componentRenderer = $componentRenderer;
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

    /**
     * @param ActionRequest $actionRequest
     */
    public function setActionRequest(ActionRequest $actionRequest): void
    {
        $this->actionRequest = $actionRequest;
    }
}
