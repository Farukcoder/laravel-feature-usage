<?php

namespace Farukcoder\FeatureHeatmap\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageLog;

class RecordFeatureUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $controllerAction,
        public ?string $routeName,
        public string $method,
        public string $uri,
        public ?int $userId,
        public string $createdAt,
    ) {
        $this->onQueue(config('feature-heatmap.queue_name', 'default'));
    }

    public function handle(): void
    {
        FeatureUsageLog::create([
            'controller_action' => $this->controllerAction,
            'route_name' => $this->routeName,
            'method' => $this->method,
            'uri' => $this->uri,
            'user_id' => $this->userId,
            'created_at' => $this->createdAt,
        ]);
    }
}
