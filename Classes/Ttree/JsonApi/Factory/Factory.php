<?php

namespace Ttree\JsonApi\Factory;

use Ttree\JsonApi\Schema\Container;
use Neos\Flow\Annotations as Flow;

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
