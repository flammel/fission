<?php

namespace Flammel\Fission\Twig;

use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class FissionRuntimeLoader implements RuntimeLoaderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates the runtime implementation of a Twig element (filter/function/test).
     *
     * @param string $class
     * @return object|null
     */
    public function load(string $class)
    {
        if ($this->objectManager->has($class)) {
            return $this->objectManager->get($class);
        }
        return null;
    }
}
