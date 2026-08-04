<?php

namespace MacropaySolutions\KernelDev\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;

class AssetPublisherPlugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        // This event runs safely after dependencies are downloaded/updated
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPackageUpdate',
        ];
    }

    public function onPackageInstall(PackageEvent $event): void
    {
        $this->publishForPackage($event->getOperation()->getPackage());
    }

    public function onPackageUpdate(PackageEvent $event): void
    {
        $this->publishForPackage($event->getOperation()->getTargetPackage());
    }

    private function publishForPackage(PackageInterface $package): void
    {
        $extra = $package->getExtra();

        // Look for "publish-assets" in the specific package being installed/updated
        if (isset($extra['publish-assets']) && \is_array($extra['publish-assets'])) {
            $this->io->write('<info>[php-kernel-dev]</info> Publishing assets for ' . $package->getName());
            $vendorDir = $this->composer->getConfig()->get('vendor-dir');
            $projectRoot = \dirname($vendorDir);
            $packagePath = $vendorDir . '/' . $package->getName();

            foreach ($extra['publish-assets'] as $source => $destination) {
                $fullSource = $packagePath . '/' . \ltrim($source, '/');
                $fullDestination = $projectRoot . '/' . \ltrim($destination, '/');

                $this->copyRecursive($fullSource, $fullDestination);
                $this->io->write("  - Copied $source to $destination");
            }
        }
    }

    private function copyRecursive(string $source, string $destination): void
    {
        if (!\file_exists($source)) {
            return;
        }

        if (\is_dir($source)) {
            if (!\file_exists($destination)) {
                \mkdir($destination, 0755, true);
            }

            $dir = \opendir($source);

            while (($file = \readdir($dir)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $this->copyRecursive($source . '/' . $file, $destination . '/' . $file);
                }
            }

            \closedir($dir);

            return;
        }

        $dir = \dirname($destination);

        if (!\file_exists($dir)) {
            \mkdir($dir, 0755, true);
        }

        if (\file_exists($destination)) {
            $this->io->write('  ⊘ Skipped (exists): ' . $destination);

            return;
        }

        \copy($source, $destination);
    }
}
