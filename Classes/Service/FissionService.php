<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\Twig\FissionExtension;
use Flammel\Fission\Twig\FissionRuntimeLoader;
use Flammel\Fission\Zweig\Presenter\NeosNamingConventionPresenter;
use Flammel\Zweig\Renderer\ComponentRenderer;
use Flammel\Zweig\Twig\ZweigExtension;
use Flammel\Zweig\Twig\ZweigRuntimeExtension;
use Flammel\Zweig\Twig\ZweigRuntimeLoader;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Neos\Domain\Service\ContentContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * @Flow\Scope("singleton")
 */
class FissionService
{
    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $config;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @var ComponentRenderer
     */
    private $componentRenderer;

    /**
     * @return ComponentRenderer
     */
    public function getComponentRenderer(): ComponentRenderer
    {
        if (!$this->componentRenderer instanceof ComponentRenderer) {
            $loader = $this->getLoader($this->config['templateDirectories']);
            $twig = new Environment($loader, [
                'debug' => true,
                'cache' => FLOW_PATH_DATA . 'Temporary' . DIRECTORY_SEPARATOR . 'TwigCache',
                'strict_variables' => true,
                'autoescape' => false
            ]);

            $presenter = new NeosNamingConventionPresenter();
            $componentRenderer = new ComponentRenderer($twig, $presenter);
            $twig->addRuntimeLoader(new ZweigRuntimeLoader(new ZweigRuntimeExtension($componentRenderer)));
            $twig->addExtension(new ZweigExtension());
            $twig->addRuntimeLoader(new FissionRuntimeLoader($this->objectManager));
            $twig->addExtension(new FissionExtension($this->config['functions']));

            $this->componentRenderer = $componentRenderer;
        }
        return $this->componentRenderer;
    }

    /**
     * @param ContentContext $contentContext
     * @param ControllerContext $controllerContext
     */
    public function initializeContext(ContentContext $contentContext, ControllerContext $controllerContext): void
    {
        $this->fissionContext->initialize($contentContext, $controllerContext, $this->getComponentRenderer());
    }

    /**
     * @param array $configuredPaths
     * @return LoaderInterface
     */
    protected function getLoader(array $configuredPaths): LoaderInterface
    {
        $paths = [];
        foreach ($configuredPaths as $path) {
            $paths[] = FLOW_PATH_PACKAGES . $path;
        }
        return new FilesystemLoader($paths);
    }
}
