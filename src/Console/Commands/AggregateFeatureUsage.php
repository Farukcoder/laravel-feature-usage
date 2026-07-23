<?php

namespace Farukcoder\FeatureHeatmap\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageLog;
use Farukcoder\FeatureHeatmap\Models\FeatureUsageSummary;

class AggregateFeatureUsage extends Command
{
    protected $signature = 'feature-heatmap:aggregate {--prune : Deletes old raw logs after aggregation}';

    protected $description = 'Aggregates raw feature usage logs into a daily summary table (the heatmap reads data from this table)';

    public function handle(): int
    {
        $this->info('Aggregating feature usage logs...');

        FeatureUsageLog::query()
            ->selectRaw('controller_action, route_name, user_id, DATE(created_at) as usage_date, COUNT(*) as hit_count')
            ->groupBy('controller_action', 'route_name', 'user_id', DB::raw('DATE(created_at)'))
            ->orderBy('usage_date')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    FeatureUsageSummary::query()->upsert(
                        [[
                            'controller_action' => $row->controller_action,
                            'route_name' => $row->route_name,
                            'user_id' => $row->user_id,
                            'usage_date' => $row->usage_date,
                            'hit_count' => (int) $row->hit_count,
                        ]],
                        ['controller_action', 'user_id', 'usage_date'],
                        // On conflict, adds to the existing hit_count
                        ['hit_count' => DB::raw('hit_count + '.(int) $row->hit_count)]
                    );
                }
            });

        $this->info('Aggregation complete.');

        if ($this->option('prune')) {
            $days = config('feature-heatmap.raw_log_retention_days', 14);
            $deleted = FeatureUsageLog::where('created_at', '<', now()->subDays($days))->delete();
            $this->info("Pruned {$deleted} raw log rows older than {$days} days.");
        }

        return self::SUCCESS;
    }
}
