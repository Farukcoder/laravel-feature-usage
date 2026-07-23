<?php

namespace Farukcoder\FeatureHeatmap;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;
use Composer\Installer\PackageEvents;
use Farukcoder\FeatureHeatmap\Console\Installer;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    /** @var IOInterface */
    protected $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
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
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
            ScriptEvents::POST_AUTOLOAD_DUMP     => 'onPostAutoloadDump',
        ];
    }

    public function onPostPackageInstall(): void
    {
        Installer::printBanner();
    }

    public function onPostAutoloadDump(): void
    {
        Installer::printBanner();
    }
}
