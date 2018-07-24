<?php
namespace Ttree\JsonApi;

use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\Package\Package as BasePackage;

/**
 * Class Package
 * @package Ttree\JsonApi
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
        $dispatcher->connect('Neos\Flow\Configuration\ConfigurationManager', 'configurationManagerReady',
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
            $dispatcher->connect('Neos\Flow\Core\Booting\Sequence', 'afterInvokeStep', function ($step) use ($bootstrap) {
                if ($step->getIdentifier() === 'neos.flow:systemfilemonitor') {
                    $nodeTypeConfigurationFileMonitor = FileMonitor::createFileMonitorAtBoot('TtreeJsonApi_JsonApiSchemaConfiguration', $bootstrap);
                    $packageManager = $bootstrap->getEarlyInstance('Neos\Flow\Package\PackageManagerInterface');
                    /**
                     * @var string $packageKey
                     * @var FlowPackageInterface $package
                     */
                    foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
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
