<?php

namespace Farukcoder\FeatureHeatmap;

use Illuminate\Support\ServiceProvider;
use Farukcoder\FeatureHeatmap\Console\Commands\AggregateFeatureUsage;

class FeatureHeatmapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/feature-heatmap.php', 'feature-heatmap');
    }

    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Views (namespace: feature-heatmap::dashboard)
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'feature-heatmap');

        // Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Publish config so developers can customise it
        $this->publishes([
            __DIR__.'/../config/feature-heatmap.php' => config_path('feature-heatmap.php'),
        ], 'feature-heatmap-config');

        // Publish views (optional, for overriding)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/feature-heatmap'),
        ], 'feature-heatmap-views');

        $router = $this->app['router'];

        // Register the named middleware alias for manual use: Route::middleware('track.feature')
        $router->aliasMiddleware('track.feature', Middleware\TrackFeatureUsage::class);

        // Auto-push middleware globally onto HTTP Kernel and Router groups
        // so EVERY request in the consuming app is tracked automatically — zero manual route setup.
        if (config('feature-heatmap.auto_track', true)) {
            // Push globally to HTTP Kernel (runs on ALL routes in the application)
            if ($this->app->bound(\Illuminate\Contracts\Http\Kernel::class)) {
                $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
                if (method_exists($kernel, 'pushMiddleware')) {
                    $kernel->pushMiddleware(Middleware\TrackFeatureUsage::class);
                }
            }

            // Also attach to configured router middleware groups ('web', 'api')
            $groups = (array) config('feature-heatmap.auto_track_groups', ['web', 'api']);
            foreach ($groups as $group) {
                $router->pushMiddlewareToGroup($group, Middleware\TrackFeatureUsage::class);
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                AggregateFeatureUsage::class,
            ]);
        }
    }
}
