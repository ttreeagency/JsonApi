<?php

namespace Flowpack\JsonApi\Encoder;

use Neomerx\JsonApi\Contracts\Representation\DocumentWriterInterface;
use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;
use Flowpack\JsonApi\Factory\Factory;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Parser\DocumentDataInterface;
use Neomerx\JsonApi\Contracts\Parser\IdentifierInterface;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;

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

    /**
     * @inheritdoc
     */
    public function encodeData($data): string
    {
        // encode to json
        $array  = $this->encodeDataToArray($data);
        $result = $this->encodeToJson($array);

        return $result;
    }

    /**
     * @param object|iterable|null $data Data to encode.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function encodeDataToArray($data): array
    {
        if (\is_array($data) === false && \is_object($data) === false && $data !== null) {
            throw new InvalidArgumentException();
        }

        $parser = $this->getFactory()->createParser($this->getSchemaContainer());
        $writer = $this->createDocumentWriter();
        $filter = $this->getFactory()->createFieldSetFilter($this->getFieldSets());

        // write header
        $this->writeHeader($writer);
        // write body
        foreach ($parser->parse($data, $this->getIncludePaths()) as $item) {
            if ($item instanceof ResourceInterface) {
                if ($item->getPosition()->getLevel() > ParserInterface::ROOT_LEVEL) {
                    if ($filter->shouldOutputRelationship($item->getPosition()) === true) {
                        $writer->addResourceToIncluded($item, $filter);
                    }
                } else {
                    $writer->addResourceToData($item, $filter);
                }
            } elseif ($item instanceof IdentifierInterface) {
                \assert($item->getPosition()->getLevel() <= ParserInterface::ROOT_LEVEL);
                $writer->addIdentifierToData($item);
            } else {
                \assert($item instanceof DocumentDataInterface);
                \assert($item->getPosition()->getLevel() === 0);
                if ($item->isCollection() === true) {
                    $writer->setDataAsArray();
                } elseif ($item->isNull() === true) {
                    $writer->setNullToData();
                }
            }
        }

        // write footer
        $this->writeFooter($writer);

        $array = $writer->getDocument();

        return $array;
    }

    /**
     * @return DocumentWriterInterface
     */
    private function createDocumentWriter(): DocumentWriterInterface
    {
        $writer = $this->getFactory()->createDocumentWriter();
        $writer->setUrlPrefix($this->getUrlPrefix());

        return $writer;
    }

}
