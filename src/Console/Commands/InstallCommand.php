<?php

namespace Farukcoder\FeatureHeatmap\Console\Commands;

use Illuminate\Console\Command;
use Farukcoder\FeatureHeatmap\Console\Installer;

class InstallCommand extends Command
{
    protected $signature = 'feature-heatmap:install';

    protected $description = 'Install and publish config for Laravel Feature Heatmap';

    public function handle(): int
    {
        Installer::printBanner();

        $this->info('Publishing Laravel Feature Heatmap configuration...');

        $this->call('vendor:publish', [
            '--provider' => 'Farukcoder\FeatureHeatmap\FeatureHeatmapServiceProvider',
            '--tag'      => 'feature-heatmap-config',
        ]);

        $this->info('Laravel Feature Heatmap installed successfully!');

        return self::SUCCESS;
    }
}
