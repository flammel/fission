<?php

namespace Flammel\Fission\ValueObject;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Exception\NodeException;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
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
     * @return WrappedNode|null
     */
    public function child(string $key)
    {
        $child = $this->node->getNode($key);
        if (static::isWrappable($child)) {
            return new WrappedNode($child);
        }
        return null;
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

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isWrappable($value): bool
    {
        return $value instanceof NodeInterface || $value instanceof WrappedNode;
    }
}
