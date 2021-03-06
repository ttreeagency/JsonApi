<?php
namespace Ttree\JsonApi\Service;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Ttree\JsonApi\Contract\EndpointServiceInterface;
use Ttree\JsonApi\Contract\JsonApiRepositoryInterface;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Ttree\JsonApi\Encoder\Encoder;
use Ttree\JsonApi\Exception;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Utility\Arrays;

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
     * @Flow\Inject(setting="endpoints.default")
     */
    protected $settings;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param string $resource
     * @param ParametersInterface $parameters
     */
    public function __construct($resource, ParametersInterface $parameters)
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
     * @return \TYPO3\Flow\Persistence\QueryResultInterface
     */
    public function findAll()
    {
        $resourceSettingsDefinition = new ResourceSettingsDefinition($this->resource);
        return $this->getRepository()->findByJsonApiParameters($this->parameters, $resourceSettingsDefinition);
    }

    /**
     * @return integer
     */
    public function countAll() {
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
     * @return JsonApiRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->objectManager->get($this->configuration['repository']);
    }

}
