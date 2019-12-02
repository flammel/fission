<?php

namespace Flammel\Fission\Zweig\TemplatePath;

use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\TemplatePath\TemplatePath;

final class NeosNamingConventionTemplatePath implements TemplatePath
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

    public function getPath(): string
    {
        return $this->path;
    }
}
