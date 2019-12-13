<?php

namespace Flammel\Fission\Zweig\Component;

use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Component\ComponentTemplatePath;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class NeosNamingConventionComponentTemplatePath implements ComponentTemplatePath
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param ComponentName $name
     */
    public function __construct(ComponentName $name)
    {
        $parts = explode(':', $name->getName());
        if (count($parts) > 1) {
            $parts[1] = implode(DIRECTORY_SEPARATOR, explode('.', $parts[1]));
        }
        $this->path = implode(DIRECTORY_SEPARATOR, $parts) . '.twig';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
