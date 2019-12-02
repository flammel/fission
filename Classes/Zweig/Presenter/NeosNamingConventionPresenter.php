<?php

namespace Flammel\Fission\Zweig\Presenter;

use Flammel\Fission\Zweig\TemplatePath\NeosNamingConventionTemplatePath;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentContext;
use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Presenter\Presentable;
use Flammel\Zweig\Presenter\Presenter;

final class NeosNamingConventionPresenter implements Presenter
{
    /**
     * @param ComponentName $name
     * @param ComponentArguments $arguments
     * @return Presentable
     */
    public function present(ComponentName $name, ComponentArguments $arguments): Presentable
    {
        return new Presentable(
            new NeosNamingConventionTemplatePath($name),
            new ComponentContext($arguments->getArguments())
        );
    }
}
