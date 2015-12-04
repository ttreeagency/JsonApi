<?php
namespace Ttree\JsonApi;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Monitor\FileMonitor;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Ttree Event Package
 *
 */
class Package extends BasePackage
{

    /**
     * @var boolean
     */
    protected $protected = true;

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect('TYPO3\Flow\Configuration\ConfigurationManager', 'configurationManagerReady',
            function ($configurationManager) {
                $configurationManager->registerConfigurationType(
                    'JsonApiSchema',
                    ConfigurationManager::CONFIGURATION_PROCESSING_TYPE_DEFAULT,
                    true
                );
            }
        );

        $context = $bootstrap->getContext();
        if (!$context->isProduction()) {
            $dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function ($step) use ($bootstrap) {
                if ($step->getIdentifier() === 'typo3.flow:systemfilemonitor') {
                    $nodeTypeConfigurationFileMonitor = FileMonitor::createFileMonitorAtBoot('TtreeJsonApi_JsonApiSchemaConfiguration', $bootstrap);
                    $packageManager = $bootstrap->getEarlyInstance('TYPO3\Flow\Package\PackageManagerInterface');
                    foreach ($packageManager->getActivePackages() as $packageKey => $package) {
                        if ($packageManager->isPackageFrozen($packageKey)) {
                            continue;
                        }
                        if (file_exists($package->getConfigurationPath())) {
                            $nodeTypeConfigurationFileMonitor->monitorDirectory($package->getConfigurationPath(), 'JsonApiSchema(\..+)\.yaml');
                        }
                    }

                    $nodeTypeConfigurationFileMonitor->monitorDirectory(FLOW_PATH_CONFIGURATION, 'JsonApiSchema(\..+)\.yaml');

                    $nodeTypeConfigurationFileMonitor->detectChanges();
                    $nodeTypeConfigurationFileMonitor->shutdownObject();
                }
            });
        }
    }
}
