<?php
namespace Flowpack\JsonApi\Domain\Model;

use Neos\Flow\Annotations as Flow;
use InvalidArgumentException;
use Flowpack\JsonApi\Exception\ConfigurationException;
use Neos\Utility\Arrays;

/**
 * @todo
 * ResourceSettingsDefinition
 */
class ResourceSettingsDefinition
{
    /**
     * @Flow\InjectConfiguration(path="endpoints.default.resources")
     * @var array
     */
    protected $settings = array();

    /**
     * @var string
     */
    protected $resource;

    /**
     * @param string $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Validate resource settings
     * @throws ConfigurationException
     */
    public function validate()
    {
        if (!isset($this->settings[$this->resource]) || !is_array($this->settings[$this->resource])) {
            throw new ConfigurationException('Configuration for resource "%s" not found', 1449128938);

        }
        $resourceSetting = $this->settings[$this->resource];
        if (!isset($resourceSetting['schemas']) || !is_array($resourceSetting['schemas']) || $resourceSetting['schemas'] === []) {
            throw new ConfigurationException('Configuration for resource "%s" must contain at least on Schema mapping', 1449128959);
        }
    }

    /**
     * @param $path
     * @return mixed
     * @throws ConfigurationException
     */
    protected function getPath($path)
    {
        static $resourceSetting;
        if ($resourceSetting === null) {
            $this->validate();
            $resourceSetting = Arrays::getValueByPath($this->settings, $this->resource);
        }
        return Arrays::getValueByPath($resourceSetting, $path);
    }

    /**
     * @return mixed
     * @throws ConfigurationException
     */
    public function getRepository()
    {
        return $this->getPath('repository');
    }

    /**
     * @return mixed
     * @throws ConfigurationException
     */
    public function getSchemas()
    {
        return $this->getPath('schemas');
    }

    /**
     * @return mixed
     * @throws ConfigurationException
     */
    public function getSortableAttributes()
    {
        return $this->getPath('sortableAttributes');
    }

    /**
     * @param $attributeName
     * @return string
     * @throws ConfigurationException
     */
    public function convertSortableAttributes($attributeName): string
    {
        $attributes = $this->getPath('sortableAttributes');
        if (!isset($attributes[$attributeName])) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" not found', $attributeName), 1449251926);
        }
        return trim($attributes[$attributeName]);
    }
}
