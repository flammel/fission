<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;
use Neos\ContentRepository\Domain\Model\NodeInterface;

interface BackendDataProvider
{
    /**
     * @return bool
     */
    public function active(): bool;

    /**
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function documentInformation(): string;

    /**
     * @param NodeInterface $node
     * @return string
     */
    public function nodeInformation(NodeInterface $node): string;

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeRootElementAttributes(WrappedNode $node): string;

    /**
     * @return string
     */
    public function unrenderedNodeInformation(): string;
}
