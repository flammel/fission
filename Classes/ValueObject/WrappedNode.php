<?php

namespace Flammel\Fission\ValueObject;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Exception\NodeException;

final class WrappedNode
{
    /**
     * @var NodeInterface
     */
    private $node;

    /**
     * @param NodeInterface|WrappedNode $node
     */
    public function __construct($node)
    {
        $this->node = $node instanceof NodeInterface ? $node : $node->unwrap();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws NodeException
     */
    public function prop(string $key)
    {
        return $this->node->getProperty($key);
    }

    /**
     * @param string $key
     * @return WrappedNode
     */
    public function child(string $key)
    {
        return new WrappedNode($this->node->getNode($key));
    }

    /**
     * @return array
     */
    public function children()
    {
        $wrapped = [];
        foreach ($this->node->getChildNodes() as $child) {
            $wrapped[] = new WrappedNode($child);
        }
        return $wrapped;
    }

    /**
     * @return NodeInterface
     */
    public function unwrap(): NodeInterface
    {
        return $this->node;
    }

    /**
     * @return string
     */
    public function contextPath(): string
    {
        return $this->node->getContextPath();
    }

    /**
     * @return string
     */
    public function nodeTypeName(): string
    {
        return $this->node->getNodeType()->getName();
    }
}
