<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Exception\RuntimeException;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;

/**
 * Class IdentifiableTrait
 */
trait IdentifiableTrait
{

    /**
     * @return string
     * @throws RuntimeException
     *      if the type member is not present, or is not a string, or is an empty string.
     */
    public function getType()
    {
        if (!$this->has(DocumentInterface::KEYWORD_TYPE)) {
            throw new RuntimeException('Type member not present.');
        }

        $type = $this->get(DocumentInterface::KEYWORD_TYPE);

        if (!is_string($type) || empty($type)) {
            throw new RuntimeException('Type member is not a string, or is empty.');
        }

        return $type;
    }

    /**
     * @return bool
     */
    public function hasType()
    {
        return $this->has(DocumentInterface::KEYWORD_TYPE);
    }

    /**
     * @return string|int
     * @throws RuntimeException
     *      if the id member is not present, or is not a string/int, or is an empty string.
     */
    public function getId()
    {
        if (!$this->has(DocumentInterface::KEYWORD_ID)) {
            throw new RuntimeException('Id member not present.');
        }

        $id = $this->get(DocumentInterface::KEYWORD_ID);

        if (!\is_string($id)) {
            throw new RuntimeException('Id member is not a string.');
        }

        if (empty($id)) {
            throw new RuntimeException('Id member is an empty string.');
        }

        return $id;
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        return $this->has(DocumentInterface::KEYWORD_ID);
    }

}
