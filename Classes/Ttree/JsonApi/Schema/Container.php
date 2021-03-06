<?php
namespace Ttree\JsonApi\Schema;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\TypeHandling;

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
