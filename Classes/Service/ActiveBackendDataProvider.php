<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Ui\Domain\Service\ConfigurationRenderingService;
use Neos\Neos\Ui\Fusion\Helper\NodeInfoHelper;

/**
 * @Flow\Scope("singleton")
 */
class ActiveBackendDataProvider implements BackendDataProvider
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @Flow\Inject()
     * @var ConfigurationRenderingService
     */
    protected $configurationRenderingService;

    /**
     * @Flow\Inject
     * @var NodeInfoHelper
     */
    protected $nodeInfoHelper;

    /**
     * @Flow\InjectConfiguration(path="documentNodeInformation", package="Neos.Neos.Ui")
     * @var array
     */
    protected $documentInformationRenderingConfiguration;

    /**
     * @return bool
     */
    public function active(): bool
    {
        return true;
    }

    /**
     * @param WrappedNode $documentNode
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function documentInformation(WrappedNode $documentNode): string
    {
        $context = [
            'documentNode' => $documentNode->unwrap(),
            'site' => $this->fissionContext->getSiteNode(),
            'controllerContext' => $this->fissionContext->getControllerContext(),
        ];
        $data = $this->configurationRenderingService->computeConfiguration(
            $this->documentInformationRenderingConfiguration,
            $context
        );
        $json = json_encode($data, JSON_PRETTY_PRINT);
        return '<script>window["@Neos.Neos.Ui:DocumentInformation"] = ' . $json . ' </script>';
    }

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeInformation(WrappedNode $node): string
    {
        if (!$this->active()) {
            return '';
        }
        $node = $node->unwrap();
        $path = $node->getContextPath();
        $data = $this->nodeInfoHelper->renderNodeWithPropertiesAndChildrenInformation($node);
        $serializedNode = json_encode($data, JSON_PRETTY_PRINT);
        return '<script data-neos-nodedata>(function() {
            (this["@Neos.Neos.Ui:Nodes"] = this["@Neos.Neos.Ui:Nodes"] || {})["' . $path . '"] = ' . $serializedNode . '
        })();</script>';
    }

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeRootElementAttributes(WrappedNode $node): string
    {
        return ' data-__neos-node-contextpath="' . $node->unwrap()->getContextPath() . '"';
    }

    /**
     * @return string
     */
    public function unrenderedNodeInformation(): string
    {
        return '';
    }
}
