<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;

class EmptyBackendDataProvider implements BackendDataProvider
{
    /**
     * @return bool
     */
    public function active(): bool
    {
        return false;
    }

    /**
     * @param WrappedNode $documentNode
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function documentInformation(WrappedNode $documentNode): string
    {
        return '';
    }

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeInformation(WrappedNode $node): string
    {
        return '';
    }

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeRootElementAttributes(WrappedNode $node): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function unrenderedNodeInformation(): string
    {
        return '';
    }
}
