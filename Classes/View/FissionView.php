<?php

namespace Flammel\Fission\View;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Twig\FissionExtension;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\Twig\FissionRuntimeLoader;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Fission\Zweig\Presenter\NeosNamingConventionPresenter;
use Flammel\Fission\Zweig\Presenter\RegistrationBasedPresenterFactory;
use Flammel\Zweig\Presenter\Presenter;
use Flammel\Zweig\Presenter\PresenterFactory;
use Flammel\Zweig\Renderer\ComponentRenderer;
use Flammel\Zweig\Twig\ZweigRuntimeLoader;
use Flammel\Zweig\Twig\ZweigExtension;
use Flammel\Zweig\Twig\ZweigRuntimeExtension;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\AbstractView;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Neos\Domain\Service\ContentContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class FissionView extends AbstractView
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $config;

    /**
     * @return string
     * @throws FissionException
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render()
    {
        $currentNode = $this->getCurrentNode();

        $loader = $this->getLoader($this->config['templateDirectories']);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => FLOW_PATH_DATA . 'Temporary' . DIRECTORY_SEPARATOR . 'TwigCache',
            'strict_variables' => true,
            'autoescape' => false
        ]);

        $presenterFactory = $this->getPresenterFactory($this->config['presenters']);
        $componentRenderer = new ComponentRenderer($twig, $presenterFactory);
        $twig->addRuntimeLoader(new ZweigRuntimeLoader(new ZweigRuntimeExtension($componentRenderer)));
        $twig->addExtension(new ZweigExtension());
        $twig->addRuntimeLoader(new FissionRuntimeLoader($this->objectManager));
        $twig->addExtension(new FissionExtension());

        $this->setUpFissionContext($currentNode, $componentRenderer);
        $wrappedNode = new WrappedNode($currentNode);
        $result = $componentRenderer->render(
            new ComponentName($wrappedNode->nodeTypeName()),
            new ComponentArguments([$wrappedNode])
        );
        return $result;
    }

    protected function getLoader(array $configuredPaths): LoaderInterface
    {
        $paths = [];
        foreach ($configuredPaths as $path) {
            $paths[] = FLOW_PATH_PACKAGES . $path;
        }
        return new FilesystemLoader($paths);
    }

    /**
     * @param NodeInterface $currentNode
     * @param ComponentRenderer $componentRenderer
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    protected function setUpFissionContext(NodeInterface $currentNode, ComponentRenderer $componentRenderer): void
    {
        /** @var ContentContext $contentContext */
        $contentContext = $currentNode->getContext();
        $this->fissionContext->setControllerContext($this->controllerContext);
        $this->fissionContext->setDocumentNode($currentNode);
        $this->fissionContext->setInBackend($contentContext->isInBackend());
        $this->fissionContext->setSiteNode($contentContext->getCurrentSiteNode());
        $this->fissionContext->setComponentRenderer($componentRenderer);
        $this->fissionContext->setActionRequest($this->controllerContext->getRequest());
    }

    /**
     * @param array $configuredPresenters
     * @return PresenterFactory
     * @throws FissionException
     */
    protected function getPresenterFactory(array $configuredPresenters): PresenterFactory
    {
        $presenterFactory = new RegistrationBasedPresenterFactory(new NeosNamingConventionPresenter());
        foreach ($configuredPresenters as $component => $presenterClass) {
            $presenter = $this->objectManager->get($presenterClass);
            if ($presenter instanceof Presenter) {
                $presenterFactory->register(new ComponentName($component), $presenter);
            } else {
                throw new FissionException(
                    'Configured presenter ' . $presenterClass . ' does not implement Presenter interface'
                );
            }
        }
        return $presenterFactory;
    }

    /**
     * @return NodeInterface
     * @throws FissionException
     */
    protected function getCurrentNode(): NodeInterface
    {
        $currentNode = isset($this->variables['value']) ? $this->variables['value'] : null;
        if (!$currentNode instanceof NodeInterface) {
            throw new FissionException('FissionView needs a variable \'value\' set with a Node object.');
        }
        return $currentNode;
    }
}
