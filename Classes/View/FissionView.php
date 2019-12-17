<?php

namespace Flammel\Fission\View;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Twig\FissionExtension;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\Twig\FissionRuntimeLoader;
use Flammel\Fission\ValueObject\WrappedNode;
use Flammel\Fission\Zweig\Presenter\NeosNamingConventionPresenter;
use Flammel\Zweig\Exception\ZweigException;
use Flammel\Zweig\Renderer\ComponentRenderer;
use Flammel\Zweig\Twig\ZweigRuntimeLoader;
use Flammel\Zweig\Twig\ZweigExtension;
use Flammel\Zweig\Twig\ZweigRuntimeExtension;
use Flammel\Zweig\Component\ComponentArguments;
use Flammel\Zweig\Component\ComponentName;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\View\AbstractView;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
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
     * @throws IllegalObjectTypeException
     * @throws ZweigException
     */
    public function render()
    {
        $start = microtime(true);
        $currentNode = $this->getCurrentNode();

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

        $this->setUpFissionContext($currentNode, $componentRenderer);
        $wrappedNode = new WrappedNode($currentNode);
        $result = $componentRenderer->render(
            new ComponentName($wrappedNode->nodeTypeName()),
            new ComponentArguments(['node' => $wrappedNode])
        );
        file_put_contents('renderperf.log', 'fission took ' . (microtime(true) - $start) . PHP_EOL, FILE_APPEND);
        return $result;
    }

    /**
     * This method is called by NodeController and therefore must be defined.
     *
     * @return bool
     */
    public function canRenderWithNodeAndPath(): bool
    {
        return true;
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
     * @throws IllegalObjectTypeException
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
        $request = $this->controllerContext->getRequest();
        if ($request instanceof ActionRequest) {
            $this->fissionContext->setActionRequest($request);
        }
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
