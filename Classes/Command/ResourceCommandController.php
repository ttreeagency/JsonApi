<?php

namespace Ttree\JsonApi\Command;

/*
 * This file is part of the Ttree.JsonApi package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package;
use Neos\Flow\Reflection\ReflectionService;
use Ttree\JsonApi\Service\GeneratorService;
use Ttree\JsonApi\Utility\StringUtility as Str;

/**
 * @Flow\Scope("singleton")
 */
class ResourceCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var GeneratorService
     */
    protected $generatorService;

    /**
     * @Flow\InjectConfiguration(package="Ttree.JsonApi", path="endpoints")
     * @var array
     */
    protected $endpoints;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * Kickstart a new resource
     *
     * The default behavior is to not overwrite any existing code. This can be
     * overridden by specifying the --force flag.
     *
     * @param string $resource
     * @param string $entity
     * @param bool $force Overwrite any existing controller or template code. Regardless of this flag, the resource will never be overwritten.
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\FluidAdaptor\Exception
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function createCommand($resource, $entity, $force = false)
    {
        $this->isEntityFound($entity);

        $endpointChoices = \array_keys($this->endpoints);
        $selectedEndpoint = $this->output->select('Select one of these endpoints to create a resource in:', $endpointChoices);
        $this->outputLine();

        $packages = [];
        $choices = [];
        /** @var Package $package */
        foreach ($this->packageManager->getAvailablePackages() as $package) {
            $type = $package->getComposerManifest('type');
            if ($type === null || (\strpos($type, 'typo3-') !== 0 && \strpos($type, 'neos-') !== 0 || \strpos($type, 'neos-framework') === 0)) {
                continue;
            }

            $choices[] = $package->getPackageKey();
            $packages[$package->getPackageKey()] = $package;
        }

        $selectedPackage = $this->output->select('Select one of these packages to create a resource in:', $choices);
        $this->outputLine();

        if ($this->isReservedWord($selectedEndpoint) || $this->isReservedWord($resource) || $this->isReservedWord($entity)) {
            $this->outputLine('One of the chosen options aren\'t possible cause its a reserved word by PHP (see for more information http://php.net/manual/en/reserved.keywords.php).');
            $this->outputLine('- Endpoint : %s - %s', [$selectedEndpoint, $this->isReservedWord($selectedEndpoint) ? '<b>Is reserved!</b>' : 'OK.']);
            $this->outputLine('- Resource : %s - %s', [$resource, $this->isReservedWord($resource) ? '<b>Is reserved!</b>' : 'OK.']);
            $this->quit(1);
        }

        /** @var Package $selectedPackage */
        $selectedPackage = $packages[$selectedPackage];
        $generatedFiles = $this->generatorService->generateResource($selectedPackage, $selectedEndpoint, $resource, $entity, $force);
        $this->outputLine(implode(PHP_EOL, $generatedFiles));

        $this->outputLine();
        $this->outputLine('Next Steps:');
        $this->outputLine('- Review and adjust the generated resource code.');
        $this->outputLine();
        $this->outputLine('- Add configuration based on newly created/updated resource in Settings.yaml.');
        $this->outputLine();
        $this->outputLine('Ttree:');
        $this->outputLine('  JsonApi:');
        $this->outputLine('    endpoints:');
        $this->outputLine('      \'' . $selectedEndpoint . '\':');
        $this->outputLine('        ...');
        $this->outputLine('          \'%s\':', [Str::pluralize($resource)]);
        $this->outputLine('            adapter: \'%s\'', [$this->generatorService->getNamespacesEntry('adapter')]);
        $this->outputLine('            schema: \'%s\'', [$this->generatorService->getNamespacesEntry('schema')]);
        $this->outputLine('            entity: \'%s\'', [$entity]);
        $this->outputLine('            related: []');
        $this->outputLine('            allowedMethods: [\'GET\', \'POST\', \'PATCH\', \'DELETE\', \'OPTIONS\']');

        $this->outputLine();
        $this->outputLine('<b>Resource creation completed!</b>');
    }

    /**
     * Check if entity exists
     *
     * @param string $entity
     * @return bool|string
     */
    protected function isEntityFound($entity)
    {
        if (\class_exists($entity)) {
            return $entity;
        }
        $this->outputLine('The entity: %s cannot be found.', [$entity]);
        $this->outputLine();
        $newEntity = $this->output->ask('Please specify the complete namespace of the entity you wish to use? ');
        $this->outputLine();
        return $this->isEntityFound($newEntity);
    }

    /**
     * Check is the chosen word is a reserved word
     * @param $word
     * @return bool
     */
    protected function isReservedWord($word)
    {
        $reservedWords = ['__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor'];

        return \in_array($word, $reservedWords);
    }
}
