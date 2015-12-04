<?php
namespace Ttree\JsonApi\Domain\Model;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Ttree\JsonApi\Exception\ConfigurationException;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * Paginate Options
 */
class ResourceSettingsDefinition
{
    /**
     * @var array
     * @Flow\Inject(setting="endpoints.default.resources")
     */
    protected $settings;

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
     * @param mixed $path
     * @return mixed
     */
    protected function getPath($path)
    {
        static $ressourceSetting;
        if ($ressourceSetting === null) {
            $this->validate();
            $ressourceSetting = Arrays::getValueByPath($this->settings, $this->resource);
        }
        return Arrays::getValueByPath($ressourceSetting, $path);
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->getPath('repository');
    }

    /**
     * @return array
     */
    public function getSchemas()
    {
        return $this->getPath('schemas');
    }
}
