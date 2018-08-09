<?php

namespace Ttree\JsonApi\Mvc\Routing;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\DynamicRoutePart;
use Ttree\JsonApi\Exception\ConfigurationException;

/**
 * Class ModelNameRoutePart
 * @package Ttree\JsonApi\Routing
 */
class VersionRoutePartHandler extends DynamicRoutePart
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $availableEndpoints;

    /**
     * Inject the settings
     * @param array $settings
     * @throws ConfigurationException
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
        if (!isset($this->settings['endpoints'])) {
            throw new ConfigurationException('Missing Endpoints configuration.');
        }

        $this->availableEndpoints = $this->settings['endpoints'];
    }

    /**
     * @param string $value
     * @return boolean
     */
    protected function matchValue($value)
    {
        if ($value === null || $value === '') {
            return false;
        }

        foreach ($this->availableEndpoints as $endpointKey => $endpointConfiguration) {
            if (isset($endpointConfiguration['version']) && $endpointConfiguration['version'] === $value) {
//                $this->setName('endpoint');
//                $this->value = $endpointKey;
                return true;
            }
        }

        return false;
    }

}