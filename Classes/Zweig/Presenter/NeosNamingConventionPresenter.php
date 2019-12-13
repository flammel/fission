<?php

namespace Flammel\Fission\Zweig\Presenter;

use Flammel\Fission\Zweig\Component\NeosNamingConventionComponentTemplatePath;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentContext;
use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Component\Component;
use Flammel\Zweig\Presenter\Presenter;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class NeosNamingConventionPresenter implements Presenter
{
    /**
     * @param ComponentName $name
     * @param ComponentArguments $arguments
     * @return Component
     */
    public function present(ComponentName $name, ComponentArguments $arguments): Component
    {
        return new Component(
            new NeosNamingConventionComponentTemplatePath($name),
            new ComponentContext($arguments->toArray())
        );
    }
}
