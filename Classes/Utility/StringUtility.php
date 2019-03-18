<?php

namespace Flowpack\JsonApi\Utility;

use Neos\Flow\Annotations as Flow;

/**
 * This class is meant for utility methods needed to match the classnames and properties
 * conventions, like string manipulation for propertynames and classnames.
 */
class StringUtility
{
    /**
     * @var array
     */
    protected static $classified = [];

    /**
     * This method underscores attributes camelCased properties
     *
     * @param string $string
     * @return string
     */
    public static function uncamelize($string)
    {
        return \strtolower(\preg_replace('/(.)([A-Z])/', '$1_$2', $string));
    }

    /**
     * This method camelCases attributes underscored properties
     *
     * @param string $string
     * @return string
     */
    public static function camelize($string)
    {
        return \preg_replace_callback('/-([a-z])/', function ($string) {
            return \strtoupper($string[1]);
        }, $string);
    }

    /**
     * Converts an underscored classname to UpperCamelCased
     *
     * @param string $className
     * @return string
     */
    public static function camelizeClassName($className)
    {
        return \ucfirst(
            \preg_replace_callback('/_([a-z0-9]{1})/i', function ($matches) {
                return '\\' . \strtoupper($matches[1]);
            }, $className)
        );
    }

    /**
     * Converts a UpperCamelCased classname to underscored className
     * @param string $className
     * @return string
     */
    public static function uncamelizeClassName($className)
    {
        if ($className[0] === '\\') {
            $className = \substr($className, 1);
        }
        $className = \preg_replace_callback('/\\\\([a-z0-9]{1})/i', function ($matches) {
            return '_' . \lcfirst($matches[1]);
        }, $className);
        // Prevent malformed vendor namespace
        $classParts = \explode('_', $className, 2);
        if (\strtoupper($classParts[0]) === $classParts[0]) {
            return $className;
        }
        return \lcfirst($className);
    }

    /**
     * Gets the upper camel case form of a string.
     *
     * @param string $value
     * @return string
     */
    public static function classify($value)
    {
        if (isset(self::$classified[$value])) {
            return self::$classified[$value];
        }

        $converted = \ucwords(\str_replace(['-', '_'], ' ', $value));

        return self::$classified[$value] = \str_replace(' ', '', $converted);
    }

    /**
     * @param string $word The word to pluralize
     * @return string The pluralized word
     */
    public static function pluralize($word)
    {
        return \Sho_Inflect::pluralize($word);
    }

    /**
     * Convert a model class name like "BlogAuthor" or a field name like
     * "blogAuthor" to a humanized version like "Blog author" for better readability.
     *
     * @param string $camelCased The camel cased value
     * @param boolean $lowercase Return lowercase value
     * @return string The humanized value
     */
    public static function humanizeCamelCase($camelCased, $lowercase = false)
    {
        $spacified = self::spacify($camelCased);
        $result = \strtolower($spacified);
        if (!$lowercase) {
            $result = \ucfirst($result);
        }
        return $result;
    }

    /**
     * Splits a string at lowercase/uppcase transitions and insert the glue
     * character in between.
     *
     * @param string $camelCased
     * @param string $glue
     * @return string
     */
    protected static function spacify($camelCased, $glue = ' ')
    {
        return \preg_replace('/([a-z0-9])([A-Z])/', '$1' . $glue . '$2', $camelCased);
    }
}