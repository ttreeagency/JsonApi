<?php
namespace Ttree\JsonApi\Schema;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\TypeHandling;

/**
 * @Flow\Scope("singleton")
 */
class Container extends \Neomerx\JsonApi\Schema\Container
{
    /**
     * @param object $resource
     *
     * @return string
     */
    protected function getResourceType($resource)
    {
        return TypeHandling::getTypeForValue($resource);
    }
}
