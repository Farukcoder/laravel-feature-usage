<?php

namespace Farukcoder\FeatureHeatmap\Console;

class Installer
{
    public static function postAutoloadDump($event = null): void
    {
        static::printBanner();
    }

    public static function postInstall($event = null): void
    {
        static::printBanner();
    }

    public static function printBanner(): void
    {
        if (php_sapi_name() !== 'cli') {
            return;
        }

        $boldCyan = "\033[1;36m";
        $green    = "\033[1;32m";
        $yellow   = "\033[1;33m";
        $reset    = "\033[0m";

        $banner = <<<ASCII

{$boldCyan}  ███████╗ █████╗ ██████╗ ██╗  ██╗██╗  ██╗  ██████╗  ██████╗ ██████╗ ███████╗██████╗ 
  ██╔════╝██╔══██╗██╔══██╗██║  ██║██║ ██╔╝ ██╔════╝ ██╔═══██╗██╔══██╗██╔════╝██╔══██╗
  █████╗  ███████║██████╔╝██║  ██║█████╔╝  ██║      ██║   ██║██║  ██║█████╗  ██████╔╝
  ██╔══╝  ██╔══██╗██╔══██╗██║  ██║██╔═██╗  ██║      ██║   ██║██║  ██║██╔══╝  ██╔══██╗
  ██║     ██║  ██║██║  ██║╚██████╔╝██║  ██╗ ╚██████╗ ╚██████╔╝██████╔╝███████╗██║  ██║
  ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝ ╚═╝  ╚═╝  ╚═════╝  ╚═════╝ ╚═════╝ ╚══════╝╚═╝  ╚═╝{$reset}

        {$green}Laravel Feature Heatmap — Route & Feature Usage Tracker{$reset}
                  {$yellow}by Farukcoder | github.com/farukcoder{$reset}

ASCII;

        if (defined('STDOUT') && is_resource(STDOUT)) {
            fwrite(STDOUT, $banner . PHP_EOL);
        } else {
            echo $banner . PHP_EOL;
        }
    }
}
