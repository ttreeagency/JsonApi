<?php

namespace Flowpack\JsonApi\Contract\Object;

use Flowpack\JsonApi\Exception\RuntimeException;

/**
 * Interface MetaMemberInterface
 */
interface MetaMemberInterface
{

    /**
     * Get the meta member of the object.
     *
     * @return StandardObjectInterface
     * @throws RuntimeException
     *      if the meta member is present and is not an object.
     */
    public function getMeta();

    /**
     * Does the object have meta?
     *
     * @return bool
     */
    public function hasMeta();

}
