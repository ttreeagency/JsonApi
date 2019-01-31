<?php

namespace Ttree\JsonApi\View;

use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use Ttree\JsonApi\Domain\Model\ResourceSettingsDefinition;
use Neos\Flow\Mvc\View\AbstractView;

/**
 * Class JsonApiView
 * @package Ttree\JsonApi\View
 */
class JsonApiView extends AbstractView
{
    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FactoryInterface
     * @Flow\Inject(lazy=false)
     */
    protected $factory;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var EncodingParametersInterface
     */
    protected $parameters;

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->encoder->encodeData($this->data, $this->parameters);
    }

    /**
     * @param object $resource
     * @return SchemaProviderInterface
     */
    public function getSchema($resource)
    {
        return $this->container->getSchema($resource);
    }

    /**
     * @param EncodingParametersInterface $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $resource
     * @throws \Ttree\JsonApi\Exception\ConfigurationException
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        $resourceSettingsDefinition = new ResourceSettingsDefinition($this->resource);
        $this->container = $this->factory->createContainer($resourceSettingsDefinition->getSchemas());
    }

    /**
     * @param EncoderInterface $encoder
     */
    public function setEncoder(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}
