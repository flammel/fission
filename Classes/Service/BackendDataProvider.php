<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;

interface BackendDataProvider
{
    /**
     * @return bool
     */
    public function active(): bool;

    /**
     * @param WrappedNode $documentNode
     * @return string
     */
    public function documentInformation(WrappedNode $documentNode): string;

    /**
     * @param WrappedNode $node
     * @return string
     */
    public function nodeInformation(WrappedNode $node): string;

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
