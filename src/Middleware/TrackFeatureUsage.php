<?php

namespace Farukcoder\FeatureHeatmap\Middleware;

use Closure;
use Illuminate\Http\Request;
use Farukcoder\FeatureHeatmap\Jobs\RecordFeatureUsage;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageLog;

class TrackFeatureUsage
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Track after the response is sent so it does not impact the user's response time
        $this->track($request);

        return $response;
    }

    protected function track(Request $request): void
    {
        if (! config('feature-heatmap.enabled', true)) {
            return;
        }

        $uri = $request->path();

        foreach (config('feature-heatmap.excluded_patterns', []) as $pattern) {
            if (preg_match('#'.$pattern.'#i', $uri)) {
                return;
            }
        }

        $route = $request->route();
        $controllerAction = null;

        if ($route) {
            $action = $route->getActionName(); // e.g. App\Http\Controllers\OrderController@index
            $controllerAction = str_contains($action, '@')
                ? class_basename(explode('@', $action)[0]).'@'.explode('@', $action)[1]
                : $action; // raw name for closure routes
        }

        $payload = [
            'controllerAction' => $controllerAction,
            'routeName' => $route?->getName(),
            'method' => $request->method(),
            'uri' => $uri,
            'userId' => $request->user()?->id,
            'createdAt' => now()->toDateTimeString(),
        ];

        if (config('feature-heatmap.use_queue', true)) {
            RecordFeatureUsage::dispatch(...$payload);
        } else {
            FeatureUsageLog::create([
                'controller_action' => $payload['controllerAction'],
                'route_name' => $payload['routeName'],
                'method' => $payload['method'],
                'uri' => $payload['uri'],
                'user_id' => $payload['userId'],
                'created_at' => $payload['createdAt'],
            ]);
        }
    }
}
