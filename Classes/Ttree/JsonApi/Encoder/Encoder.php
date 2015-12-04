<?php
namespace Ttree\JsonApi\Encoder;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Ttree\JsonApi\Factory\Factory;
use TYPO3\Flow\Annotations as Flow;

/**
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
