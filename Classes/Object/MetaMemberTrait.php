<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;

/**
 * Class MetaMemberTrait
 *
 */
trait MetaMemberTrait
{

    /**
     * Get the meta member of the document.
     *
     * @return StandardObjectInterface
     * @throws RuntimeException
     *      if the meta member is present and is not an object or null.
     */
    public function getMeta()
    {
        $meta = $this->hasMeta() ? $this->get(DocumentInterface::KEYWORD_META) : new StandardObject();

        if (!\is_null($meta) && !$meta instanceof StandardObjectInterface) {
            throw new RuntimeException('Data member is not an object.');
        }

        return $meta;
    }

    /**
     * @return bool
     */
    public function hasMeta()
    {
        return $this->has(DocumentInterface::KEYWORD_META);
    }

}
