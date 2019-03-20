<?php

namespace Flowpack\JsonApi\Encoder;

use Flowpack\JsonApi\Factory\Factory;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;

/**
 * Class Encoder
 * @package Flowpack\JsonApi\Encoder
 */
class Encoder extends BaseEncoder
{
    /**
     * @return FactoryInterface
     */
    protected static function createFactory(): FactoryInterface
    {
        return new Factory();
    }
}
