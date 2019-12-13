<?php

namespace Flammel\Fission\Functions;

use Flammel\Fission\Exception\FissionException;
use Flammel\Fission\Service\BackendDataProvider;
use Flammel\Fission\Service\FissionContext;
use Flammel\Fission\ValueObject\WrappedNode;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Exception;
use Neos\Flow\I18n\Exception\IndexOutOfBoundsException;
use Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Service\LinkingService;

/**
 * @Flow\Scope("singleton")
 */
class Fission implements FissionFunction
{
    /**
     * @Flow\Inject
     * @var FissionContext
     */
    protected $fissionContext;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @param mixed ...$args
     * @return mixed|void
     * @throws FissionException
     */
    public function invoke(...$args)
    {
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function dump($value): void
    {
        \Neos\Flow\var_dump($value);
    }

    /**
     * @param mixed $node
     * @return WrappedNode
     */
    public function wrap($node): WrappedNode
    {
        return new WrappedNode($node);
    }

    /**
     * @return BackendDataProvider
     */
    public function backend(): BackendDataProvider
    {
        return $this->fissionContext->getBackendDataProvider();
    }

    /**
     * @return WrappedNode
     */
    public function siteNode(): WrappedNode
    {
        return new WrappedNode($this->fissionContext->getSiteNode());
    }

    /**
     * @return WrappedNode
     */
    public function documentNode(): WrappedNode
    {
        return new WrappedNode($this->fissionContext->getDocumentNode());
    }

    /**
     * @param mixed $node
     * @return string
     */
    public function nodeRoot($node): string
    {
        return $this->fissionContext->getBackendDataProvider()->nodeRootElementAttributes(new WrappedNode($node));
    }

    /**
     * @param mixed $node
     * @return string
     */
    public function nodeUri($node): string
    {
        $node = (new WrappedNode($node))->unwrap();
        try {
            return $this->linkingService->createNodeUri(
                $this->fissionContext->getControllerContext(),
                $node
            );
        } catch (Exception $e) {
        } catch (MissingActionNameException $e) {
        } catch (\Neos\Flow\Property\Exception $e) {
        } catch (\Neos\Flow\Security\Exception $e) {
        } catch (\Neos\Neos\Exception $e) {
        }
        return 'uri node failed TODO';
    }

    /**
     * @param mixed $resource
     * @return string
     */
    public function resourceUri($resource): string
    {
        if ($resource instanceof PersistentResource) {
            $uri = $this->resourceManager->getPublicPersistentResourceUri($resource);
            if (is_string($uri)) {
                return $uri;
            }
        }
        return 'resource uri failed TODO';
    }

    /**
     * @param string $key
     * @param string $package
     * @param string $sourceName
     * @return string
     * @throws IndexOutOfBoundsException
     * @throws InvalidFormatPlaceholderException
     */
    public function translate(string $key, string $package, string $sourceName = 'Main'): string
    {
        return $this->translator->translateById($key, [], [], null, $sourceName, $package) ?? $key;
    }
}
