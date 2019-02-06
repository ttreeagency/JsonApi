<?php

namespace Ttree\JsonApi\Encoder;

use Neos\Flow\Annotations as Flow;
use Ttree\JsonApi\Factory\Factory;
use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;

/**
 * Class Encoder
 * @package Ttree\JsonApi\Encoder
 */
class Encoder extends BaseEncoder
{
    /**
     * @return Factory
     */
    protected function getFactory(): FactoryInterface
    {
        return new Factory();
    }

}
