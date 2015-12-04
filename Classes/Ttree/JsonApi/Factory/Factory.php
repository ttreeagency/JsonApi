<?php
namespace Ttree\JsonApi\Factory;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Ttree\JsonApi\Schema\Container;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class Factory extends \Neomerx\JsonApi\Factories\Factory
{
    public function createContainer(array $providers = [])
    {
        return new Container($this, $providers);
    }

}
