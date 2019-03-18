<?php

namespace Flowpack\JsonApi\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\Flow\Core\ClassLoader;
use Neos\Flow\Package\PackageInterface;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Utility\Files;
use Flowpack\JsonApi\Utility\StringUtility as Str;

/**
 * Service for the JsonApi generator
 *
 */
class GeneratorService
{
    /**
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var \Neos\Flow\Package\PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var \Neos\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var array
     */
    protected $generatedFiles = [];

    /**
     * @var array
     */
    protected $namespaces = [];

    /**
     * Generate a resource from a entity for the package with the given resource and entity
     *
     * @param Package $package
     * @param string $endpoint
     * @param string $resource
     * @param string $entity
     * @param bool $overwrite Overwrite any existing files?
     * @return array An array of generated filenames
     * @throws \Neos\FluidAdaptor\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function generateResource($package, $endpoint, $resource, $entity, $overwrite = false)
    {
        $endpoint = \ucfirst($endpoint);

        $this->generateAdapter($package->getPackageKey(), $endpoint, $resource, $overwrite);
        $this->generateSchema($package->getPackageKey(), $endpoint, $resource, $entity, $overwrite);
        $this->generateTestsForResource($package->getPackageKey(), $endpoint, $resource, $entity, $overwrite);

        return $this->generatedFiles;
    }

    /**
     * Generate a repository for a model given a model name and package key
     *
     * @param string $packageKey The package key
     * @param string $endpoint
     * @param string $resource
     * @param string $entity The name of the model
     * @param boolean $overwrite Overwrite any existing files?
     * @return array An array of generated filenames
     * @throws \Neos\FluidAdaptor\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function generateAdapter($packageKey, $endpoint, $resource, $entity, $overwrite = false)
    {
        list($baseNamespace, $namespaceEntryPath) = $this->getPrimaryNamespaceAndEntryPath($this->packageManager->getPackage($packageKey));
        $resourceName = \ucfirst($resource);
        $adapterClassName = 'Adapter';
        $namespace = \trim($baseNamespace, '\\') . '\\JsonApi\\' . $endpoint . '\\' . $resourceName;

        $this->namespaces['adapter'] = $namespace . '\\' . $adapterClassName;

        $templatePathAndFilename = 'resource://Flowpack.JsonApi/Private/Generator/Adapter/AdapterTemplate.php.tmpl';

        $contextVariables = [];
        $contextVariables['packageKey'] = $packageKey;
        $contextVariables['namespace'] = $namespace;
        $contextVariables['adapterClassName'] = $adapterClassName;

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $adapterFilename = $adapterClassName . '.php';
        $adapterPath = Files::concatenatePaths([$namespaceEntryPath, 'JsonApi/' . $endpoint . '/' . $resourceName]) . '/';
        $targetPathAndFilename = $adapterPath . $adapterFilename;

        $this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

        return $this->generatedFiles;
    }

    /**
     * Generate a schema for a resource given a entity
     * @param string $packageKey
     * @param string $endpoint
     * @param string $resource
     * @param string $entity
     * @param bool $overwrite Overwrite any existing files?
     * @return array An array of generated filenames
     * @throws \Neos\FluidAdaptor\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function generateSchema($packageKey, $endpoint, $resource, $entity, $overwrite = false)
    {
        list($baseNamespace, $namespaceEntryPath) = $this->getPrimaryNamespaceAndEntryPath($this->packageManager->getPackage($packageKey));
        $entityShortName = \substr($entity, \strrpos($entity, '\\') + 1);
        $resourcePlural = Str::pluralize($resource);
        $resourceName = \ucfirst($resource);
        $schemaClassName = 'Schema';
        $namespace = \trim($baseNamespace, '\\') . '\\JsonApi\\' . $endpoint . '\\' . $resourceName;

        $this->namespaces['schema'] = $namespace . '\\' . $schemaClassName;

        $templatePathAndFilename = 'resource://Flowpack.JsonApi/Private/Generator/Schema/SchemaTemplate.php.tmpl';

        $contextVariables = [];
        $contextVariables['packageKey'] = $packageKey;
        $contextVariables['resource'] = $resource;
        $contextVariables['resourcePlural'] = $resourcePlural;
        $contextVariables['entityClassName'] = $entity;
        $contextVariables['entityShortName'] = $entityShortName;
        $contextVariables['schemaClassName'] = $schemaClassName;
        $contextVariables['namespace'] = $namespace;

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $schemaFilename = $schemaClassName . '.php';
        $schemaPath = Files::concatenatePaths([$namespaceEntryPath, 'JsonApi/' . $endpoint . '/' . $resourceName]) . '/';
        $targetPathAndFilename = $schemaPath . $schemaFilename;

        $this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

        return $this->generatedFiles;
    }

    /**
     * Generate a dummy testcase for a resource for the package and endpoint
     *
     * @param string $packageKey The package key
     * @param string $endpoint
     * @param string $resource
     * @param string $entity
     * @param bool $overwrite Overwrite any existing files?
     * @return array An array of generated filenames
     * @throws \Neos\FluidAdaptor\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function generateTestsForResource($packageKey, $endpoint, $resource, $entity, $overwrite = false)
    {
        list($baseNamespace) = $this->getPrimaryNamespaceAndEntryPath($this->packageManager->getPackage($packageKey));
        $testName = ucfirst($resource) . 'EndpointTest';
        $entityShortName = \substr($entity, \strrpos($entity, '\\') + 1);
        $resourcePlural = Str::pluralize($resource);
        $namespace = trim($baseNamespace, '\\') . '\\Tests\\Functional\\JsonApi\\' . $endpoint;

        $templatePathAndFilename = 'resource://Flowpack.JsonApi/Private/Generator/Tests/Functional/Resource/ResourceEndpointTestTemplate.php.tmpl';

        $contextVariables = [];
        $contextVariables['packageKey'] = $packageKey;
        $contextVariables['testName'] = $testName;
        $contextVariables['resource'] = $resource;
        $contextVariables['endpoint'] = $endpoint;
        $contextVariables['resourcePlural'] = $resourcePlural;
        $contextVariables['entity'] = $entity;
        $contextVariables['entityShortName'] = $entityShortName;
        $contextVariables['namespace'] = $namespace;

        $fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

        $testFilename = $testName . '.php';
        $testPath = $this->packageManager->getPackage($packageKey)->getPackagePath() . FlowPackageInterface::DIRECTORY_TESTS_FUNCTIONAL . 'JsonApi/' . $endpoint . '/';
        $targetPathAndFilename = $testPath . $testFilename;

        $this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

        return $this->generatedFiles;
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getNamespacesEntry($entry)
    {
        if (isset($this->namespaces[$entry])) {
            return $this->namespaces[$entry];
        }
    }

    /**
     * Normalize types and prefix types with namespaces
     *
     * @param array $fieldDefinitions The field definitions
     * @param string $namespace The namespace
     * @return array The normalized and type converted field definitions
     */
    protected function normalizeFieldDefinitions(array $fieldDefinitions, $namespace = '')
    {
        foreach ($fieldDefinitions as &$fieldDefinition) {
            if ($fieldDefinition['type'] == 'bool') {
                $fieldDefinition['type'] = 'boolean';
            } elseif ($fieldDefinition['type'] == 'int') {
                $fieldDefinition['type'] = 'integer';
            } elseif (preg_match('/^[A-Z]/', $fieldDefinition['type'])) {
                if (class_exists($fieldDefinition['type'])) {
                    $fieldDefinition['type'] = '\\' . $fieldDefinition['type'];
                } else {
                    $fieldDefinition['type'] = '\\' . $namespace . '\\' . $fieldDefinition['type'];
                }
            }
        }
        return $fieldDefinitions;
    }

    /**
     * Generate a file with the given content and add it to the
     * generated files
     *
     * @param string $targetPathAndFilename
     * @param string $fileContent
     * @param boolean $force
     * @return void
     * @throws \Neos\Utility\Exception\FilesException
     */
    protected function generateFile($targetPathAndFilename, $fileContent, $force = false)
    {
        if (!is_dir(dirname($targetPathAndFilename))) {
            \Neos\Utility\Files::createDirectoryRecursively(dirname($targetPathAndFilename));
        }

        if (substr($targetPathAndFilename, 0, 11) === 'resource://') {
            list($packageKey, $resourcePath) = explode('/', substr($targetPathAndFilename, 11), 2);
            $relativeTargetPathAndFilename = $packageKey . '/Resources/' . $resourcePath;
        } elseif (strpos($targetPathAndFilename, 'Tests') !== false) {
            $relativeTargetPathAndFilename = substr($targetPathAndFilename, strrpos(substr($targetPathAndFilename, 0, strpos($targetPathAndFilename, 'Tests/') - 1), '/') + 1);
        } else {
            $relativeTargetPathAndFilename = substr($targetPathAndFilename, strrpos(substr($targetPathAndFilename, 0, strpos($targetPathAndFilename, 'Classes/') - 1), '/') + 1);
        }

        if (!file_exists($targetPathAndFilename) || $force === true) {
            file_put_contents($targetPathAndFilename, $fileContent);
            $this->generatedFiles[] = 'Created .../' . $relativeTargetPathAndFilename;
        } else {
            $this->generatedFiles[] = 'Omitted as file already exists .../' . $relativeTargetPathAndFilename;
        }
    }

    /**
     * Render the given template file with the given variables
     *
     * @param string $templatePathAndFilename
     * @param array $contextVariables
     * @return string
     * @throws \Neos\FluidAdaptor\Exception
     */
    protected function renderTemplate($templatePathAndFilename, array $contextVariables)
    {
        $standaloneView = new StandaloneView();
        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assignMultiple($contextVariables);
        return $standaloneView->render();
    }

    /**
     * @param PackageInterface $package
     * @return array
     */
    protected function getPrimaryNamespaceAndEntryPath(PackageInterface $package)
    {
        $autoloadConfigurations = $package->getComposerManifest('autoload');

        $firstAutoloadType = null;
        $firstAutoloadConfiguration = null;
        foreach ($autoloadConfigurations as $autoloadType => $autoloadConfiguration) {
            if (ClassLoader::isAutoloadTypeWithPredictableClassPath($autoloadType)) {
                $firstAutoloadType = $autoloadType;
                $firstAutoloadConfiguration = $autoloadConfiguration;
                break;
            }
        }

        $autoloadPaths = reset($firstAutoloadConfiguration);
        $firstAutoloadPath = is_array($autoloadPaths) ? reset($autoloadPaths) : $autoloadPaths;
        $namespace = key($firstAutoloadConfiguration);
        $autoloadPathPostfix = '';
        if ($firstAutoloadType === ClassLoader::MAPPING_TYPE_PSR0) {
            $autoloadPathPostfix = str_replace('\\', '/', trim($namespace, '\\'));
        }

        return [
            $namespace,
            Files::concatenatePaths([$package->getPackagePath(), $firstAutoloadPath, $autoloadPathPostfix]),
            $firstAutoloadType,
        ];
    }
}
