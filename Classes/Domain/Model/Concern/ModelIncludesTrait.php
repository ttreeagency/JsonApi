<?php

namespace Flowpack\JsonApi\Domain\Model\Concern;

use Doctrine\Common\Collections\Collection;

use Flowpack\JsonApi\Utility\StringUtility as Str;
use Neos\Eel\Helper\ArrayHelper;
use Neos\Eel\Utility;

/**
 * Trait ModelIncludesTrait
 * @package Flowpack\JsonApi\Domain\Model\Concern
 */
trait ModelIncludesTrait
{

    /**
     * The model relationships to eager load on every query.
     *
     * @var string[]
     */
    protected $defaultWith = [];

    /**
     * Mapping of JSON API include paths to model relationship paths.
     *
     * This adapter automatically maps include paths to model eager load
     * relationships. For example, if the JSON API include path is
     * `comments.created-by`, the model relationship `comments.createdBy`
     * will be eager loaded.
     *
     * If there are any paths that do not map directly, you can define them
     * on this property. For instance, if the JSON API `comments.created-by`
     * path actually relates to `comments.user` model path, you can
     * define that mapping here:
     *
     * ```php
     * protected $includePaths = [
     *   'comments.author' => 'comments.user'
     * ];
     * ```
     *
     * To prevent an include path from being eager loaded, set its value
     * to `null` in the map. E.g.
     *
     * ```php
     * protected $includePaths = [
     *   'comments.author' => null,
     * ];
     * ```
     *
     * @var array
     */
    protected $includePaths = [];

    /**
     * Get the relationship paths to eager load.
     *
     * @param Collection|array $includePaths
     *      the JSON API resource paths to be included.
     * @return array
     */
    protected function getRelationshipPaths($includePaths)
    {
        return \array_unique(\array_merge($this->convertIncludePaths($includePaths), $this->defaultWith));
    }

    /**
     * @param Collection|array $includePaths
     * @return Collection
     */
    protected function convertIncludePaths($includePaths)
    {
        $convertedIncludedPaths = [];
        foreach($includePaths as $path) {
            $convertedIncludedPaths[] = $this->convertIncludePath($path);
        }
        return $convertedIncludedPaths;
    }

    /**
     * Convert a JSON API include path to a model relationship path.
     *
     * @param $path
     * @return string|null
     */
    protected function convertIncludePath($path)
    {
        if (\array_key_exists($path, $this->includePaths)) {
            return $this->includePaths[$path] ?: null;
        }

        // TODO explode path and camelize string
    }
}
