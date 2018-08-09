<?php

namespace Ttree\JsonApi\Domain\Model\Concern;

use Ttree\JsonApi\Utility\StringUtility as Str;

/**
 * Trait DeserializesAttribute
 *
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
    protected $attributes = [];

    /**
     * The resource attributes that are dates.
     *
     * A list of JSON API attribute fields that should be cast to dates. If this is
     * empty, then `Model::getDates()` will be used.
     *
     * @var string[]
     */
    protected $dates = [];

    /**
     * Convert a JSON API resource field name to a model key.
     *
     * @param $field
     * @param $model
     * @return string
     */
    protected function modelKeyForField($field, $model)
    {
        if (isset($this->attributes[$field])) {
            return $this->attributes[$field];
        }

        $key = Str::camelize($field);

        return $this->attributes[$field] = $key;
    }

    /**
     * Deserialize a value obtained from the resource's attributes.
     *
     * @param $value
     *      the value that the client provided.
     * @param $field
     *      the attribute key for the value
     * @param $record
     * @return null|mixed
     */
    protected function deserializeAttribute($value, $field, $record)
    {
//        if ($this->isDateAttribute($field, $record)) {
//            return $this->deserializeDate($value, $field, $record);
//        }

        $method = 'deserialize' . Str::classify($field) . 'Field';

        if (method_exists($this, $method)) {
            return $this->{$method}($value, $record);
        }

        return $value;
    }

    /**
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

    /**
     * Is this resource key a date attribute?
     *
     * @param $field
     * @param Model $record
     * @return bool
     */
    protected function isDateAttribute($field, $record)
    {
        if (empty($this->dates)) {
            return in_array($this->modelKeyForField($field, $record), $record->getDates(), true);
        }

        return in_array($field, $this->dates, true);
    }

}
