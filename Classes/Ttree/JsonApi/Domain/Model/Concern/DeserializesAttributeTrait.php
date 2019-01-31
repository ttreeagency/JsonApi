<?php

namespace Ttree\JsonApi\Domain\Model\Concern;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Ttree\JsonApi\Utility\StringUtility as Str;

/**
 * Trait DeserializesAttribute
 */
trait DeserializesAttributeTrait
{

    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * By default, JSON API attribute fields will automatically be converted to the
     * underscored or camel cased equivalent for the model key. E.g. if the model
     * uses snake case, the JSON API field `published-at` will be converted
     * to `published_at`. If the model does not use snake case, it will be converted
     * to `publishedAt`.
     *
     * For any fields that do not directly convert to model keys, you can list them
     * here. For example, if the JSON API field `published-at` needed to map to the
     * `published_date` model key, then it can be listed as follows:
     *
     * ```php
     * protected $attributes = [
     *   'published-at' => 'published_date',
     * ];
     * ```
     *
     * @var array
     */
    protected $attributeMapping = [];

    /**
     * @Flow\Inject()
     * @var \Neos\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * Convert a JSON API resource field name to a model key.
     *
     * @param string $attribute
     * @return string
     */
    protected function attributeToProperty($attribute)
    {
        if (isset($this->attributeMapping[$attribute])) {
            return $this->attributeMapping[$attribute];
        }

        $property = Str::camelize($attribute);

        return $this->attributeMapping[$attribute] = $property;
    }

    /**
     * Deserialize a value obtained from the resource's attributes.
     *
     * @param $value the value that the client provided.
     * @param $field the attribute key for the value
     * @param $record
     * @return null|mixed
     */
    protected function deserializeAttribute($value, $field, $record)
    {
//        \Neos\Flow\var_dump($this->reflectionService->getClassPropertyNames(\get_class($record)));
//        \Neos\Flow\var_dump($this->reflectionService->getPropertyTagsValues(\get_class($record), $field));
//        \Neos\Flow\var_dump($this->reflectionService->getPropertyNamesByTag(\get_class($record), 'var'));
//        exit();
//
//        if (method_exists($record))
//        \Neos\Flow\var_dump($this->reflectionService->getMethodTagsValues(\get_class($record), 'set'. ucfirst($field)), 'test');
//        exit();
//
//
//
//        if ($this->isDateAttribute($field, $record)) {
//            return $this->deserializeDate($value, $field, $record);
//        }
        // TODO do someting with property mapping

        $method = 'deserialize' . Str::classify($field) . 'Field';

        if (method_exists($this, $method)) {
            return $this->{$method}($value, $record);
        }

        return $value;
    }

    /**
     * @todo
     * Convert a JSON date into a PHP date time object.
     *
     * @param $value
     *      the value in the JSON API resource attribute field.
     * @param string $field
     *      the JSON API field name being deserialized.
     * @param Model $record
     *      the domain record being filled.
     * @return Carbon|null
     */
    protected function deserializeDate($value, $field, $record)
    {
        return !is_null($value) ? new Carbon($value) : null;
    }
}