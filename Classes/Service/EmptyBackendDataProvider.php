<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;
use Neos\ContentRepository\Domain\Model\NodeInterface;

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
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function documentInformation(): string
    {
        return '';
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    public function nodeInformation(NodeInterface $node): string
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
