<?php

namespace Ttree\JsonApi\Encoder;

use Neos\Flow\Annotations as Flow;
use Ttree\JsonApi\Factory\Factory;

/**
 * Class Encoder
 * @package Ttree\JsonApi\Encoder
 */
class Encoder extends \Neomerx\JsonApi\Encoder\Encoder
{
    /**
     * @return Factory
     */
    protected static function getFactory()
    {
        return new Factory();
    }

}
