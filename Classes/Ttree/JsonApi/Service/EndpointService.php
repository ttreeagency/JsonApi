<?php

namespace Ttree\JsonApi\Service;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Ttree\JsonApi\Contract\EndpointServiceInterface;
use Ttree\JsonApi\Contract\JsonApiRepositoryInterface;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Ttree\JsonApi\Encoder\Encoder;
use Ttree\JsonApi\Exception;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\Arrays;

/**
 * Class EndpointService
 *
 * @api
 */
class EndpointService implements EndpointServiceInterface
{
    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="endpoints.default")
     */
    protected $settings;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var EncodingParametersInterface
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param string $resource
     * @param EncodingParametersInterface $parameters
     */
    public function __construct($resource, EncodingParametersInterface $parameters)
    {
        $this->resource = $resource;
        $this->parameters = $parameters;
    }

    /**
     * @throws Exception
     */
    protected function initializeObject()
    {
        $this->initializeConfiguration();
    }

    /**
     * @param string|null $urlPrefix
     * @param integer $depth
     * @return EncoderInterface
     */
    public function getEncoder($urlPrefix = null, $depth = 512)
    {
        return Encoder::instance($this->configuration['schemas'], new EncoderOptions(JSON_PRETTY_PRINT, $urlPrefix, $depth));
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return \Neos\Flow\Persistence\QueryResultInterface
     */
    public function findAll()
    {
        $resourceSettingsDefinition = new ResourceSettingsDefinition($this->resource);
        return $this->getRepository()->findByJsonApiParameters($this->parameters, $resourceSettingsDefinition);
    }

    /**
     * @return integer
     */
    public function countAll()
    {
        return $this->getRepository()->countAll();
    }

    /**
     * @param string $identifier
     * @return object
     */
    public function findByIdentifier($identifier)
    {
        return $this->getRepository()->findByIdentifier($identifier);
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return isset($this->settings['baseUrl']) ? $this->settings['baseUrl'] : '/';
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function initializeConfiguration()
    {
        $configuration = Arrays::getValueByPath($this->settings, ['resources', $this->resource]);
        if (!is_array($configuration)) {
            throw new Exception(sprintf('Resource "%s" not configured', $this->resource), 1447947509);
        }
        $this->configuration = $configuration;
    }

    /**
     * @return object|JsonApiRepositoryInterface
     * @throws Exception
     */
    protected function getRepository()
    {
        if (!isset($this->configuration['repository'])) {
            $repository = $this->objectManager->get('Ttree\JsonApi\Domain\Repository\DefaultRepository');

            if (!isset($this->configuration['entity'])) {
                throw new Exception(sprintf('Resource "%s" no "entity" configured', $this->resource), 1447947510);
            }
            $repository->setEntityClassName($this->configuration['entity']);

            return $repository;
        }

        return $this->objectManager->get($this->configuration['repository']);
    }

}
