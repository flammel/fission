<?php

namespace Flammel\Fission\Service;

use Flammel\Fission\ValueObject\WrappedNode;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Exception;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Service\LinkingService;

class NeosFunctions
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
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function translateFunction(string $key, string $package, string $sourceName = 'Main'): string
    {
        return $this->translator->translateById($key, [], [], null, $sourceName, $package) ?? $key;
    }
}
