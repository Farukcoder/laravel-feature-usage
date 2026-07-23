<?php

namespace Farukcoder\FeatureHeatmap\Console\Commands;

use Illuminate\Console\Command;
use Farukcoder\FeatureHeatmap\Console\Installer;

class ShowBanner extends Command
{
    protected $signature = 'feature-heatmap:banner';

    protected $description = 'Display the Farukcoder CLI banner';

    public function handle(): int
    {
        Installer::printBanner();

        return self::SUCCESS;
    }
}
