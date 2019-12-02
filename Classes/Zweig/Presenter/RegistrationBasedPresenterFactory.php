<?php

namespace Flammel\Fission\Zweig\Presenter;

use Flammel\Zweig\Component\ComponentName;
use Flammel\Zweig\Presenter\Presenter;
use Flammel\Zweig\Presenter\PresenterFactory;

final class RegistrationBasedPresenterFactory implements PresenterFactory
{
    /**
     * @var Presenter
     */
    private $defaultPresenter;

    /**
     * @var array<string, Presenter>
     */
    private $registry;

    public function __construct(Presenter $defaultPresenter)
    {
        $this->defaultPresenter = $defaultPresenter;
        $this->registry = [];
    }

    /**
     * @param ComponentName $componentName
     * @param Presenter $presenter
     */
    public function register(ComponentName $componentName, Presenter $presenter): void
    {
        $this->registry[$componentName->getName()] = $presenter;
    }

    /**
     * @param ComponentName $componentName
     * @return Presenter
     */
    public function getPresenter(ComponentName $componentName): Presenter
    {
        if (isset($this->registry[$componentName->getName()])) {
            return $this->registry[$componentName->getName()];
        } else {
            return $this->defaultPresenter;
        }
    }
}
