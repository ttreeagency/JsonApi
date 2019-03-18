<?php

namespace Flowpack\JsonApi\Factory;

use Flowpack\JsonApi\Schema\Container;
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
