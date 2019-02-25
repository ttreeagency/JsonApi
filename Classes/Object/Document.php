<?php

namespace Ttree\JsonApi\Object;

use Ttree\JsonApi\Contract\Object\DocumentInterface;
use Ttree\JsonApi\Contract\Object\StandardObjectInterface;
use Ttree\JsonApi\Exception\RuntimeException;
use Ttree\JsonApi\Document\Error;

/**
 * Class Document
 * @package Ttree\JsonApi\Object
 */
class Document extends StandardObject implements DocumentInterface
{

    use MetaMemberTrait;

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (!$this->has(self::DATA)) {
            throw new RuntimeException('Data member is not present.');
        }

        $data = $this->get(self::DATA);

        if (\is_array($data) || \is_null($data)) {
            return $data;
        }

        if ($data instanceof StandardObjectInterface) {
            throw new RuntimeException('Data member is not an object or null.');
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getResource()
    {
        $data = $this->{self::DATA};

        if (!\is_object($data)) {
            throw new RuntimeException('Data member is not an object.');
        }

        return new ResourceObject($data);
    }

    /**
     * @inheritDoc
     */
    public function getResources()
    {
        $data = $this->get(self::DATA);

        if (!\is_array($data)) {
            throw new RuntimeException('Data member is not an array.');
        }

        return ResourceObjectCollection::create($data);
    }

    /**
     * @inheritdoc
     */
    public function getRelationship()
    {
        return new Relationship($this->proxy);
    }

    /**
     * @inheritDoc
     */
    public function getIncluded()
    {
        if (!$this->has(self::INCLUDED)) {
            return null;
        }

        if (!\is_array($data = $this->{self::INCLUDED})) {
            throw new RuntimeException('Included member is not an array.');
        }

        return ResourceObjectCollection::create($data);
    }

    /**
     * @inheritDoc
     */
    public function getErrors()
    {
        if (!$this->has(self::ERRORS)) {
            return null;
        }

        if (!\is_array($data = $this->{self::ERRORS})) {
            throw new RuntimeException('Errors member is not an array.');
        }

        return Error::createMany($data);
    }

}
